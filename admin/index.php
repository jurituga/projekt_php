<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pdo = getDB();

$stats = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role != "admin"')->fetchColumn(),
    'pending' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role != "admin" AND status = "pending"')->fetchColumn(),
    'companies' => (int) $pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn(),
    'jobs' => (int) $pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn(),
    'applications' => (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
    'services' => (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn(),
    'service_requests' => (int) $pdo->query('SELECT COUNT(*) FROM service_requests')->fetchColumn(),
];

$pageTitle = 'Admin Panel';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Admin Control Panel</h1>
        <p class="muted">Welcome back, <?= e($_SESSION['user_name']) ?>.</p>
    </div>

    <div class="dashboard-stats admin-stats">
        <div class="stat-card"><strong><?= $stats['users'] ?></strong><span>Users</span></div>
        <div class="stat-card">
            <strong><?= $stats['pending'] ?></strong>
            <span>Pending approval</span>
            <?php if ($stats['pending'] > 0): ?>
                <a href="<?= BASE_URL ?>/admin/users.php" style="display:block;font-size:.78rem;margin-top:.25rem;">Approve &rarr;</a>
            <?php endif; ?>
        </div>
        <div class="stat-card"><strong><?= $stats['companies'] ?></strong><span>Companies</span></div>
        <div class="stat-card"><strong><?= $stats['jobs'] ?></strong><span>Jobs</span></div>
        <div class="stat-card"><strong><?= $stats['applications'] ?></strong><span>Applications</span></div>
        <div class="stat-card"><strong><?= $stats['services'] ?></strong><span>Services</span></div>
        <div class="stat-card"><strong><?= $stats['service_requests'] ?></strong><span>Requests</span></div>
    </div>

    <div class="admin-links">
        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-primary">Manage Users</a>
        <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn btn-secondary">Manage Jobs</a>
        <a href="<?= BASE_URL ?>/admin/services.php" class="btn btn-secondary">Manage Services</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
