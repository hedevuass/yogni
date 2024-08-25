<?php
session_start();

// Общие настройки движка
require 'settings.php';
// Общие функции движка
require 'functions.php';
// База данных
require 'actions/database/functions.php';
// Функции применимые только к данному проекту
require 'actions/functions.php';


if (isset($_REQUEST['path'])) {
	$requested_page = $_REQUEST['path'] ?: 'index';
} else {
	//получаем страницы кабинета
	$cabinet_pages = get_all_cabinet_pages();
	$requested_vars = explode('/', $_SERVER['REQUEST_URI']);
	$requested_page = strtok($requested_vars[1], '?') ?: 'index';
	$requested_page = $requested_page == 'show_login' ? 'index' : $requested_page;
	if ($requested_page == "yuristy-i-advokaty" || $requested_page == "experty" || $requested_page == "notariusy") {
		$requested_page = "catalog";
	} else if ($requested_page == "cabinet") {
		if(isset($requested_vars[2])) {
			$requested_page = strtok($requested_vars[2], '?') ?: 'cabinet_index';
		} else {
			$requested_page = 'cabinet_index';
		}
	} else if (in_array($requested_page, $cabinet_pages)) {
		//редирект со страниц кабинета без /cabinet/ на оригнальные (с /cabinet/)
		responseRedirect('/cabinet/'.$requested_page);
	}
}

$HTML = '';

if (!file_exists("pages/$requested_page.html")) $requested_page = "not_found";

$HTML = file_get_contents("pages/$requested_page.html");

$HTML = modules_include($HTML);

if (file_exists("pages/$requested_page.php")) require("pages/$requested_page.php");

$HTML = editable($HTML);

echo $HTML;
