<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_USER);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT * FROM user_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch();
if (!$profile) {
    $pdo->prepare('INSERT INTO user_profiles (user_id) VALUES (?)')->execute([$userId]);
    $profile = ['user_id' => $userId, 'phone' => '', 'address' => '', 'headline' => ''];
}

$stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $headline = trim($_POST['headline'] ?? '');

    if (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    } else {
        $pdo->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $userId]);
        $pdo->prepare('UPDATE user_profiles SET phone = ?, address = ?, headline = ? WHERE user_id = ?')->execute([$phone, $address, $headline, $userId]);
        $_SESSION['user_name'] = $name;
        $success = true;
        $user['name'] = $name;
        $profile['phone'] = $phone;
        $profile['address'] = $address;
        $profile['headline'] = $headline;
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>My Profile</h1>
    <?php if ($success): ?>
        <div class="alert alert-success">Profile updated.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="form-card">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <p class="muted"><?= e($user['email']) ?> (cannot change)</p>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= e($profile['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="2"><?= e($profile['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="headline">Headline</label>
            <input type="text" id="headline" name="headline" value="<?= e($profile['headline'] ?? '') ?>" placeholder="e.g. Software Developer">
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
    <p><a href="<?= BASE_URL ?>/user/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
