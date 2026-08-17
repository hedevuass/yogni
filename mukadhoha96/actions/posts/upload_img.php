<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    global $allow, $deny;

    if (!isset($allow) || !is_array($allow)) {
        $allow = ['jpeg', 'jpg', 'png', 'heic', 'jfif', 'webp'];
    }
    if (!isset($deny) || !is_array($deny)) {
        $deny = [];
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allow) || in_array($ext, $deny)) {
        http_response_code(400);
        echo json_encode(['error' => 'Допустимы только jpeg, jpg, png, heic, jfif, webp.']);
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'Файл слишком большой (максимальный размер - до 5 МБ).']);
        exit;
    }

    $size = getimagesize($file['tmp_name']);
    if (!$size || $size[0] < 50 || $size[1] < 50) {
        http_response_code(400);
        echo json_encode(['error' => 'Изображение слишком маленькое (минимум 50x50px).']);
        exit;
    }

    $folder = 'articles';
    $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $folder . '/';
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    $name = 'art_content_' . uniqid() . '_' . date('d-m-y_H-i-s');
    $image = null;

    if (class_exists('HeicToJpg') && HeicToJpg::isHeic($file['tmp_name'])) {
        $jpgName = $name . '.jpg';
        $jpgPath = $path . $jpgName;

        if (HeicToJpg::convert($file['tmp_name'])->saveAs($jpgPath)) {
            if (function_exists('fix_image_orientation')) {
                fix_image_orientation($jpgPath);
            }

            $image = load_image_webp([
                'name' => $jpgName,
                'tmp_name' => $jpgPath,
                'size' => filesize($jpgPath),
                'type' => 'image/jpeg'
            ], $folder, $name, 5, ['width' => 50, 'height' => 50], [], 75, 1200);

            if (file_exists($jpgPath)) {
                unlink($jpgPath);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Не удалось конвертировать HEIC → JPG.']);
            exit;
        }
    } else {
        if (function_exists('fix_image_orientation')) {
            fix_image_orientation($file['tmp_name']);
        }
        
        $image = load_image_webp($file, $folder, $name, 5, ['width' => 50, 'height' => 50], [], 75, 1200);
    }

    if (isset($image['error'])) {
        http_response_code(400);
        echo json_encode(['error' => $image['error']]);
        exit;
    }

    if (isset($image['name'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'location' => '/uploads/' . $folder . '/' . $image['name']
        ]);
        exit;
    }
}

http_response_code(500);
echo json_encode(['error' => 'Файл не был получен сервером или произошел сбой.']);