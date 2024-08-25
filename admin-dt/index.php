<?php

require $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] .'/functions.php';
require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF .'actions/database/functions.php';

// Ауентификация
base_auth();

// Массив роутов
$routes = include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'routes.php';

// Подключаемые файлы
$urn = strtok($_SERVER['REQUEST_URI'], '?');

require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF .'modules/common/header.php';

if (isset($routes[$urn]['module']) && $routes[$urn]['module'])
    require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/' . $routes[$urn]['module'] . '.php';

require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/common/footer.php';
