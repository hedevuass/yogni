<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    global $allow, $deny;

    // Подстраховка: если глобальные переменные не дошли, наполняем их вручную
    if (!isset($allow) || !is_array($allow)) {
        $allow = ['jpeg', 'jpg', 'png', 'heic', 'jfif', 'webp'];
    }
    if (!isset($deny) || !is_array($deny)) {
        $deny = [];
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // 1. Проверяем валидность расширения файла
    if (!in_array($ext, $allow) || in_array($ext, $deny)) {
        http_response_code(400);
        echo json_encode(['error' => 'Допустимы только jpeg, jpg, png, heic, jfif, webp.']);
        exit;
    }

    // 2. Ограничение по весу файла (до 5 МБ)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'Файл слишком большой (максимальный размер - до 5 МБ).']);
        exit;
    }

    // 3. Проверяем минимальное разрешение картинки
    $size = getimagesize($file['tmp_name']);
    if (!$size || $size[0] < 50 || $size[1] < 50) {
        http_response_code(400);
        echo json_encode(['error' => 'Изображение слишком маленькое (минимум 50x50px).']);
        exit;
    }

    // Целевая папка внутри /uploads/
    $folder = 'articles';
    $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $folder . '/';
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    // Генерируем уникальное имя для файла контента
    $name = 'art_content_' . uniqid() . '_' . date('d-m-y_H-i-s');
    $image = null;

    // 4. Обработка формата HEIC (с айфонов)
    if (class_exists('HeicToJpg') && HeicToJpg::isHeic($file['tmp_name'])) {
        $jpgName = $name . '.jpg';
        $jpgPath = $path . $jpgName;

        if (HeicToJpg::convert($file['tmp_name'])->saveAs($jpgPath)) {
            if (function_exists('fix_image_orientation')) {
                fix_image_orientation($jpgPath);
            }

            // Передаем конвертированный файл в функцию сжатия
            // Качество ставим 75 (оптимально для веба), макс. ширина/высота — 1200px
            $image = load_image_webp([
                'name' => $jpgName,
                'tmp_name' => $jpgPath,
                'size' => filesize($jpgPath),
                'type' => 'image/jpeg'
            ], $folder, $name, 5, ['width' => 50, 'height' => 50], [], 75, 1200);

            // Удаляем временный JPG, так как load_image_webp создаст финальный .webp
            if (file_exists($jpgPath)) {
                unlink($jpgPath);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Не удалось конвертировать HEIC → JPG.']);
            exit;
        }
    } else {
        // 5. Обработка стандартных форматов (JPG, PNG, JFIF)
        if (function_exists('fix_image_orientation')) {
            fix_image_orientation($file['tmp_name']);
        }
        
        $image = load_image_webp($file, $folder, $name, 5, ['width' => 50, 'height' => 50], [], 75, 1200);
    }

    // 6. Проверяем, не вернула ли load_image_webp ошибку
    if (isset($image['error'])) {
        http_response_code(400);
        echo json_encode(['error' => $image['error']]);
        exit;
    }

    // Если всё прошло успешно и файл создан — возвращаем JSON для TinyMCE
    if (isset($image['name'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'location' => '/uploads/' . $folder . '/' . $image['name']
        ]);
        exit;
    }
}

// Дефолтный ответ, если до этой строки что-то пошло не так
http_response_code(500);
echo json_encode(['error' => 'Файл не был получен сервером или произошел сбой.']);