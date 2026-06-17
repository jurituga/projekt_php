<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pdo = getDB();

$stmt = $pdo->query("
    SELECT s.id, s.title, s.status, s.price, s.price_type, s.created_at, u.name AS freelancer_name
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    ORDER BY s.created_at DESC
");
$services = $stmt->fetchAll();

$pageTitle = 'Manage Services';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Manage Services</h1>
    <p><a href="<?= BASE_URL ?>/admin/index.php">&larr; Admin Panel</a></p>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Freelancer</th>
                <th>Price</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td><?= (int)$s['id'] ?></td>
                    <td><a href="<?= BASE_URL ?>/service.php?id=<?= (int)$s['id'] ?>"><?= e($s['title']) ?></a></td>
                    <td><?= e($s['freelancer_name']) ?></td>
                    <td><?= $s['price'] ? '$' . number_format($s['price']) . ' ' . $s['price_type'] : '—' ?></td>
                    <td><span class="status status-<?= e($s['status']) ?>"><?= e($s['status']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
                    <td>
                        <form method="post" action="<?= BASE_URL ?>/admin/service_action.php" style="display:inline" onsubmit="return confirm('Delete this service?');">
                            <input type="hidden" name="service_id" value="<?= (int)$s['id'] ?>">
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
