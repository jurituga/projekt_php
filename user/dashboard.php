<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_USER);

$userId = currentUserId();
$pdo = getDB();

// My applications
$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.created_at, a.cover_letter, j.title AS job_title, j.id AS job_id, c.company_name
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

// My service requests
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

$stmt = $pdo->prepare('SELECT id, file_name, is_default, created_at FROM cvs WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
$stmt->execute([$userId]);
$cvs = $stmt->fetchAll();

$cvUploadError = '';
$cvUploadSuccess = isset($_GET['cv_uploaded']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dashboard_cv_upload'])) {
    if (empty($_FILES['cv_file']['name'])) {
        $cvUploadError = 'Please select a PDF file.';
    } elseif ($_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
        $cvUploadError = 'Upload failed. Try again.';
    } elseif ($_FILES['cv_file']['size'] > UPLOAD_MAX_CV_SIZE) {
        $cvUploadError = 'File too large (max 5MB).';
    } elseif (!in_array($_FILES['cv_file']['type'], ALLOWED_CV_TYPES, true)) {
        $cvUploadError = 'Only PDF files are allowed.';
    } else {
        $ext = pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION) ?: 'pdf';
        $filename = 'cv_' . $userId . '_' . time() . '.' . $ext;
        $filepath = UPLOAD_PATH_CV . $filename;
        if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $filepath)) {
            $isDefault = empty($cvs) ? 1 : 0;
            $pdo->prepare('INSERT INTO cvs (user_id, file_name, file_path, is_default) VALUES (?, ?, ?, ?)')
                ->execute([$userId, $_FILES['cv_file']['name'], $filename, $isDefault]);
            header('Location: ' . BASE_URL . '/user/dashboard.php?cv_uploaded=1');
            exit;
        }
        $cvUploadError = 'Could not save file.';
    }
}

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>My Dashboard</h1>
        <p class="muted">Welcome back, <?= e($_SESSION['user_name']) ?>.</p>
    </div>

    <section class="card" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
            <h2 style="margin:0">My CVs</h2>
            <a href="<?= BASE_URL ?>/user/cvs.php" class="btn btn-ghost btn-sm">Manage CVs</a>
        </div>
        <?php if ($cvUploadSuccess): ?>
            <div class="alert alert-success" style="margin-bottom:1rem">CV uploaded successfully.</div>
        <?php endif; ?>
        <?php if ($cvUploadError): ?>
            <div class="alert alert-error" style="margin-bottom:1rem"><?= e($cvUploadError) ?></div>
        <?php endif; ?>
        <?php if (empty($cvs)): ?>
            <p class="muted" style="margin-bottom:1rem">Upload a PDF CV to attach when applying for jobs.</p>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="dashboard_cv_upload" value="1">
                <div class="form-group" style="margin-bottom:.75rem">
                    <input type="file" name="cv_file" accept="application/pdf" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Upload CV</button>
            </form>
        <?php else: ?>
            <ul class="cv-summary-list">
                <?php foreach (array_slice($cvs, 0, 3) as $cv): ?>
                    <li>
                        <span><?= e($cv['file_name']) ?></span>
                        <?php if ($cv['is_default']): ?>
                            <span class="status status-published" style="font-size:.7rem">Default</span>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/download_cv.php?id=<?= (int)$cv['id'] ?>" class="btn btn-small" target="_blank" rel="noopener">Download</a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if (count($cvs) > 3): ?>
                <p class="muted" style="margin-top:.5rem;font-size:.85rem">+ <?= count($cvs) - 3 ?> more</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Applications</h2>
        <?php if (empty($applications)): ?>
            <p class="muted">You haven't applied to any jobs yet. <a href="<?= BASE_URL ?>/jobs.php">Browse jobs</a>.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>/job.php?id=<?= (int)$app['job_id'] ?>"><?= e($app['job_title']) ?></a></td>
                            <td><?= e($app['company_name']) ?></td>
                            <td><span class="status status-<?= e($app['status']) ?>"><?= e($app['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/user/applications.php">View all applications &rarr;</a></p>
        <?php endif; ?>
    </section>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Service Requests</h2>
        <?php if (empty($serviceRequests)): ?>
            <p class="muted">No service requests. <a href="<?= BASE_URL ?>/services.php">Browse services</a>.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Freelancer</th>
                        <th>Booking Date</th>
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
                                <?php if ($sr['booking_date']): ?>
                                    <?= date('M j, Y', strtotime($sr['booking_date'])) ?>
                                    <?php if ($sr['booking_time']): ?>
                                        <br><small><?= date('g:i A', strtotime($sr['booking_time'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
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
                                    <br><small class="text-danger"><strong>Reason:</strong> <?= e(mb_substr($sr['rejection_reason'], 0, 60)) ?><?= mb_strlen($sr['rejection_reason']) > 60 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y', strtotime($sr['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/user/service_requests.php">View all &rarr;</a></p>
        <?php endif; ?>
    </section>

    <div class="actions">
        <a href="<?= BASE_URL ?>/user/profile.php" class="btn btn-secondary">Edit Profile</a>
        <a href="<?= BASE_URL ?>/user/cvs.php" class="btn btn-secondary">Manage CVs</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
