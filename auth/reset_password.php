<?php
require_once __DIR__ . '/../config/init.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = false;
$validToken = false;

$pdo = getDB();

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    $stmt = $pdo->prepare('SELECT prt.id, prt.user_id, prt.expires_at, prt.used, u.name FROM password_reset_tokens prt JOIN users u ON u.id = prt.user_id WHERE prt.token = ?');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $error = 'Invalid reset link. Please request a new one.';
    } elseif ($reset['used']) {
        $error = 'This reset link has already been used.';
    } elseif (strtotime($reset['expires_at']) < time()) {
        $error = 'This reset link has expired. Please request a new one.';
    } else {
        $validToken = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hashed, $reset['user_id']]);
        $pdo->prepare('UPDATE password_reset_tokens SET used = 1 WHERE id = ?')->execute([$reset['id']]);
        $success = true;
        $validToken = false;
    }
}

$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Reset Password</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">Your password has been reset successfully.</div>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary" style="width:100%">Log in with new password</a></p>
        <?php elseif ($error && !$validToken): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
            <p style="margin-top:1rem"><a href="<?= BASE_URL ?>/auth/forgot_password.php" class="btn btn-ghost btn-sm">Request a new link</a></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <p style="color:var(--text-sub);font-size:.88rem;margin-bottom:1.25rem">Hi <?= e($reset['name']) ?>, enter your new password below.</p>
            <form method="post" action="">
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="form-group">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" required autofocus minlength="6" placeholder="At least 6 characters">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6" placeholder="Repeat password">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
