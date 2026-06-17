<?php
/**
 * One-time setup: set admin password to "admin123"
 * Run from browser: http://localhost/web/database/setup_admin.php
 * Or from CLI: php database/setup_admin.php
 * Delete or restrict access after use.
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'admin' LIMIT 1");
    $stmt->execute([$hash]);
    if ($stmt->rowCount() > 0) {
        echo "Admin password has been set to: admin123\n";
    } else {
        echo "No admin user found. Import schema.sql first.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
