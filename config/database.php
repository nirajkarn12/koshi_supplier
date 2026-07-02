<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kathmandu');

$dbhost = 'localhost';
$dbname = 'resinnep_ecommerceweb';
$dbuser = 'root';
$dbpass = '';

$documentRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?? ''), '/');
$projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..') ?? ''), '/');

if ($documentRoot !== '' && $projectRoot !== '' && strpos($projectRoot, $documentRoot) === 0) {
    $relativePath = substr($projectRoot, strlen($documentRoot));
    $basePath = '/' . ltrim($relativePath, '/') . '/';
} else {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    $basePath = ($scriptDir === '' || $scriptDir === '.') ? '/' : $scriptDir . '/';
}

if ($basePath === '//') {
    $basePath = '/';
}

define('BASE_URL', $basePath);
define('ASSET_URL', BASE_URL . 'assets/');
define('UPLOAD_URL', ASSET_URL . 'uploads/');

define('SITE_NAME', 'koshi supplier');

try {
    $pdo = new PDO("mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
