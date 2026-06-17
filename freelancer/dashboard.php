<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

$userId = currentUserId();
$pdo = getDB();

// Service requests (incoming)
$stmt = $pdo->prepare("
    SELECT sr.id, sr.status, sr.message, sr.booking_date, sr.booking_time, sr.rejection_reason, sr.created_at,
           sr.payment_status, sr.payment_amount,
           s.title AS service_title, u.name AS requester_name, u.id AS requester_id
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    JOIN users u ON u.id = sr.requester_id
    WHERE s.freelancer_id = ?
    ORDER BY sr.created_at DESC
    LIMIT 15
");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

// My services count
$stmt = $pdo->prepare('SELECT COUNT(*) FROM services WHERE freelancer_id = ? AND status = ?');
$stmt->execute([$userId, 'active']);
$activeServicesCount = (int) $stmt->fetchColumn();

// Freelancer type
$stmt = $pdo->prepare('SELECT freelancer_type FROM freelancer_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profileInfo = $stmt->fetch();
$freelancerType = $profileInfo['freelancer_type'] ?? 'general';
$isScheduled = in_array($freelancerType, ['electrician', 'plumber'], true);

// Rating stats
$stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS rating_count FROM freelancer_ratings WHERE freelancer_id = ?');
$stmt->execute([$userId]);
$ratingInfo = $stmt->fetch();
$avgRating = $ratingInfo['avg_rating'] ? round($ratingInfo['avg_rating'], 1) : 0;
$ratingCount = (int)($ratingInfo['rating_count'] ?? 0);

// Total earnings
$stmt = $pdo->prepare("SELECT COALESCE(SUM(sr.payment_amount), 0) AS total_earned FROM service_requests sr JOIN services s ON s.id = sr.service_id WHERE s.freelancer_id = ? AND sr.payment_status = 'paid'");
$stmt->execute([$userId]);
$totalEarned = (float)$stmt->fetchColumn();

$pageTitle = 'Freelancer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Freelancer Dashboard
            <?php if ($isScheduled): ?>
                <span class="status status-active"><?= e(ucfirst($freelancerType)) ?></span>
            <?php endif; ?>
        </h1>
        <p class="muted">Welcome back, <?= e($_SESSION['user_name']) ?>.</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <strong><?= $activeServicesCount ?></strong>
            <span>Active services</span>
        </div>
        <div class="stat-card">
            <strong><?= count(array_filter($requests, fn($r) => $r['status'] === SERVICE_REQUEST_PENDING)) ?></strong>
            <span>Pending requests</span>
        </div>
        <div class="stat-card">
            <strong>$<?= number_format($totalEarned, 2) ?></strong>
            <span>Total Earned</span>
        </div>
        <div class="stat-card">
            <?php if ($ratingCount > 0): ?>
                <strong>
                    <?= renderStars($avgRating) ?>
                    <?= number_format($avgRating, 1) ?>
                </strong>
                <span><?= $ratingCount ?> review<?= $ratingCount > 1 ? 's' : '' ?></span>
            <?php else: ?>
                <strong>&mdash;</strong>
                <span>No ratings yet</span>
            <?php endif; ?>
        </div>
    </div>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>Service Requests</h2>
        <?php if (empty($requests)): ?>
            <p class="muted">No service requests yet. <a href="<?= BASE_URL ?>/freelancer/services.php">Create services</a> to get requests.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Requester</th>
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
                            <td><?= e($r['requester_name']) ?></td>
                            <td>
                                <?php if ($r['booking_date']): ?>
                                    <?= date('M j, Y', strtotime($r['booking_date'])) ?>
                                    <?php if ($r['booking_time']): ?>
                                        <br><small><?= date('g:i A', strtotime($r['booking_time'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (($r['payment_status'] ?? 'unpaid') === 'paid'): ?>
                                    <span class="status status-active">Paid $<?= number_format($r['payment_amount'], 2) ?></span>
                                <?php elseif (($r['payment_status'] ?? 'unpaid') === 'refunded'): ?>
                                    <span class="status status-rejected">Refunded</span>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
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
                            <tr class="reject-form-row" id="reject-form-<?= (int)$r['id'] ?>" style="display:none;">
                                <td colspan="7">
                                    <form method="post" action="<?= BASE_URL ?>/freelancer/request_action.php" class="reject-reason-form">
                                        <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <label><strong>Reason for rejection</strong> <span class="required">*</span></label>
                                        <textarea name="rejection_reason" rows="2" required placeholder="Explain why you are rejecting this request..."></textarea>
                                        <div style="margin-top:0.5rem;">
                                            <button type="submit" class="btn btn-small btn-danger">Confirm Rejection</button>
                                            <button type="button" class="btn btn-small btn-ghost" onclick="toggleRejectForm(<?= (int)$r['id'] ?>)">Cancel</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/freelancer/requests.php">View all requests &rarr;</a></p>
        <?php endif; ?>
    </section>

    <?php if ($isScheduled): ?>
    <section class="card" style="margin-bottom:1.5rem">
        <p class="muted">Manage your availability dates and times from each service's edit page.</p>
        <a href="<?= BASE_URL ?>/freelancer/services.php" class="btn btn-primary" style="margin-top:.75rem">Go to My Services</a>
    </section>
    <?php endif; ?>

    <div class="actions">
        <a href="<?= BASE_URL ?>/freelancer/profile.php" class="btn btn-secondary">Edit Profile</a>
        <a href="<?= BASE_URL ?>/freelancer/services.php" class="btn btn-primary">My Services</a>
        <a href="<?= BASE_URL ?>/freelancer/earnings.php" class="btn btn-secondary">My Earnings</a>
    </div>
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
