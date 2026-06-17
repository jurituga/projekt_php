<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$userId = currentUserId();
$otherId = (int)($_GET['with'] ?? 0);
if (!$otherId || $otherId === $userId) {
    header('Location: ' . BASE_URL . '/messages/inbox.php');
    exit;
}

$pdo = getDB();

// Verify the other user exists and is not admin
$stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ? AND role != ?');
$stmt->execute([$otherId, ROLE_ADMIN]);
$otherUser = $stmt->fetch();
if (!$otherUser) {
    header('Location: ' . BASE_URL . '/messages/inbox.php');
    exit;
}

// Find or create conversation (always store smaller id as user_one)
$uOne = min($userId, $otherId);
$uTwo = max($userId, $otherId);

$stmt = $pdo->prepare('SELECT id FROM conversations WHERE user_one = ? AND user_two = ?');
$stmt->execute([$uOne, $uTwo]);
$conv = $stmt->fetch();

if (!$conv) {
    // Only create on POST (sending first message)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $convId = null;
    } else {
        $pdo->prepare('INSERT INTO conversations (user_one, user_two) VALUES (?, ?)')->execute([$uOne, $uTwo]);
        $convId = (int) $pdo->lastInsertId();
    }
} else {
    $convId = (int) $conv['id'];
}

$error = '';

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['message'] ?? '');
    if ($body === '') {
        $error = 'Message cannot be empty.';
    } else {
        if (!$convId) {
            $pdo->prepare('INSERT INTO conversations (user_one, user_two) VALUES (?, ?)')->execute([$uOne, $uTwo]);
            $convId = (int) $pdo->lastInsertId();
        }
        $pdo->prepare('INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)')->execute([$convId, $userId, $body]);
        $pdo->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')->execute([$convId]);
        // Redirect to prevent resubmission
        header('Location: ' . BASE_URL . '/messages/chat.php?with=' . $otherId);
        exit;
    }
}

// Mark messages from the other user as read
if ($convId) {
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id = ? AND is_read = 0')
        ->execute([$convId, $otherId]);
}

// Fetch messages
$messages = [];
if ($convId) {
    $stmt = $pdo->prepare('SELECT m.id, m.sender_id, m.body, m.created_at FROM messages m WHERE m.conversation_id = ? ORDER BY m.created_at ASC');
    $stmt->execute([$convId]);
    $messages = $stmt->fetchAll();
}

$pageTitle = 'Chat with ' . $otherUser['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container chat-container">
    <div class="chat-header-bar">
        <a href="<?= BASE_URL ?>/messages/inbox.php" class="btn btn-small btn-secondary">&larr; Inbox</a>
        <div class="chat-with">
            <span class="conv-avatar conv-avatar-sm"><?= strtoupper(mb_substr($otherUser['name'], 0, 1)) ?></span>
            <strong><?= e($otherUser['name']) ?></strong>
            <span class="conv-role"><?= e(ucfirst($otherUser['role'])) ?></span>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        <?php if (empty($messages)): ?>
            <p class="muted chat-empty">No messages yet. Say hello!</p>
        <?php else: ?>
            <?php
            $lastDate = '';
            foreach ($messages as $msg):
                $msgDate = date('M j, Y', strtotime($msg['created_at']));
                if ($msgDate !== $lastDate):
                    $lastDate = $msgDate;
            ?>
                <div class="chat-date-divider"><span><?= $msgDate ?></span></div>
            <?php endif; ?>
                <div class="chat-bubble <?= $msg['sender_id'] == $userId ? 'chat-mine' : 'chat-theirs' ?>">
                    <p><?= nl2br(e($msg['body'])) ?></p>
                    <span class="chat-time"><?= date('g:i A', strtotime($msg['created_at'])) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="chat-input-bar" id="chatForm">
        <textarea name="message" id="chatInput" rows="1" placeholder="Type a message..." required></textarea>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>

<script>
// Auto-scroll to bottom
var chatBox = document.getElementById('chatMessages');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

// Submit on Enter (Shift+Enter for new line)
var input = document.getElementById('chatInput');
if (input) {
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (this.value.trim()) document.getElementById('chatForm').submit();
        }
    });
}

// Poll for new messages every 5 seconds
var convWith = <?= (int)$otherId ?>;
var lastMsgId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
setInterval(function() {
    fetch('<?= BASE_URL ?>/messages/poll.php?with=' + convWith + '&after=' + lastMsgId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(function(m) {
                    var div = document.createElement('div');
                    div.className = 'chat-bubble ' + (m.is_mine ? 'chat-mine' : 'chat-theirs');
                    div.innerHTML = '<p>' + escHtml(m.body) + '</p><span class="chat-time">' + m.time + '</span>';
                    chatBox.appendChild(div);
                    lastMsgId = m.id;
                });
                chatBox.scrollTop = chatBox.scrollHeight;
            }
            if (data.unread_total !== undefined) {
                var badge = document.getElementById('msgBadge');
                if (badge) badge.textContent = data.unread_total > 0 ? data.unread_total : '';
                if (badge) badge.style.display = data.unread_total > 0 ? 'inline-flex' : 'none';
            }
        })
        .catch(function() {});
}, 5000);

function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML.replace(/\n/g, '<br>');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
