<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_USER);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT id, file_name, file_path, is_default, created_at FROM cvs WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
$stmt->execute([$userId]);
$cvs = $stmt->fetchAll();

$error = '';
$success = '';

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (empty($_FILES['cv_file']['name'])) {
        $error = 'Please select a PDF file.';
    } elseif ($_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Try again.';
    } elseif ($_FILES['cv_file']['size'] > UPLOAD_MAX_CV_SIZE) {
        $error = 'File too large (max 5MB).';
    } elseif (!in_array($_FILES['cv_file']['type'], ALLOWED_CV_TYPES, true)) {
        $error = 'Only PDF files are allowed.';
    } else {
        $ext = pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION) ?: 'pdf';
        $filename = 'cv_' . $userId . '_' . time() . '.' . $ext;
        $filepath = UPLOAD_PATH_CV . $filename;
        if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $filepath)) {
            $isDefault = empty($cvs) ? 1 : 0;
            $pdo->prepare('INSERT INTO cvs (user_id, file_name, file_path, is_default) VALUES (?, ?, ?, ?)')->execute([$userId, $_FILES['cv_file']['name'], $filename, $isDefault]);
            $success = 'CV uploaded.';
            header('Location: /web/user/cvs.php?success=1');
            exit;
        }
        $error = 'Could not save file.';
    }
}

// Set default
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default'])) {
    $cvId = (int)($_POST['cv_id'] ?? 0);
    $stmt = $pdo->prepare('UPDATE cvs SET is_default = 0 WHERE user_id = ?');
    $stmt->execute([$userId]);
    $stmt = $pdo->prepare('UPDATE cvs SET is_default = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$cvId, $userId]);
    header('Location: /web/user/cvs.php');
    exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cv'])) {
    $cvId = (int)($_POST['cv_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT file_path FROM cvs WHERE id = ? AND user_id = ?');
    $stmt->execute([$cvId, $userId]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare('DELETE FROM cvs WHERE id = ?')->execute([$cvId]);
        $fullPath = UPLOAD_PATH_CV . $row['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    header('Location: /web/user/cvs.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, file_name, file_path, is_default, created_at FROM cvs WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
$stmt->execute([$userId]);
$cvs = $stmt->fetchAll();

if (isset($_GET['success'])) {
    $success = 'CV uploaded successfully.';
}

$pageTitle = 'My CVs';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>My CVs</h1>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card form-card">
        <h2>Upload CV (PDF, max 5MB)</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="upload" value="1">
            <div class="form-group">
                <input type="file" name="cv_file" accept="application/pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

    <h2>Your CVs</h2>
    <?php if (empty($cvs)): ?>
        <p class="muted">No CVs uploaded yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>File name</th>
                    <th>Default</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cvs as $cv): ?>
                    <tr>
                        <td><?= e($cv['file_name']) ?></td>
                        <td><?= $cv['is_default'] ? 'Yes' : 'No' ?></td>
                        <td><?= date('M j, Y', strtotime($cv['created_at'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/download_cv.php?id=<?= (int)$cv['id'] ?>" class="btn btn-small">Download</a>
                            <?php if (!$cv['is_default']): ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="set_default" value="1">
                                    <input type="hidden" name="cv_id" value="<?= (int)$cv['id'] ?>">
                                    <button type="submit" class="btn btn-small">Set default</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this CV?');">
                                <input type="hidden" name="delete_cv" value="1">
                                <input type="hidden" name="cv_id" value="<?= (int)$cv['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="<?= BASE_URL ?>/user/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
