<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pdo = getDB();

$stmt = $pdo->query("
    SELECT id, name, email, role, status, created_at
    FROM users
    WHERE role != 'admin'
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Manage Users</h1>
    <p><a href="<?= BASE_URL ?>/admin/index.php">&larr; Admin Panel</a></p>

    <?php
    $pendingUsers = array_filter($users, fn($u) => $u['status'] === USER_STATUS_PENDING);
    if (!empty($pendingUsers)):
    ?>
    <div class="alert alert-success" style="margin-bottom:1rem;">
        <strong><?= count($pendingUsers) ?> registration(s) pending approval.</strong> Approve companies and freelancers so they can log in.
    </div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['role']) ?></td>
                    <td><span class="status status-<?= e($u['status']) ?>"><?= e($u['status']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['role'] === ROLE_COMPANY || $u['role'] === ROLE_FREELANCER): ?>
                            <a href="<?= BASE_URL ?>/admin/user_docs.php?user_id=<?= (int)$u['id'] ?>" class="btn btn-small btn-secondary">Review</a>
                        <?php endif; ?>
                        <?php if ($u['status'] === USER_STATUS_PENDING): ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" class="btn btn-small">Approve</button>
                            </form>
                        <?php elseif ($u['status'] === USER_STATUS_BLOCKED): ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" class="btn btn-small">Activate</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="action" value="block">
                                <button type="submit" class="btn btn-small">Block</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline" onsubmit="return confirm('Delete this user and all related data?');">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
