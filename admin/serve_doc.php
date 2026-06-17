<?php
/**
 * Serve uploaded verification documents to admin only
 */
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$type = $_GET['type'] ?? '';
$file = basename($_GET['file'] ?? '');

if (!$file || !$type) {
    echo 'Missing parameters.';
    exit;
}

if ($type === 'gov') {
    $dir = UPLOAD_PATH_GOV_ID;
} elseif ($type === 'cert') {
    $dir = UPLOAD_PATH_CERTIFICATIONS;
} else {
    echo 'Invalid type.';
    exit;
}

$fullPath = $dir . $file;
if (!file_exists($fullPath)) {
    echo 'File not found.';
    exit;
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
if ($mime === 'application/pdf') {
    header('Content-Disposition: inline; filename="' . $file . '"');
}
readfile($fullPath);
exit;
