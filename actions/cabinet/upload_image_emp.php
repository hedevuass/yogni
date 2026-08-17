<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

// Запрещенные расширения файлов.
$deny = array(
    'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 
    'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 
    'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
);

if (!isset($_FILES['photo_file'])) {
    $json['error'] = 'Файл не загружен.';
} else {
    $file = $_FILES['photo_file'];

    // Проверим на ошибки загрузки.
    if (!empty($file['error']) || empty($file['tmp_name'])) {
        $json['error'] = 'Не удалось загрузить файл';
    } elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
        $json['error'] = 'Не удалось загрузить файл';
    } else {
        // Получаем расширение файла
        $ext = pathinfo($file['name'])['extension'];

        if (!isset($_SESSION['user']['id']) || empty($ext)) {
            $json['error'] = 'Не удалось загрузить файл';
        } elseif (!empty($deny) && in_array(strtolower($ext), $deny)) {
            $json['error'] = 'Недопустимый формат изображения';
        } elseif (intval(getimagesize($file['tmp_name'])[0]) < 250 || intval(getimagesize($file['tmp_name'])[1]) < 250) {
            $json['error'] = 'Ширина или высота изображения слишком маленькое';
        } else {
            $name = 'avatar' . $_SESSION['user']['id'] . '_' . date('d-m-y_H-i-s');

            $image = load_image_webp($file, 'employee', $name, 5, ['width' => 250, 'height' => 250], [], 60, 300);

            if (isset($image['error'])) {
                $json['error'] = $image['error'];
            } else {
                $json['success'] = $image['name'];
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($json);
die();