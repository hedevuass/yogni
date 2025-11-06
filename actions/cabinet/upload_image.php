<?php
use Maestroerror\HeicToJpg;

include_once $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();
$json = [];

// Разрешенные и запрещенные типы
$allow = array('jpg', 'jpeg', 'png', 'heic');
$deny = array('phtml','php','php3','php4','php5','php6','php7','phps','cgi','pl','asp','aspx','shtml','shtm','htaccess','htpasswd','ini','log','sh','js','html','htm','css','sql','spl','scgi','fcgi','exe');

// Директория загрузки
$path = dirname(__DIR__, 2) . '/uploads/avatars/';

// Общая функция обработки загрузки
function handle_upload($file, $path, &$json)
{
    global $allow, $deny;

    if (empty($file['tmp_name'])) return;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($_SESSION['user']['id']) || empty($ext)) {
        $json['error'] = 'Не удалось загрузить файл.';
        return;
    }

    if (!in_array($ext, $allow) || in_array($ext, $deny)) {
        $json['error'] = 'Допустимы только jpeg, jpg, png, heic.';
        return;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $json['error'] = 'Файл слишком большой (максимальный размер - до 5 МБ).';
        return;
    }

    $size = getimagesize($file['tmp_name']);
    if ($size[0] < 250 || $size[1] < 250) {
        $json['error'] = 'Изображение слишком маленькое.';
        return;
    }

    $name = 'avatar' . $_SESSION['user']['id'] . '_' . date('d-m-y_H-i-s');

    // Если HEIC → сначала конвертируем в JPG, затем в WebP
    if (HeicToJpg::isHeic($file['tmp_name'])) {
        $jpgName = $name . '.jpg';
        $jpgPath = $path . $jpgName;

        if (!is_dir($path)) mkdir($path, 0755, true);

        if (HeicToJpg::convert($file['tmp_name'])->saveAs($jpgPath)) {
            $image = load_image_webp([
                'name' => $jpgName,
                'tmp_name' => $jpgPath,
                'size' => filesize($jpgPath),
                'type' => 'image/jpeg'
            ], 'avatars', $name, 5, ['width' => 250, 'height' => 250], [], 80, 300);

            unlink($jpgPath); // удаляем промежуточный JPG

        } else {
            $json['error'] = 'Не удалось конвертировать HEIC → JPG.';
            return;
        }
    } else {
        // Если JPG/PNG → сразу в WebP
        $image = load_image_webp($file, 'avatars', $name, 5, ['width' => 250, 'height' => 250], [], 80, 100);
    }

    // Проверка на ошибки загрузки
    if (isset($image['error'])) {
        $json['error'] = $image['error'];
        return;
    }

    // --- Создание миниатюры ---
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
    $avatarPath = $uploadDir . $image['name'];
    $thumbName = 'thumb_' . $image['name'];
    $thumbPath = $uploadDir . $thumbName;

    if (file_exists($avatarPath)) {
        $src = imagecreatefromwebp($avatarPath);
        if ($src) {
            $width = imagesx($src);
            $height = imagesy($src);

            $thumbSize = 250;
            $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            // Обрезка по центру
            $minSide = min($width, $height);
            $srcX = (int)(($width - $minSide) / 2);
            $srcY = (int)(($height - $minSide) / 2);

            imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $minSide, $minSide);
            imagewebp($thumb, $thumbPath, 100);

            imagedestroy($thumb);
            imagedestroy($src);

            $json['thumb'] = $thumbName;
        }
    }

    // --- Сохраняем имя аватара в БД ---
    if (isset($image['name']) && set_user_avatar($image['name'], $_SESSION['user']['id'])) {
        $json['success'] = $image['name'];
    } else {
        $json['error'] = 'Ошибка при сохранении аватара в БД.';
    }
}

// Обработка двух возможных полей загрузки
if (isset($_FILES['photo_file'])) {
    handle_upload($_FILES['photo_file'], $path, $json);
}
if (isset($_FILES['photo_file_obzor'])) {
    handle_upload($_FILES['photo_file_obzor'], $path, $json);
}

header('Content-Type: application/json');
echo json_encode($json);
die();