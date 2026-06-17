<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT id, title, description, price, price_type, status, created_at FROM services WHERE freelancer_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$services = $stmt->fetchAll();

$pageTitle = 'My Services';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>My Services</h1>
    <p><a href="<?= BASE_URL ?>/freelancer/service_edit.php" class="btn btn-primary">Add Service</a></p>

    <?php if (empty($services)): ?>
        <p class="muted">No services yet. Create one to get requests.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                    <tr>
                        <td><strong><?= e($s['title']) ?></strong></td>
                        <td><?= $s['price'] ? '$' . number_format($s['price']) . ' ' . $s['price_type'] : '—' ?></td>
                        <td><span class="status status-<?= e($s['status']) ?>"><?= e($s['status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/freelancer/service_edit.php?id=<?= (int)$s['id'] ?>" class="btn btn-small">Edit</a>
                            <form method="post" action="<?= BASE_URL ?>/freelancer/service_delete.php" style="display:inline" onsubmit="return confirm('Delete this service?');">
                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="<?= BASE_URL ?>/freelancer/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
