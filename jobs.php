<?php
require_once __DIR__ . '/config/init.php';

$pdo = getDB();
$search = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$jobType = $_GET['type'] ?? '';

$sql = "
    SELECT j.id, j.title, j.description, j.location, j.job_type, j.salary_min, j.salary_max, j.created_at, c.company_name, c.id AS company_id
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    WHERE j.status = 'published'
";
$params = [];
if ($search !== '') {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($location !== '') {
    $sql .= " AND j.location LIKE ?";
    $params[] = '%' . $location . '%';
}
if ($jobType !== '') {
    $sql .= " AND j.job_type = ?";
    $params[] = $jobType;
}
$sql .= " ORDER BY j.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$pageTitle = 'Job Listings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container page-header">
    <h1>Job Listings</h1>
    <form method="get" class="search-form inline-form">
        <input type="text" name="q" placeholder="Search jobs..." value="<?= e($search) ?>">
        <input type="text" name="location" placeholder="Location" value="<?= e($location) ?>">
        <select name="type">
            <option value="">All types</option>
            <option value="full_time" <?= $jobType === 'full_time' ? 'selected' : '' ?>>Full-time</option>
            <option value="part_time" <?= $jobType === 'part_time' ? 'selected' : '' ?>>Part-time</option>
            <option value="contract" <?= $jobType === 'contract' ? 'selected' : '' ?>>Contract</option>
            <option value="internship" <?= $jobType === 'internship' ? 'selected' : '' ?>>Internship</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>

<div class="container">
    <?php if (empty($jobs)): ?>
        <p class="muted">No jobs match your criteria.</p>
    <?php else: ?>
        <div class="card-list">
            <?php foreach ($jobs as $job): ?>
                <a href="<?= BASE_URL ?>/job.php?id=<?= (int)$job['id'] ?>" class="listing-card">
                    <div class="listing-avatar av-job"><?= mb_strtoupper(mb_substr($job['company_name'], 0, 1)) ?></div>
                    <div class="listing-body">
                        <h3 class="listing-title"><?= e($job['title']) ?></h3>
                        <div class="listing-info">
                            <span><?= e($job['company_name']) ?></span>
                            <span class="listing-dot">&bull;</span>
                            <span><?= e($job['location'] ?? 'Remote') ?></span>
                        </div>
                        <p class="listing-desc"><?= e(mb_substr($job['description'], 0, 120)) ?></p>
                        <div class="listing-footer">
                            <span class="listing-tag tag-type"><?= e(str_replace('_', '-', $job['job_type'])) ?></span>
                            <?php if ($job['salary_min'] || $job['salary_max']): ?>
                                <span class="listing-tag tag-salary">$<?= $job['salary_min'] ? number_format($job['salary_min']) : '—' ?> – $<?= $job['salary_max'] ? number_format($job['salary_max']) : '—' ?></span>
                            <?php endif; ?>
                            <span class="listing-date"><?= date('M j', strtotime($job['created_at'])) ?></span>
                        </div>
                    </div>
                    <span class="listing-arrow">&rsaquo;</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
