<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$userId = currentUserId();
$pdo = getDB();

// Fetch all conversations for the current user, with the other person's name and last message
$stmt = $pdo->prepare("
    SELECT c.id AS conv_id, c.updated_at,
           CASE WHEN c.user_one = ? THEN c.user_two ELSE c.user_one END AS other_id,
           u.name AS other_name, u.role AS other_role,
           (SELECT m.body FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_message,
           (SELECT m.created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) AS last_message_at,
           (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.sender_id != ? AND m.is_read = 0) AS unread_count
    FROM conversations c
    JOIN users u ON u.id = CASE WHEN c.user_one = ? THEN c.user_two ELSE c.user_one END
    WHERE c.user_one = ? OR c.user_two = ?
    ORDER BY c.updated_at DESC
");
$stmt->execute([$userId, $userId, $userId, $userId, $userId]);
$conversations = $stmt->fetchAll();

$pageTitle = 'Messages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Messages</h1>

    <?php if (empty($conversations)): ?>
        <div class="card">
            <p class="muted">No conversations yet. Start one by clicking "Message" on a freelancer's service page.</p>
        </div>
    <?php else: ?>
        <div class="conversation-list">
            <?php foreach ($conversations as $conv): ?>
                <a href="<?= BASE_URL ?>/messages/chat.php?with=<?= (int)$conv['other_id'] ?>" class="conversation-item <?= $conv['unread_count'] > 0 ? 'unread' : '' ?>">
                    <div class="conv-avatar"><?= strtoupper(mb_substr($conv['other_name'], 0, 1)) ?></div>
                    <div class="conv-body">
                        <div class="conv-header-row">
                            <span class="conv-name"><?= e($conv['other_name']) ?></span>
                            <span class="conv-role"><?= e(ucfirst($conv['other_role'])) ?></span>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="conv-badge"><?= (int)$conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="conv-preview"><?= e(mb_substr($conv['last_message'] ?? '', 0, 80)) ?><?= mb_strlen($conv['last_message'] ?? '') > 80 ? '...' : '' ?></p>
                        <span class="conv-time"><?= $conv['last_message_at'] ? date('M j, g:i A', strtotime($conv['last_message_at'])) : '' ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
