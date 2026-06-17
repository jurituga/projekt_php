<?php
require_once __DIR__ . '/config/init.php';

$pdo = getDB();
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT s.id, s.title, s.description, s.price, s.price_type, s.created_at, u.name AS freelancer_name, u.id AS freelancer_id, fp.freelancer_type,
           COALESCE(AVG(fr.rating), 0) AS avg_rating, COUNT(fr.id) AS rating_count
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    LEFT JOIN freelancer_profiles fp ON fp.user_id = u.id
    LEFT JOIN freelancer_ratings fr ON fr.freelancer_id = u.id
    WHERE s.status = 'active'
";
$params = [];
if ($search !== '') {
    $sql .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$sql .= " GROUP BY s.id ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$pageTitle = 'Services';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container page-header">
    <h1>Services</h1>
    <form method="get" class="search-form inline-form">
        <input type="text" name="q" placeholder="Search services..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>

<div class="container">
    <?php if (empty($services)): ?>
        <p class="muted">No services found.</p>
    <?php else: ?>
        <div class="card-list">
            <?php foreach ($services as $svc): ?>
                <a href="<?= BASE_URL ?>/service.php?id=<?= (int)$svc['id'] ?>" class="listing-card">
                    <div class="listing-avatar av-service"><?= mb_strtoupper(mb_substr($svc['freelancer_name'], 0, 1)) ?></div>
                    <div class="listing-body">
                        <h3 class="listing-title"><?= e($svc['title']) ?></h3>
                        <div class="listing-info">
                            <span><?= e($svc['freelancer_name']) ?></span>
                            <?php if (in_array($svc['freelancer_type'] ?? '', ['electrician', 'plumber'], true)): ?>
                                <span class="listing-dot">&bull;</span>
                                <span class="listing-tag" style="margin:0"><?= e(ucfirst($svc['freelancer_type'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($svc['rating_count'] > 0): ?>
                            <div class="rating-summary" style="margin:.2rem 0">
                                <?= renderStars($svc['avg_rating']) ?>
                                <span class="rating-number"><?= number_format($svc['avg_rating'], 1) ?></span>
                                <span class="rating-count">(<?= (int)$svc['rating_count'] ?>)</span>
                            </div>
                        <?php endif; ?>
                        <p class="listing-desc"><?= e(mb_substr($svc['description'], 0, 120)) ?></p>
                        <div class="listing-footer">
                            <span class="listing-tag tag-price"><?= $svc['price'] ? '$' . number_format($svc['price']) . ' ' . $svc['price_type'] : 'Contact for price' ?></span>
                        </div>
                    </div>
                    <span class="listing-arrow">&rsaquo;</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
