<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

session_start();

$json = [];

// Проверка авторизации
if (!isset($_SESSION['user']['id'])) {
    $json['error'] = 'Ошибка доступа. Перезайдите в аккаунт.';
    responseJson($json);
}

// Проверка, что передан файл
if (empty($_FILES)) {
    $json['error'] = 'Файл не получен.';
    responseJson($json);
}

// В цикле обрабатываем все переданные файлы (имена те же, что и раньше)
foreach ($_FILES as $key => $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $json['error'][$key] = 'Ошибка загрузки файла.';
        continue;
    }

    // Загружаем через готовую функцию
    $image = load_image_webp($file, 'documents');

    // Проверяем результат
    if (isset($image['error'])) {
        $json['error'][$key] = $image['error'];
    } else {
        $json['success'][$key] = $image['name'];
    }
}

// Отправляем JSON с результатами
responseJson($json);
