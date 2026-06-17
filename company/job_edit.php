<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_COMPANY);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT id FROM companies WHERE user_id = ?');
$stmt->execute([$userId]);
$company = $stmt->fetch();
if (!$company) {
    header('Location: /web/company/profile.php');
    exit;
}
$companyId = (int) $company['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$job = null;
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ? AND company_id = ?');
    $stmt->execute([$id, $companyId]);
    $job = $stmt->fetch();
    if (!$job) {
        header('Location: /web/company/jobs.php');
        exit;
    }
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $jobType = $_POST['job_type'] ?? 'full_time';
    $salaryMin = isset($_POST['salary_min']) && $_POST['salary_min'] !== '' ? (float)$_POST['salary_min'] : null;
    $salaryMax = isset($_POST['salary_max']) && $_POST['salary_max'] !== '' ? (float)$_POST['salary_max'] : null;
    $status = $_POST['status'] ?? 'published';

    if (!in_array($jobType, ['full_time', 'part_time', 'contract', 'internship'], true)) {
        $jobType = 'full_time';
    }
    if (!in_array($status, ['draft', 'published', 'closed'], true)) {
        $status = 'published';
    }

    if (strlen($title) < 2) {
        $error = 'Title must be at least 2 characters.';
    } else {
        if ($isEdit) {
            $pdo->prepare('UPDATE jobs SET title = ?, description = ?, location = ?, job_type = ?, salary_min = ?, salary_max = ?, status = ? WHERE id = ? AND company_id = ?')->execute([$title, $description, $location, $jobType, $salaryMin, $salaryMax, $status, $id, $companyId]);
            $success = true;
        } else {
            $pdo->prepare('INSERT INTO jobs (company_id, title, description, location, job_type, salary_min, salary_max, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([$companyId, $title, $description, $location, $jobType, $salaryMin, $salaryMax, $status]);
            header('Location: /web/company/jobs.php');
            exit;
        }
        $job = array_merge($job ?: [], ['title' => $title, 'description' => $description, 'location' => $location, 'job_type' => $jobType, 'salary_min' => $salaryMin, 'salary_max' => $salaryMax, 'status' => $status]);
    }
}

$pageTitle = $isEdit ? 'Edit Job' : 'Post Job';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1><?= $isEdit ? 'Edit Job' : 'Post a Job' ?></h1>
    <?php if ($success): ?>
        <div class="alert alert-success">Job updated.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="form-card">
        <div class="form-group">
            <label for="title">Job Title</label>
            <input type="text" id="title" name="title" value="<?= e($job['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="6" required><?= e($job['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?= e($job['location'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="job_type">Job Type</label>
                <select id="job_type" name="job_type">
                    <option value="full_time" <?= ($job['job_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full-time</option>
                    <option value="part_time" <?= ($job['job_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part-time</option>
                    <option value="contract" <?= ($job['job_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contract</option>
                    <option value="internship" <?= ($job['job_type'] ?? '') === 'internship' ? 'selected' : '' ?>>Internship</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="salary_min">Salary Min ($)</label>
                <input type="number" id="salary_min" name="salary_min" step="0.01" min="0" value="<?= $job['salary_min'] !== null && $job['salary_min'] !== '' ? e((string)$job['salary_min']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="salary_max">Salary Max ($)</label>
                <input type="number" id="salary_max" name="salary_max" step="0.01" min="0" value="<?= $job['salary_max'] !== null && $job['salary_max'] !== '' ? e((string)$job['salary_max']) : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?= ($job['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($job['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="closed" <?= ($job['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Publish' ?> Job</button>
    </form>
    <p><a href="<?= BASE_URL ?>/company/jobs.php">&larr; Back to Jobs</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
