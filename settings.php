<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

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
const EMAIL_FEEDBACK = 'urfaq.ru@mail.ru';

// Урл сайта
const SITE_URL = 'yrfak/';

// База данных

// Показ ошибок: ERRMODE_SILENT - не показывать ошибки, ERRMODE_EXCEPTION - показывать ошибки
const ERRMODE_SET = PDO::ERRMODE_EXCEPTION;

const DB_HOSTNAME = 'localhost';
const DB_DATABASE = 'urfac';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';

// Город по умолчанию
if (!isset($_SESSION['city'])) $_SESSION['city'] = [
	'id' 		=> 365,
	'name' 		=> 'Москва',
];

// Админская часть приложения
const LOGIN = 'admin';
const PASSWORD = 'password';
const ADMIN_HREF = '/admin-dt/';

/*  ini_set('error_reporting', E_ALL);
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1); */