<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

$userId = currentUserId();
$pdo = getDB();

// Total earnings
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(sr.payment_amount), 0) AS total_earned,
           COUNT(*) AS paid_requests
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    WHERE s.freelancer_id = ? AND sr.payment_status = 'paid'
");
$stmt->execute([$userId]);
$totals = $stmt->fetch();

// Recent paid requests
$stmt = $pdo->prepare("
    SELECT sr.payment_amount, sr.paid_at, sr.status,
           s.title AS service_title, u.name AS customer_name
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    JOIN users u ON u.id = sr.requester_id
    WHERE s.freelancer_id = ? AND sr.payment_status = 'paid'
    ORDER BY sr.paid_at DESC
    LIMIT 20
");
$stmt->execute([$userId]);
$payments = $stmt->fetchAll();

$pageTitle = 'My Earnings';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>My Earnings</h1>

    <div class="dashboard-stats">
        <div class="stat-card">
            <strong>$<?= number_format($totals['total_earned'], 2) ?></strong>
            <span>Total Earned</span>
        </div>
        <div class="stat-card">
            <strong><?= (int)$totals['paid_requests'] ?></strong>
            <span>Paid Bookings</span>
        </div>
    </div>

    <section class="card">
        <h2>Payment History</h2>
        <?php if (empty($payments)): ?>
            <p class="muted">No payments received yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Job Status</th>
                        <th>Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= e($p['service_title']) ?></td>
                            <td><?= e($p['customer_name']) ?></td>
                            <td><strong>$<?= number_format($p['payment_amount'], 2) ?></strong></td>
                            <td><span class="status status-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                            <td><?= $p['paid_at'] ? date('M j, Y g:i A', strtotime($p['paid_at'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <p><a href="<?= BASE_URL ?>/freelancer/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
