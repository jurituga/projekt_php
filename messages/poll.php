<?php
require_once __DIR__ . '/../config/init.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['messages' => [], 'unread_total' => 0]);
    exit;
}

$userId = currentUserId();
$otherId = (int)($_GET['with'] ?? 0);
$afterId = (int)($_GET['after'] ?? 0);
$pdo = getDB();

$newMessages = [];

if ($otherId && $afterId) {
    $uOne = min($userId, $otherId);
    $uTwo = max($userId, $otherId);

    $stmt = $pdo->prepare('SELECT id FROM conversations WHERE user_one = ? AND user_two = ?');
    $stmt->execute([$uOne, $uTwo]);
    $conv = $stmt->fetch();

    if ($conv) {
        $convId = (int) $conv['id'];

        // Mark incoming messages as read
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id = ? AND is_read = 0')
            ->execute([$convId, $otherId]);

        // Fetch new messages after the given ID
        $stmt = $pdo->prepare('SELECT id, sender_id, body, created_at FROM messages WHERE conversation_id = ? AND id > ? ORDER BY created_at ASC');
        $stmt->execute([$convId, $afterId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $r) {
            $newMessages[] = [
                'id'      => (int) $r['id'],
                'is_mine' => (int) $r['sender_id'] === $userId,
                'body'    => $r['body'],
                'time'    => date('g:i A', strtotime($r['created_at'])),
            ];
        }
    }
}

// Total unread across all conversations
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM messages m
    JOIN conversations c ON c.id = m.conversation_id
    WHERE m.is_read = 0 AND m.sender_id != ? AND (c.user_one = ? OR c.user_two = ?)
");
$stmt->execute([$userId, $userId, $userId]);
$unreadTotal = (int) $stmt->fetchColumn();

echo json_encode(['messages' => $newMessages, 'unread_total' => $unreadTotal]);
