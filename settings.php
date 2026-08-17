<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$time_now = time();
$time_now_Y = date( 'Y' , $time_now );
$time_now_m = date( 'm' , $time_now );
$time_now_d = date( 'd' , $time_now );

if( isset( $_SERVER['HTTP_CLIENT_IP'] ) )
	$ip_current_user = $_SERVER['HTTP_CLIENT_IP'];
elseif( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
	$ip_current_user = $_SERVER['HTTP_X_FORWARDED_FOR'];
elseif( isset( $_SERVER['HTTP_X_FORWARDED'] ) )
	$ip_current_user = $_SERVER['HTTP_X_FORWARDED'];
elseif( isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] ) )
	$ip_current_user = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
elseif( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) )
	$ip_current_user = $_SERVER['HTTP_FORWARDED_FOR'];
elseif( isset( $_SERVER['HTTP_FORWARDED'] ) )
	$ip_current_user = $_SERVER['HTTP_FORWARDED'];
elseif( isset( $_SERVER['REMOTE_ADDR'] ) )
	$ip_current_user = $_SERVER['REMOTE_ADDR'];
else
	$ip_current_user = '0.0.0.0';

// Email's
define('EMAIL_FEEDBACK', $_ENV['EMAIL_FEEDBACK']);

// Урл сайта
define('SITE_URL', $_ENV['SITE_URL']);

// База данных

// Показ ошибок: ERRMODE_SILENT - не показывать ошибки, ERRMODE_EXCEPTION - показывать ошибки
const ERRMODE_SET = PDO::ERRMODE_EXCEPTION;

define('DB_HOSTNAME', $_ENV['DB_HOST']);
define('DB_DATABASE', $_ENV['DB_DATABASE']);
define('DB_USERNAME', $_ENV['DB_USERNAME']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);

define('APP_SECRET_KEY', $_ENV['APP_SECRET_KEY']);

// Город по умолчанию
if (!isset($_SESSION['city'])) $_SESSION['city'] = [
	'id' 		=> 365,
	'name' 		=> 'Москва',
];

// Админская часть приложения
$settings = [
    'admin_href' => '/mukadhoha96/',
	'favicon' => '/uploads/fav-icon/favicon.png',
];

/* define('LOGIN', $settings['login']);
define('PASSWORD', $settings['password']); */
define('ADMIN_HREF', $settings['admin_href']);

return $settings;

/*  ini_set('error_reporting', E_ALL);
 ini_set('display_errors', 1	);
 ini_set('display_startup_errors', 1); */