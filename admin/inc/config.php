<?php
// Error Reporting Turn On
ini_set('error_reporting', E_ALL);

// Setting up the time zone
date_default_timezone_set('Asia/kathmandu');

// Host Name
$dbhost = 'localhost';

// Database Name
$dbname   = 'resinnep_ecommerceweb';

// Database Username
$dbuser = 'root';

// Database Password
$dbpass = '';

// Defining base url
define("BASE_URL", "http://localhost/koshi_supplier/");

// Getting Admin url
define("ADMIN_URL", BASE_URL . "admin" . "/");
// SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'nirajkarna66@gmail.com');
define('SMTP_PASS', 'eptg ikjc lbbd yosq');
define('SMTP_PORT', 465);

define('SMTP_FROM_EMAIL', 'nirajkarna66@gmail.com');
define('SMTP_FROM_NAME', 'Koshi Supplier');

define('SMTP_REPLYTO_EMAIL', 'nirajkarna66@gmail.com');
define('SMTP_REPLYTO_NAME', 'Koshi Supplier');

try {
	$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch( PDOException $exception ) {
	echo "Connection error :" . $exception->getMessage();
}