<?php
require_once __DIR__ . '/../config/init.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';
$success = false;
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $pdo->prepare('UPDATE password_reset_tokens SET used = 1 WHERE user_id = ? AND used = 0')->execute([$user['id']]);

            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $token, $expiresAt]);

            $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/auth/reset_password.php?token=' . $token;

            $subject = 'Vacanto — Password Reset';
            $message = "Hi {$user['name']},\n\nClick the link below to reset your password:\n{$resetUrl}\n\nThis link expires in 1 hour.\n\nIf you did not request this, ignore this email.";
            $headers = "From: noreply@vacanto.local\r\nContent-Type: text/plain; charset=UTF-8";
            @mail($email, $subject, $message, $headers);

            $resetLink = $resetUrl;
        }

        $success = true;
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Forgot Password</h1>
        <p style="color:var(--text-sub);font-size:.88rem;margin-bottom:1.25rem">Enter the email address linked to your account and we'll generate a reset link.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">If an account with that email exists, a password reset link has been generated.</div>
            <?php if ($resetLink): ?>
                <div class="card" style="margin-top:1rem;padding:1rem">
                    <p style="font-size:.8rem;font-weight:600;color:var(--text-sub);margin-bottom:.5rem">Reset link (local dev &mdash; no mail server):</p>
                    <a href="<?= e($resetLink) ?>" style="word-break:break-all;font-size:.82rem"><?= e($resetLink) ?></a>
                </div>
            <?php endif; ?>
            <p style="margin-top:1.25rem"><a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-ghost btn-sm">&larr; Back to login</a></p>
        <?php else: ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Send Reset Link</button>
            </form>
            <p class="auth-link" style="margin-top:1rem"><a href="<?= BASE_URL ?>/auth/login.php">&larr; Back to login</a></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
