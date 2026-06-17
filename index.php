<?php
require_once __DIR__ . '/config/init.php';

try {
    $pdo = getDB();
} catch (PDOException $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Setup required</title></head><body style="font-family:sans-serif;max-width:600px;margin:2rem auto;padding:1rem;">';
    echo '<h1>Database not set up</h1>';
    echo '<p>Create the database and tables first:</p>';
    echo '<ol><li>Start <strong>Apache</strong> and <strong>MySQL</strong> in XAMPP.</li>';
    echo '<li>Open <a href="http://localhost/phpmyadmin">phpMyAdmin</a>.</li>';
    echo '<li>Import <code>database/schema.sql</code> (creates database and tables).</li>';
    echo '<li>Optional: import <code>database/seed.sql</code> for sample jobs and freelancers.</li>';
    echo '<li>Refresh this page.</li></ol>';
    echo '<p><small>Error: ' . htmlspecialchars($e->getMessage()) . '</small></p>';
    echo '</body></html>';
    exit;
}

$stmtJobs = $pdo->query("
    SELECT j.id, j.title, j.location, j.job_type, j.created_at, c.company_name
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    WHERE j.status = 'published'
    ORDER BY j.created_at DESC
    LIMIT 6
");
$recentJobs = $stmtJobs->fetchAll();

$stmtServices = $pdo->query("
    SELECT s.id, s.title, s.price, s.price_type, u.name AS freelancer_name
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    WHERE s.status = 'active'
    ORDER BY s.created_at DESC
    LIMIT 6
");
$recentServices = $stmtServices->fetchAll();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero">
    <div class="container">
        <h1>Hire experts.<br>Get hired.</h1>
        <p>The modern platform for job opportunities and professional freelance services. Find the right match, fast.</p>
        <div class="hero-actions">
            <a href="<?= BASE_URL ?>/jobs.php" class="btn btn-primary btn-lg">Browse Jobs</a>
            <a href="<?= BASE_URL ?>/services.php" class="btn btn-secondary btn-lg">Explore Services</a>
        </div>
    </div>
</div>

<div class="container section" style="margin-top: 3rem;">
    <h2>Latest Job Openings</h2>
    <?php if (empty($recentJobs)): ?>
        <p class="muted">No job listings yet.</p>
    <?php else: ?>
        <div class="card-list">
            <?php foreach ($recentJobs as $job): ?>
                <a href="<?= BASE_URL ?>/job.php?id=<?= (int)$job['id'] ?>" class="listing-card">
                    <div class="listing-avatar av-job"><?= mb_strtoupper(mb_substr($job['company_name'], 0, 1)) ?></div>
                    <div class="listing-body">
                        <h3 class="listing-title"><?= e($job['title']) ?></h3>
                        <div class="listing-info">
                            <span><?= e($job['company_name']) ?></span>
                            <span class="listing-dot">&bull;</span>
                            <span><?= e($job['location'] ?? 'Remote') ?></span>
                        </div>
                        <div class="listing-footer">
                            <span class="listing-tag tag-type"><?= e(str_replace('_', '-', $job['job_type'])) ?></span>
                            <span class="listing-date"><?= date('M j', strtotime($job['created_at'])) ?></span>
                        </div>
                    </div>
                    <span class="listing-arrow">&rsaquo;</span>
                </a>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:1.25rem;"><a href="<?= BASE_URL ?>/jobs.php" class="btn btn-ghost btn-sm">View all jobs &rarr;</a></p>
    <?php endif; ?>
</div>

<div class="container section">
    <h2>Popular Services</h2>
    <?php if (empty($recentServices)): ?>
        <p class="muted">No services yet.</p>
    <?php else: ?>
        <div class="card-list">
            <?php foreach ($recentServices as $svc): ?>
                <a href="<?= BASE_URL ?>/service.php?id=<?= (int)$svc['id'] ?>" class="listing-card">
                    <div class="listing-avatar av-service"><?= mb_strtoupper(mb_substr($svc['freelancer_name'], 0, 1)) ?></div>
                    <div class="listing-body">
                        <h3 class="listing-title"><?= e($svc['title']) ?></h3>
                        <div class="listing-info">
                            <span><?= e($svc['freelancer_name']) ?></span>
                        </div>
                        <div class="listing-footer">
                            <span class="listing-tag tag-price"><?= $svc['price'] ? '$' . number_format($svc['price'], 2) . ' ' . $svc['price_type'] : 'Contact for price' ?></span>
                        </div>
                    </div>
                    <span class="listing-arrow">&rsaquo;</span>
                </a>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:1.25rem;"><a href="<?= BASE_URL ?>/services.php" class="btn btn-ghost btn-sm">View all services &rarr;</a></p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
