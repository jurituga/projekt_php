<?php
require_once __DIR__ . '/config/init.php';

if (!isLoggedIn() || !in_array(currentUserRole(), [ROLE_USER, ROLE_COMPANY], true)) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$requestId = (int)($_GET['request_id'] ?? $_POST['request_id'] ?? 0);
if (!$requestId) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT sr.id, sr.service_id, sr.status, s.title AS service_title, s.freelancer_id, u.name AS freelancer_name
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    JOIN users u ON u.id = s.freelancer_id
    WHERE sr.id = ? AND sr.requester_id = ? AND sr.status = ?
");
$stmt->execute([$requestId, currentUserId(), SERVICE_REQUEST_COMPLETED]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM freelancer_ratings WHERE service_request_id = ?');
$stmt->execute([$requestId]);
$alreadyRated = (bool)$stmt->fetch();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyRated) {
    $rating = (int)($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    }

    $uploadedFiles = [];
    if (!$error && !empty($_FILES['review_images']['name'][0])) {
        $fileCount = count($_FILES['review_images']['name']);
        if ($fileCount > UPLOAD_MAX_RATING_IMAGES) {
            $error = 'You can upload a maximum of ' . UPLOAD_MAX_RATING_IMAGES . ' images.';
        } else {
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['review_images']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $tmpName = $_FILES['review_images']['tmp_name'][$i];
                $origName = $_FILES['review_images']['name'][$i];
                $size = $_FILES['review_images']['size'][$i];
                $mime = mime_content_type($tmpName);

                if (!in_array($mime, ALLOWED_RATING_IMAGE_TYPES, true)) {
                    $error = 'Only JPG, PNG, GIF and WebP images are allowed.';
                    break;
                }
                if ($size > UPLOAD_MAX_RATING_IMG_SIZE) {
                    $error = 'Each image must be under 5 MB.';
                    break;
                }

                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $ext = 'jpg';
                }
                $uniqueName = uniqid('rev_', true) . '.' . $ext;
                $uploadedFiles[] = ['tmp' => $tmpName, 'name' => $uniqueName];
            }
        }
    }

    if (!$error) {
        @mkdir(UPLOAD_PATH_RATING_IMAGES, 0777, true);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO freelancer_ratings (freelancer_id, reviewer_id, service_request_id, rating, review) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$request['freelancer_id'], currentUserId(), $requestId, $rating, $review ?: null]);
            $ratingId = (int)$pdo->lastInsertId();

            foreach ($uploadedFiles as $file) {
                $destPath = UPLOAD_PATH_RATING_IMAGES . $file['name'];
                if (move_uploaded_file($file['tmp'], $destPath)) {
                    $pdo->prepare('INSERT INTO rating_images (rating_id, file_path) VALUES (?, ?)')
                        ->execute([$ratingId, $file['name']]);
                }
            }

            $pdo->commit();
            $success = true;
            $alreadyRated = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Something went wrong. Please try again.';
        }
    }
}

$pageTitle = 'Rate Freelancer';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Rate Freelancer</h1>
    </div>

    <div class="card" style="margin-bottom:1.25rem">
        <p><strong>Service:</strong> <?= e($request['service_title']) ?></p>
        <p><strong>Freelancer:</strong> <?= e($request['freelancer_name']) ?></p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">Thank you! Your rating has been submitted.</div>
        <p><a href="<?= BASE_URL ?>/user/service_requests.php">&larr; Back to My Requests</a></p>
    <?php elseif ($alreadyRated): ?>
        <div class="alert alert-success">You have already rated this service.</div>
        <p><a href="<?= BASE_URL ?>/user/service_requests.php">&larr; Back to My Requests</a></p>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="form-card">
            <input type="hidden" name="request_id" value="<?= (int)$requestId ?>">

            <div class="form-group">
                <label>Rating <span class="required">*</span></label>
                <div class="star-rating-input">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                        <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">&#9733;</label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="review">Review (optional)</label>
                <textarea id="review" name="review" rows="4" placeholder="Share your experience with this freelancer..."></textarea>
            </div>

            <div class="form-group">
                <label for="review_images">Photos (optional, max <?= UPLOAD_MAX_RATING_IMAGES ?>)</label>
                <span class="form-hint">Upload photos of the completed work. JPG, PNG, GIF or WebP, max 5 MB each.</span>
                <input type="file" id="review_images" name="review_images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                <div class="image-preview-grid" id="imagePreviewGrid"></div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Rating</button>
        </form>
        <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/user/service_requests.php">&larr; Back to My Requests</a></p>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('review_images');
    var grid = document.getElementById('imagePreviewGrid');
    if (!input || !grid) return;

    input.addEventListener('change', function () {
        grid.innerHTML = '';
        var files = Array.from(this.files);
        var max = <?= UPLOAD_MAX_RATING_IMAGES ?>;
        if (files.length > max) {
            alert('You can upload a maximum of ' + max + ' images.');
            this.value = '';
            return;
        }
        files.forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var thumb = document.createElement('div');
                thumb.className = 'img-preview-thumb';
                thumb.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                grid.appendChild(thumb);
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
