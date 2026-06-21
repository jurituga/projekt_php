<?php

$path = dirname(__DIR__).'/vendor/laravel/framework/config/database.php';

if (! is_file($path)) {
    exit(0);
}

$content = file_get_contents($path);

if (! str_contains($content, 'Pdo\\Mysql::ATTR_SSL_CA')) {
    exit(0);
}

$content = preg_replace(
    '/\$pdoMysqlSslCaAttr = PHP_VERSION_ID >= 80500\s*\?\s*\\\\Pdo\\\\Mysql::ATTR_SSL_CA\s*:\s*PDO::MYSQL_ATTR_SSL_CA;/',
    '$pdoMysqlSslCaAttr = 1008; // NativePHP bundled PHP lacks Pdo\\Mysql',
    $content,
    1,
    $count
);

if ($count > 0) {
    file_put_contents($path, $content);
}
