<?php
require_once __DIR__ . '/config/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/jobs.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT j.*, c.company_name, c.id AS company_id, c.user_id AS company_user_id
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    WHERE j.id = ? AND j.status = 'published'
");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/jobs.php');
    exit;
}

$canApply = isLoggedIn() && currentUserRole() === ROLE_USER;
$alreadyApplied = false;
if ($canApply) {
    $stmt = $pdo->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ?');
    $stmt->execute([$id, currentUserId()]);
    $alreadyApplied = (bool) $stmt->fetch();
}

$applyError = '';
$applySuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply']) && $canApply && !$alreadyApplied) {
    $coverLetter = trim($_POST['cover_letter'] ?? '');
    $cvId = !empty($_POST['cv_id']) ? (int)$_POST['cv_id'] : null;

    $pdo = getDB();
    if ($cvId) {
        $stmt = $pdo->prepare('SELECT id FROM cvs WHERE user_id = ? AND id = ?');
        $stmt->execute([currentUserId(), $cvId]);
        if (!$stmt->fetch()) {
            $cvId = null;
        }
    }
    $stmt = $pdo->prepare('INSERT INTO applications (job_id, user_id, cv_id, cover_letter, status) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$id, currentUserId(), $cvId ?: null, $coverLetter, APPLICATION_STATUS_PENDING]);
    $applySuccess = true;
    $alreadyApplied = true;
}

$userCvs = [];
if ($canApply && isLoggedIn()) {
    $stmt = $pdo->prepare('SELECT id, file_name, is_default FROM cvs WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
    $stmt->execute([currentUserId()]);
    $userCvs = $stmt->fetchAll();
}

$pageTitle = $job['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="detail-header">
    <div class="container">
        <p class="breadcrumb"><a href="<?= BASE_URL ?>/jobs.php">Jobs</a> &rarr; <?= e($job['title']) ?></p>
        <h1><?= e($job['title']) ?></h1>
        <div class="detail-meta">
            <span class="detail-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21V5a2 2 0 0 1 2-2h6l2 2h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <?= e($job['company_name']) ?>
            </span>
            <span class="detail-meta-dot"></span>
            <span class="detail-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <?= e($job['location'] ?? 'Remote') ?>
            </span>
            <span class="detail-meta-dot"></span>
            <span class="detail-meta-item"><?= date('M j, Y', strtotime($job['created_at'])) ?></span>
        </div>
        <div class="detail-tags">
            <span class="detail-tag"><?= e(str_replace('_', ' ', $job['job_type'])) ?></span>
            <?php if ($job['salary_min'] || $job['salary_max']): ?>
                <span class="detail-tag tag-salary">$<?= $job['salary_min'] ? number_format($job['salary_min']) : '—' ?> – $<?= $job['salary_max'] ? number_format($job['salary_max']) : '—' ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container detail-content">
    <div class="detail-section">
        <h2 class="detail-section-title">Job Description</h2>
        <div class="content-block"><?= nl2br(e($job['description'])) ?></div>
    </div>

    <?php if ($applySuccess): ?>
        <div class="alert alert-success">Application submitted successfully.</div>
    <?php endif; ?>

    <?php if ($canApply): ?>
        <?php if ($alreadyApplied): ?>
            <div class="card" style="text-align:center;padding:2rem">
                <p style="font-size:.95rem;color:var(--text-sub)">You have already applied for this job.</p>
            </div>
        <?php else: ?>
            <div class="card form-card">
                <h2>Apply for this position</h2>
                <?php if ($applyError): ?>
                    <div class="alert alert-error"><?= e($applyError) ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="hidden" name="apply" value="1">
                    <?php if (!empty($userCvs)): ?>
                        <div class="form-group">
                            <label for="cv_id">Select CV</label>
                            <select id="cv_id" name="cv_id">
                                <option value="">No CV</option>
                                <?php foreach ($userCvs as $cv): ?>
                                    <option value="<?= (int)$cv['id'] ?>" <?= $cv['is_default'] ? 'selected' : '' ?>><?= e($cv['file_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="cover_letter">Cover letter</label>
                        <textarea id="cover_letter" name="cover_letter" rows="5" placeholder="Tell the employer why you're a great fit..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            </div>
        <?php endif; ?>
    <?php elseif (!isLoggedIn()): ?>
        <div class="card" style="text-align:center;padding:2rem">
            <p style="margin-bottom:.75rem"><a href="<?= BASE_URL ?>/auth/login.php">Log in</a> or <a href="<?= BASE_URL ?>/auth/register.php">register</a> as a Job Seeker to apply.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
