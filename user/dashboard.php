<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_USER);

$userId = currentUserId();
$pdo = getDB();

// My applications
$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.created_at, a.cover_letter, j.title AS job_title, j.id AS job_id, c.company_name
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

// My service requests
$stmt = $pdo->prepare("
    SELECT sr.id, sr.status, sr.message, sr.booking_date, sr.booking_time, sr.rejection_reason, sr.created_at,
           sr.payment_status, sr.payment_amount,
           s.title AS service_title, u.name AS freelancer_name
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    JOIN users u ON u.id = s.freelancer_id
    WHERE sr.requester_id = ?
    ORDER BY sr.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$serviceRequests = $stmt->fetchAll();

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>My Dashboard</h1>
        <p class="muted">Welcome back, <?= e($_SESSION['user_name']) ?>.</p>
    </div>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Applications</h2>
        <?php if (empty($applications)): ?>
            <p class="muted">You haven't applied to any jobs yet. <a href="<?= BASE_URL ?>/jobs.php">Browse jobs</a>.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>/job.php?id=<?= (int)$app['job_id'] ?>"><?= e($app['job_title']) ?></a></td>
                            <td><?= e($app['company_name']) ?></td>
                            <td><span class="status status-<?= e($app['status']) ?>"><?= e($app['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/user/applications.php">View all applications &rarr;</a></p>
        <?php endif; ?>
    </section>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Service Requests</h2>
        <?php if (empty($serviceRequests)): ?>
            <p class="muted">No service requests. <a href="<?= BASE_URL ?>/services.php">Browse services</a>.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Freelancer</th>
                        <th>Booking Date</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Requested</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($serviceRequests as $sr): ?>
                        <tr>
                            <td><?= e($sr['service_title']) ?></td>
                            <td><?= e($sr['freelancer_name']) ?></td>
                            <td>
                                <?php if ($sr['booking_date']): ?>
                                    <?= date('M j, Y', strtotime($sr['booking_date'])) ?>
                                    <?php if ($sr['booking_time']): ?>
                                        <br><small><?= date('g:i A', strtotime($sr['booking_time'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (($sr['payment_status'] ?? 'unpaid') === 'paid'): ?>
                                    <span class="status status-active">Paid $<?= number_format($sr['payment_amount'], 2) ?></span>
                                <?php elseif (($sr['payment_status'] ?? 'unpaid') === 'refunded'): ?>
                                    <span class="status status-rejected">Refunded</span>
                                <?php elseif ($sr['status'] === SERVICE_REQUEST_COMPLETED && $sr['payment_amount'] > 0): ?>
                                    <a href="<?= BASE_URL ?>/pay.php?request_id=<?= (int)$sr['id'] ?>" class="btn btn-small btn-pay">Pay $<?= number_format($sr['payment_amount'], 2) ?></a>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status status-<?= e($sr['status']) ?>"><?= e($sr['status']) ?></span>
                                <?php if ($sr['status'] === SERVICE_REQUEST_REJECTED && !empty($sr['rejection_reason'])): ?>
                                    <br><small class="text-danger"><strong>Reason:</strong> <?= e(mb_substr($sr['rejection_reason'], 0, 60)) ?><?= mb_strlen($sr['rejection_reason']) > 60 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y', strtotime($sr['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/user/service_requests.php">View all &rarr;</a></p>
        <?php endif; ?>
    </section>

    <div class="actions">
        <a href="<?= BASE_URL ?>/user/profile.php" class="btn btn-secondary">Edit Profile</a>
        <a href="<?= BASE_URL ?>/user/cvs.php" class="btn btn-secondary">Manage CVs</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
