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


// === Определяем запрошенную страницу ===
if (isset($_REQUEST['path'])) {
    $requested_page = $_REQUEST['path'] ?: 'index';
} else {
    $cabinet_pages = get_all_cabinet_pages();

    // убираем GET-параметры, оставляем только путь
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $requested_vars = explode('/', $path);

    // определяем основную страницу
    $requested_page = $requested_vars[0] ?: 'index';
    $requested_page = $requested_page === 'show_login' ? 'index' : $requested_page;

    if (in_array($requested_page, get_catalog_slugs(), true)) {
        $requested_page = 'catalog';
    } elseif ($requested_page === 'cabinet') {
        if (!empty($requested_vars[1])) {
            $requested_page = $requested_vars[1];
        } else {
            $requested_page = 'cabinet_index';
        }
    } elseif (in_array($requested_page, $cabinet_pages)) {
        responseRedirect('/cabinet/' . $requested_page);
    }
}

$HTML = '';

// теперь проверяем файл страницы
if (!file_exists("pages/$requested_page.html")) {
    $requested_page = 'not_found';
}

$HTML = file_get_contents("pages/$requested_page.html");

$HTML = modules_include($HTML);

if (file_exists("pages/$requested_page.php")) require("pages/$requested_page.php");

$HTML = editable($HTML);

echo $HTML;
