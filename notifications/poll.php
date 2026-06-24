<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['unread_total' => 0]);
    exit;
}

echo json_encode(['unread_total' => unreadNotificationCount(currentUserId())]);
