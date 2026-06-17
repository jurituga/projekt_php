<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_COMPANY);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT id FROM companies WHERE user_id = ?');
$stmt->execute([$userId]);
$company = $stmt->fetch();
if (!$company) {
    header('Location: ' . BASE_URL . '/company/profile.php');
    exit;
}
$companyId = (int) $company['id'];

// Job count
$stmt = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = ?');
$stmt->execute([$companyId, 'published']);
$jobsCount = (int) $stmt->fetchColumn();

// Recent applications (to our jobs)
$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.created_at, j.title AS job_title, j.id AS job_id, u.name AS applicant_name, u.id AS applicant_id, a.cv_id
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    WHERE j.company_id = ?
    ORDER BY a.created_at DESC
    LIMIT 10
");
$stmt->execute([$companyId]);
$applications = $stmt->fetchAll();

// My service requests (as a company booking freelancers)
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

$pageTitle = 'Company Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Company Dashboard</h1>
        <p class="muted">Welcome back, <?= e($_SESSION['user_name']) ?>.</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <strong><?= $jobsCount ?></strong>
            <span>Published jobs</span>
        </div>
        <div class="stat-card">
            <strong><?= count($applications) ?></strong>
            <span>Recent applications</span>
        </div>
    </div>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>Recent Job Applications</h2>
        <?php if (empty($applications)): ?>
            <p class="muted">No applications yet. <a href="<?= BASE_URL ?>/company/jobs.php">Create jobs</a> to receive applications.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Applicant</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>CV</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>/company/job_applications.php?job_id=<?= (int)$app['job_id'] ?>"><?= e($app['job_title']) ?></a></td>
                            <td><?= e($app['applicant_name']) ?></td>
                            <td><span class="status status-<?= e($app['status']) ?>"><?= e($app['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                            <td><?php if ($app['cv_id']): ?><a href="<?= BASE_URL ?>/download_cv.php?id=<?= (int)$app['cv_id'] ?>">Download</a><?php else: ?>&mdash;<?php endif; ?></td>
                            <td>
                                <?php if ($app['status'] === APPLICATION_STATUS_PENDING || $app['status'] === APPLICATION_STATUS_VIEWED): ?>
                                    <form method="post" action="<?= BASE_URL ?>/company/application_action.php" style="display:inline">
                                        <input type="hidden" name="application_id" value="<?= (int)$app['id'] ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-small">Accept</button>
                                    </form>
                                    <form method="post" action="<?= BASE_URL ?>/company/application_action.php" style="display:inline">
                                        <input type="hidden" name="application_id" value="<?= (int)$app['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-small btn-danger">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/company/applications.php">View all applications &rarr;</a></p>
        <?php endif; ?>
    </section>

    <?php if (!empty($serviceRequests)): ?>
    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Service Requests</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Freelancer</th>
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
                                <br><small class="text-danger"><strong>Reason:</strong> <?= e(mb_substr($sr['rejection_reason'], 0, 60)) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($sr['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

    <div class="actions">
        <a href="<?= BASE_URL ?>/company/profile.php" class="btn btn-secondary">Company Profile</a>
        <a href="<?= BASE_URL ?>/company/jobs.php" class="btn btn-primary">My Jobs</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
