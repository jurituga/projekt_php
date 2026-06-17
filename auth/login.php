<?php
require_once __DIR__ . '/../config/init.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $is_admin_login = isset($_POST['admin_login']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid email or password.';
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Invalid email or password.';
        } elseif ($user['status'] === USER_STATUS_BLOCKED) {
            $error = 'Your account has been blocked. Contact support.';
        } elseif ($user['status'] === USER_STATUS_PENDING && $user['role'] !== ROLE_ADMIN) {
            $error = 'Your account is pending approval.';
        } elseif ($is_admin_login && $user['role'] !== ROLE_ADMIN) {
            $error = 'Admin access only. Use regular login.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === ROLE_ADMIN) {
                header('Location: ' . BASE_URL . '/admin/index.php');
            } elseif ($user['role'] === ROLE_COMPANY) {
                header('Location: ' . BASE_URL . '/company/dashboard.php');
            } elseif ($user['role'] === ROLE_FREELANCER) {
                header('Location: ' . BASE_URL . '/freelancer/dashboard.php');
            } else {
                header('Location: ' . BASE_URL . '/user/dashboard.php');
            }
            exit;
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Login</h1>
        <?php if (isset($_GET['pending']) && $_GET['pending'] === '1'): ?>
            <div class="alert alert-success">Registration successful. Your account is pending admin approval. You will be able to log in once an administrator approves your request.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group checkbox" style="display:flex;align-items:center;justify-content:space-between">
                <label><input type="checkbox" name="admin_login" value="1"> Admin login</label>
                <a href="<?= BASE_URL ?>/auth/forgot_password.php" style="font-size:.82rem">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="auth-link">Don't have an account? <a href="<?= BASE_URL ?>/auth/register.php">Register</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
