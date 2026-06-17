<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT sr.id, sr.status, sr.message, sr.booking_date, sr.booking_time, sr.rejection_reason, sr.created_at,
           sr.payment_status, sr.payment_amount,
           s.title AS service_title, s.id AS service_id, u.name AS requester_name, u.id AS requester_id
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    JOIN users u ON u.id = sr.requester_id
    WHERE s.freelancer_id = ?
    ORDER BY sr.created_at DESC
");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

// Check for rejection validation error from session
$rejectError = $_SESSION['reject_error'] ?? '';
$rejectErrorId = $_SESSION['reject_request_id'] ?? 0;
unset($_SESSION['reject_error'], $_SESSION['reject_request_id']);

$pageTitle = 'Service Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Service Requests</h1>

    <?php if ($rejectError): ?>
        <div class="alert alert-error"><?= e($rejectError) ?></div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <p class="muted">No service requests yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Requester</th>
                    <th>Message</th>
                    <th>Booking</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= e($r['service_title']) ?></td>
                        <td><?= e($r['requester_name']) ?> <a href="<?= BASE_URL ?>/messages/chat.php?with=<?= (int)$r['requester_id'] ?>" class="btn btn-small btn-secondary" title="Message">&#9993;</a></td>
                        <td><?= e(mb_substr($r['message'] ?? '', 0, 80)) ?><?= mb_strlen($r['message'] ?? '') > 80 ? '...' : '' ?></td>
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
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span>
                            <?php if ($r['status'] === SERVICE_REQUEST_REJECTED && $r['rejection_reason']): ?>
                                <br><small class="muted">Reason: <?= e(mb_substr($r['rejection_reason'], 0, 60)) ?><?= mb_strlen($r['rejection_reason']) > 60 ? '...' : '' ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php if ($r['status'] === SERVICE_REQUEST_PENDING): ?>
                                <form method="post" action="<?= BASE_URL ?>/freelancer/request_action.php" style="display:inline">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-small">Accept</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($r['status'] === SERVICE_REQUEST_ACCEPTED): ?>
                                <form method="post" action="<?= BASE_URL ?>/freelancer/request_action.php" style="display:inline" onsubmit="return confirm('Mark this job as completed?');">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="action" value="complete">
                                    <button type="submit" class="btn btn-small btn-success">Complete</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($r['status'] === SERVICE_REQUEST_PENDING || $r['status'] === SERVICE_REQUEST_ACCEPTED): ?>
                                <button type="button" class="btn btn-small btn-danger" onclick="toggleRejectForm(<?= (int)$r['id'] ?>)">Reject</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($r['status'] === SERVICE_REQUEST_PENDING || $r['status'] === SERVICE_REQUEST_ACCEPTED): ?>
                        <tr class="reject-form-row" id="reject-form-<?= (int)$r['id'] ?>" style="display:<?= (int)$rejectErrorId === (int)$r['id'] ? 'table-row' : 'none' ?>;">
                            <td colspan="8">
                                <form method="post" action="<?= BASE_URL ?>/freelancer/request_action.php" class="reject-reason-form">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <label for="rejection_reason_<?= (int)$r['id'] ?>"><strong>Reason for rejection</strong> <span class="required">*</span></label>
                                    <textarea id="rejection_reason_<?= (int)$r['id'] ?>" name="rejection_reason" rows="2" required placeholder="Explain why you are rejecting this request..."></textarea>
                                    <div style="margin-top:0.5rem;">
                                        <button type="submit" class="btn btn-small btn-danger">Confirm Rejection</button>
                                        <button type="button" class="btn btn-small btn-secondary" onclick="toggleRejectForm(<?= (int)$r['id'] ?>)">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="<?= BASE_URL ?>/freelancer/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<script>
function toggleRejectForm(id) {
    var row = document.getElementById('reject-form-' + id);
    if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
