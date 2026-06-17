<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$userId = (int)($_GET['user_id'] ?? 0);
if (!$userId) {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT id, name, email, role, status, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$company = null;
$freelancer = null;

if ($user['role'] === ROLE_COMPANY) {
    $stmt = $pdo->prepare('SELECT * FROM companies WHERE user_id = ?');
    $stmt->execute([$userId]);
    $company = $stmt->fetch();
}

if ($user['role'] === ROLE_FREELANCER) {
    $stmt = $pdo->prepare('SELECT * FROM freelancer_profiles WHERE user_id = ?');
    $stmt->execute([$userId]);
    $freelancer = $stmt->fetch();
}

$pageTitle = 'Review: ' . $user['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <p><a href="<?= BASE_URL ?>/admin/users.php">&larr; Back to Users</a></p>
    <h1>Review: <?= e($user['name']) ?></h1>
    <table class="data-table" style="margin-bottom:1.5rem;">
        <tr><th>Email</th><td><?= e($user['email']) ?></td></tr>
        <tr><th>Role</th><td><?= e($user['role']) ?></td></tr>
        <tr><th>Status</th><td><span class="status status-<?= e($user['status']) ?>"><?= e($user['status']) ?></span></td></tr>
        <tr><th>Registered</th><td><?= date('M j, Y H:i', strtotime($user['created_at'])) ?></td></tr>
    </table>

    <?php if ($company): ?>
        <div class="card">
            <h2>Company details</h2>
            <table class="data-table">
                <tr><th>Company Name</th><td><?= e($company['company_name'] ?? '—') ?></td></tr>
                <tr><th>Industry</th><td><?= e($company['industry'] ?? '—') ?></td></tr>
                <tr><th>Description</th><td><?= e($company['description'] ?? '—') ?></td></tr>
                <tr><th>Website</th><td><?= $company['website'] ? '<a href="' . e($company['website']) . '" target="_blank">' . e($company['website']) . '</a>' : '—' ?></td></tr>
                <tr><th>Phone</th><td><?= e($company['phone'] ?? '—') ?></td></tr>
            </table>

            <h3 style="margin-top:1.25rem;">Verification &amp; trust</h3>
            <table class="data-table">
                <tr><th>Business Registration No.</th><td><?= e($company['business_registration_number'] ?? '—') ?></td></tr>
                <tr><th>Tax ID / VAT</th><td><?= e($company['tax_id_vat'] ?? '—') ?></td></tr>
                <tr>
                    <th>Business Registration Document</th>
                    <td>
                        <?php if (!empty($company['government_id_ref'])): ?>
                            <?php
                            $path = UPLOAD_PATH_GOV_ID . $company['government_id_ref'];
                            $ext = strtolower(pathinfo($company['government_id_ref'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= BASE_URL ?>/admin/serve_doc.php?type=gov&file=<?= urlencode($company['government_id_ref']) ?>" style="max-width:400px;max-height:300px;border:1px solid var(--color-border);border-radius:var(--radius-sm);display:block;margin-bottom:0.5rem;">
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/admin/serve_doc.php?type=gov&file=<?= urlencode($company['government_id_ref']) ?>" class="btn btn-small">Download</a>
                        <?php else: ?>
                            <span class="muted">Not uploaded</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Government ID</th>
                    <td>
                        <?php if (!empty($company['government_id_path'])): ?>
                            <?php
                            $ext = strtolower(pathinfo($company['government_id_path'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= BASE_URL ?>/admin/serve_doc.php?type=gov&file=<?= urlencode($company['government_id_path']) ?>" style="max-width:400px;max-height:300px;border:1px solid var(--color-border);border-radius:var(--radius-sm);display:block;margin-bottom:0.5rem;">
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/admin/serve_doc.php?type=gov&file=<?= urlencode($company['government_id_path']) ?>" class="btn btn-small">Download</a>
                        <?php else: ?>
                            <span class="muted">Not uploaded</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($freelancer): ?>
        <div class="card">
            <h2>Freelancer profile</h2>
            <table class="data-table">
                <tr><th>Bio</th><td><?= nl2br(e($freelancer['bio'] ?? '—')) ?></td></tr>
                <tr><th>Skills</th><td><?= e($freelancer['skills'] ?? '—') ?></td></tr>
                <tr><th>Hourly Rate</th><td><?= $freelancer['hourly_rate'] ? '$' . number_format($freelancer['hourly_rate'], 2) : '—' ?></td></tr>
            </table>

            <h3 style="margin-top:1.25rem;">Verification &amp; certifications</h3>
            <table class="data-table">
                <tr>
                    <th>Government ID</th>
                    <td>
                        <?php if (!empty($freelancer['government_id_path'])): ?>
                            <?php
                            $ext = strtolower(pathinfo($freelancer['government_id_path'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= BASE_URL ?>/admin/serve_doc.php?type=gov&file=<?= urlencode($freelancer['government_id_path']) ?>" style="max-width:400px;max-height:300px;border:1px solid var(--color-border);border-radius:var(--radius-sm);display:block;margin-bottom:0.5rem;">
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/admin/serve_doc.php?type=gov&file=<?= urlencode($freelancer['government_id_path']) ?>" class="btn btn-small">Download</a>
                        <?php else: ?>
                            <span class="muted">Not uploaded</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr><th>Qualifications</th><td><?= nl2br(e($freelancer['qualifications'] ?? '—')) ?></td></tr>
                <tr>
                    <th>Certification / license doc</th>
                    <td>
                        <?php if (!empty($freelancer['certification_path'])): ?>
                            <?php
                            $ext = strtolower(pathinfo($freelancer['certification_path'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= BASE_URL ?>/admin/serve_doc.php?type=cert&file=<?= urlencode($freelancer['certification_path']) ?>" style="max-width:400px;max-height:300px;border:1px solid var(--color-border);border-radius:var(--radius-sm);display:block;margin-bottom:0.5rem;">
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/admin/serve_doc.php?type=cert&file=<?= urlencode($freelancer['certification_path']) ?>" class="btn btn-small">Download</a>
                        <?php else: ?>
                            <span class="muted">Not uploaded</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top:1.5rem;" class="actions">
        <?php if ($user['status'] === USER_STATUS_PENDING): ?>
            <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="action" value="activate">
                <button type="submit" class="btn btn-primary">Approve</button>
            </form>
        <?php elseif ($user['status'] === USER_STATUS_BLOCKED): ?>
            <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="action" value="activate">
                <button type="submit" class="btn btn-primary">Activate</button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="action" value="block">
                <button type="submit" class="btn btn-secondary">Block</button>
            </form>
        <?php endif; ?>
        <form method="post" action="<?= BASE_URL ?>/admin/user_action.php" style="display:inline" onsubmit="return confirm('Delete this user?');">
            <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
