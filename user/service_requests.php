<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_USER);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT sr.id, sr.status, sr.message, sr.booking_date, sr.booking_time, sr.rejection_reason, sr.created_at,
           sr.payment_status, sr.payment_amount,
           s.title AS service_title, s.id AS service_id, u.name AS freelancer_name, u.id AS freelancer_uid,
           fr.rating AS my_rating
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    JOIN users u ON u.id = s.freelancer_id
    LEFT JOIN freelancer_ratings fr ON fr.service_request_id = sr.id
    WHERE sr.requester_id = ?
    ORDER BY sr.created_at DESC
");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

$payMsg = $_SESSION['pay_error'] ?? '';
unset($_SESSION['pay_error']);

$pageTitle = 'My Service Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>My Service Requests</h1>
    <?php if ($payMsg): ?>
        <div class="alert alert-error"><?= e($payMsg) ?></div>
    <?php endif; ?>
    <?php if (empty($requests)): ?>
        <p class="muted">No service requests. <a href="<?= BASE_URL ?>/services.php">Browse services</a>.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Freelancer</th>
                    <th>Booking</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/service.php?id=<?= (int)$r['service_id'] ?>"><?= e($r['service_title']) ?></a></td>
                        <td><?= e($r['freelancer_name']) ?> <a href="<?= BASE_URL ?>/messages/chat.php?with=<?= (int)$r['freelancer_uid'] ?>" class="btn btn-small btn-secondary" title="Message">&#9993;</a></td>
                        <td>
                            <?php if ($r['booking_date']): ?>
                                <?= date('M j, Y', strtotime($r['booking_date'])) ?>
                                <?php if ($r['booking_time']): ?>
                                    <br><small><?= date('g:i A', strtotime($r['booking_time'])) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($r['payment_status'] ?? 'unpaid') === 'paid'): ?>
                                <span class="status status-active">Paid $<?= number_format($r['payment_amount'], 2) ?></span>
                            <?php elseif (($r['payment_status'] ?? 'unpaid') === 'refunded'): ?>
                                <span class="status status-rejected">Refunded</span>
                            <?php elseif ($r['status'] === SERVICE_REQUEST_COMPLETED && $r['payment_amount'] > 0): ?>
                                <a href="<?= BASE_URL ?>/pay.php?request_id=<?= (int)$r['id'] ?>" class="btn btn-small btn-pay">Pay $<?= number_format($r['payment_amount'], 2) ?></a>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span>
                            <?php if ($r['status'] === SERVICE_REQUEST_REJECTED && $r['rejection_reason']): ?>
                                <br><small class="text-danger"><strong>Reason:</strong> <?= e($r['rejection_reason']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === SERVICE_REQUEST_COMPLETED && $r['my_rating']): ?>
                                <?= renderStars($r['my_rating']) ?>
                            <?php elseif ($r['status'] === SERVICE_REQUEST_COMPLETED): ?>
                                <a href="<?= BASE_URL ?>/rate.php?request_id=<?= (int)$r['id'] ?>" class="btn btn-small btn-success">Rate</a>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="<?= BASE_URL ?>/user/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
