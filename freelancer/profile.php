<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT * FROM freelancer_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch();
if (!$profile) {
    $pdo->prepare('INSERT INTO freelancer_profiles (user_id) VALUES (?)')->execute([$userId]);
    $profile = ['user_id' => $userId, 'bio' => '', 'skills' => '', 'hourly_rate' => null];
}

$stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $freelancerType = trim($_POST['freelancer_type'] ?? 'general');
    $allowedTypes = ['general', 'electrician', 'plumber'];
    if (!in_array($freelancerType, $allowedTypes, true)) $freelancerType = 'general';
    $bio = trim($_POST['bio'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $hourlyRate = isset($_POST['hourly_rate']) && $_POST['hourly_rate'] !== '' ? (float)$_POST['hourly_rate'] : null;
    $govIdRef = trim($_POST['government_id_ref'] ?? '');
    $qualifications = trim($_POST['qualifications'] ?? '');

    $govIdPath = isset($profile['government_id_path']) ? $profile['government_id_path'] : null;
    $certPath = isset($profile['certification_path']) ? $profile['certification_path'] : null;

    if (!empty($_FILES['government_id_file']['name']) && $_FILES['government_id_file']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['government_id_file']['size'] > UPLOAD_MAX_DOC_SIZE) {
            $error = 'Government ID file too large (max 5MB).';
        } elseif (!in_array($_FILES['government_id_file']['type'], ALLOWED_DOC_TYPES, true)) {
            $error = 'Government ID must be PDF or image (JPEG, PNG, GIF).';
        } else {
            $ext = pathinfo($_FILES['government_id_file']['name'], PATHINFO_EXTENSION) ?: 'pdf';
            $filename = 'gov_' . $userId . '_' . time() . '.' . strtolower($ext);
            if (move_uploaded_file($_FILES['government_id_file']['tmp_name'], UPLOAD_PATH_GOV_ID . $filename)) {
                $govIdPath = $filename;
            } else {
                $error = 'Could not save government ID file.';
            }
        }
    }

    if (!$error && !empty($_FILES['certification_file']['name']) && $_FILES['certification_file']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['certification_file']['size'] > UPLOAD_MAX_DOC_SIZE) {
            $error = 'Certification file too large (max 5MB).';
        } elseif (!in_array($_FILES['certification_file']['type'], ALLOWED_DOC_TYPES, true)) {
            $error = 'Certification must be PDF or image (JPEG, PNG, GIF).';
        } else {
            $ext = pathinfo($_FILES['certification_file']['name'], PATHINFO_EXTENSION) ?: 'pdf';
            $filename = 'cert_' . $userId . '_' . time() . '.' . strtolower($ext);
            if (move_uploaded_file($_FILES['certification_file']['tmp_name'], UPLOAD_PATH_CERTIFICATIONS . $filename)) {
                $certPath = $filename;
            } else {
                $error = 'Could not save certification file.';
            }
        }
    }

    if (!$error && strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    }

    if (!$error) {
        $pdo->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $userId]);

        $hasVerificationCols = array_key_exists('government_id_ref', $profile);
        if ($hasVerificationCols) {
            $pdo->prepare('UPDATE freelancer_profiles SET freelancer_type = ?, bio = ?, skills = ?, hourly_rate = ?, government_id_ref = ?, government_id_path = ?, qualifications = ?, certification_path = ? WHERE user_id = ?')
                ->execute([$freelancerType, $bio, $skills, $hourlyRate, $govIdRef ?: null, $govIdPath, $qualifications ?: null, $certPath, $userId]);
        } else {
            $pdo->prepare('UPDATE freelancer_profiles SET freelancer_type = ?, bio = ?, skills = ?, hourly_rate = ? WHERE user_id = ?')
                ->execute([$freelancerType, $bio, $skills, $hourlyRate, $userId]);
        }

        $_SESSION['user_name'] = $name;
        $success = true;
        $user['name'] = $name;
        $profile['bio'] = $bio;
        $profile['skills'] = $skills;
        $profile['hourly_rate'] = $hourlyRate;
        $profile['freelancer_type'] = $freelancerType;
        $profile['government_id_ref'] = $govIdRef;
        $profile['government_id_path'] = $govIdPath;
        $profile['qualifications'] = $qualifications;
        $profile['certification_path'] = $certPath;
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Freelancer Profile</h1>
    <?php if ($success): ?>
        <div class="alert alert-success">Profile updated.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-card">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <p class="muted"><?= e($user['email']) ?></p>
        </div>
        <div class="form-group">
            <label for="freelancer_type">Freelancer type</label>
            <select id="freelancer_type" name="freelancer_type">
                <option value="general" <?= ($profile['freelancer_type'] ?? 'general') === 'general' ? 'selected' : '' ?>>General Freelancer</option>
                <option value="electrician" <?= ($profile['freelancer_type'] ?? '') === 'electrician' ? 'selected' : '' ?>>Electrician</option>
                <option value="plumber" <?= ($profile['freelancer_type'] ?? '') === 'plumber' ? 'selected' : '' ?>>Plumber</option>
            </select>
            <span class="form-hint">Electricians and Plumbers can manage daily availability for bookings.</span>
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4"><?= e($profile['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="skills">Skills (comma-separated)</label>
            <input type="text" id="skills" name="skills" value="<?= e($profile['skills'] ?? '') ?>" placeholder="PHP, MySQL, JavaScript">
        </div>
        <div class="form-group">
            <label for="hourly_rate">Hourly rate ($)</label>
            <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" value="<?= isset($profile['hourly_rate']) && $profile['hourly_rate'] !== null ? e((string)$profile['hourly_rate']) : '' ?>">
        </div>

        <h2 class="form-section-title">Verification &amp; certifications</h2>
        <div class="form-group">
            <label for="government_id_ref">Government ID number or reference</label>
            <input type="text" id="government_id_ref" name="government_id_ref" value="<?= e($profile['government_id_ref'] ?? '') ?>" placeholder="ID number">
        </div>
        <div class="form-group">
            <label for="government_id_file">Government ID (upload)</label>
            <input type="file" id="government_id_file" name="government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif,application/pdf,image/jpeg,image/png,image/gif">
            <span class="form-hint">PDF or image. Max 5MB.</span>
            <?php if (!empty($profile['government_id_path'])): ?>
                <p class="muted">Current file uploaded. Upload a new file to replace.</p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="qualifications">Licenses / certifications / qualifications list</label>
            <textarea id="qualifications" name="qualifications" rows="3" placeholder="List licenses, certifications, or qualifications..."><?= e($profile['qualifications'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="certification_file">Certification or license document (upload)</label>
            <input type="file" id="certification_file" name="certification_file" accept=".pdf,.jpg,.jpeg,.png,.gif,application/pdf,image/jpeg,image/png,image/gif">
            <span class="form-hint">PDF or image. Max 5MB.</span>
            <?php if (!empty($profile['certification_path'])): ?>
                <p class="muted">Current file uploaded. Upload a new file to replace.</p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
    <p><a href="<?= BASE_URL ?>/freelancer/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
