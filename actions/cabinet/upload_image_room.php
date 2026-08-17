<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

$json = ['status' => 'error'];

if (!isset($_SESSION['user']['id'])) {
    $json['error'] = 'Сессия устарела. Пожалуйста, войдите заново.';
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    die();
}

$user_id = (int)$_SESSION['user']['id'];

if (empty($_FILES['photos'])) {
    $json['error'] = 'Файлы не получены.';
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    die();
}

$uploadDirRooms = $_SERVER['DOCUMENT_ROOT'] . '/uploads/rooms/';
if (!is_dir($uploadDirRooms)) {
    mkdir($uploadDirRooms, 0755, true);
}

$deny = ['phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'];

$uploadedPhotos = [];

// Перебираем все загруженные файлы из попапа
foreach ($_FILES['photos']['tmp_name'] as $index => $tmpName) {
    if (empty($tmpName) || $_FILES['photos']['error'][$index] !== UPLOAD_ERR_OK) {
        continue;
    }

    $fileName = $_FILES['photos']['name'][$index];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($ext, $deny)) {
        $json['error'] = 'Недопустимый формат файла.';
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        die();
    }

    $fileArray = [
        'name'     => $fileName,
        'type'     => $_FILES['photos']['type'][$index],
        'tmp_name' => $tmpName,
        'error'    => $_FILES['photos']['error'][$index],
        'size'     => $_FILES['photos']['size'][$index],
    ];

    if (function_exists('fix_image_orientation')) {
        fix_image_orientation($tmpName);
    }

    $nameTemplate = 'room_popup_' . $index . '_' . $user_id . '_' . date('d-m-y_H-i-s');

    // Главная проверка через load_image_webp
    $image = load_image_webp($fileArray, 'rooms', $nameTemplate, 5, ['width' => 250, 'height' => 250], [], 60, 1200);

    if (isset($image['error'])) {
        // Ошибка валидации изображения
        $json['error'] = $image['error'];
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        die();
    }

    $uploadedName = $image['name'];

    // Генерация миниатюры 16:9
    $fullPath = $uploadDirRooms . $uploadedName;
    $src = @imagecreatefromwebp($fullPath);
    if ($src) {
        $width = imagesx($src);
        $height = imagesy($src);
        $catW = 640; $catH = 360;
        $catalog = imagecreatetruecolor($catW, $catH);
        imagealphablending($catalog, false);
        imagesavealpha($catalog, true);

        if (($width / $height) > ($catW / $catH)) {
            $srcH = $height;
            $srcW = (int)($height * ($catW / $catH));
            $srcX = (int)(($width - $srcW) / 2);
            $srcY = 0;
        } else {
            $srcW = $width;
            $srcH = (int)($width / ($catW / $catH));
            $srcX = 0;
            $srcY = (int)(($height - $srcH) / 2);
        }

        imagecopyresampled($catalog, $src, 0, 0, $srcX, $srcY, $catW, $catH, $srcW, $srcH);
        imagewebp($catalog, $uploadDirRooms . 'catalog_' . $uploadedName, 85);
        imagedestroy($catalog);
        imagedestroy($src);
    }

    // Сохраняем имя загруженного файла по индексу слота
    $uploadedPhotos[$index] = $uploadedName;
}

$json['success'] = true;
$json['photos'] = $uploadedPhotos;

echo json_encode($json, JSON_UNESCAPED_UNICODE);
die();