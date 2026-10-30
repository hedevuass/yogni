<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

session_start();
header('Content-Type: application/json');

$json = [];

/**
 * Проверка названий залов на запрещённые слова / опасный HTML.
 * Возвращает плоский список уникальных "нарушителей" (слово или "HTML-вставка").
 */
function checkRoomsForbiddenWords(array $roomsPost, array $forbiddenWords): array
{
    $found = [];

    foreach ($roomsPost as $roomData) {
        $name = trim($roomData['name'] ?? '');
        if ($name === '') continue;

        // Опасный HTML в названии
        if (preg_match('/<[^>]+>/', $name)) {
            $found['HTML-вставка'] = true;
        }

        // Ссылки в названии
        if (preg_match('/https?:\/\/[^\s]+/i', $name)) {
            $found['ссылка'] = true;
        }

        $cleanName = mb_strtolower(strip_tags($name), 'UTF-8');

        foreach ($forbiddenWords as $word) {
            $trimmedWord = trim($word);
            if ($trimmedWord === '') continue;
            if (in_array($trimmedWord, ['<', '>', '&lt;', '&gt;'], true)) continue;

            $quotedWord = preg_quote(mb_strtolower($word, 'UTF-8'), '/');
            if (preg_match('/(?<!\p{L})' . $quotedWord . '(?!\p{L})/iu', $cleanName)) {
                $found[$word] = true;
            }
        }
    }

    return array_keys($found);
}

/**
 * Обработка одного файла фото зала: валидация, webp, миниатюра 16:9
 */
function process_room_photo($file, $roomKey, $index, $user_id, $uploadDirRooms, &$error_msg)
{
    $deny  = ['phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $deny)) {
        $error_msg = 'Недопустимый формат файла.';
        return false;
    }

    $size = @getimagesize($file['tmp_name']);
    /* if (!$size || $size[0] < 50 || $size[1] < 50) {
        $error_msg = 'Изображение слишком маленькое (минимум 50x50px).';
        return false;
    } */

    fix_image_orientation($file['tmp_name']);

    $safeRoomKey = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$roomKey);
    $name = 'room_' . $safeRoomKey . '_' . $index . '_' . $user_id . '_' . date('d-m-y_H-i-s');

    $image = load_image_webp($file, 'rooms', $name, 5, ['width' => 250, 'height' => 250], [], 60, 1200);

    if (isset($image['error'])) {
        $error_msg = $image['error'];
        return false;
    }

    // Миниатюра 16:9 — по аналогии с галереей
    $fullPath = $uploadDirRooms . $image['name'];
    $src = @imagecreatefromwebp($fullPath);

    if ($src) {
        $width = imagesx($src);
        $height = imagesy($src);

        $catW = 640;
        $catH = 360;
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
        imagewebp($catalog, $uploadDirRooms . 'catalog_' . $image['name'], 85);
        imagedestroy($catalog);
        imagedestroy($src);
    }

    return $image['name'];
}

// --- ЛОГИКА ОБРАБОТКИ ФОРМЫ ---

if (!isset($_SESSION['user']['id'])) {
    $json['error'] = 'Сессия устарела. Пожалуйста, войдите заново.';
    echo json_encode($json);
    die();
}

$user_id = (int)$_SESSION['user']['id'];

// Шаг 0.5: получаем текущие залы из базы (чтобы знать, какие файлы потом можно удалить)
$currentRoomsRow = $data_base->selectRow('SELECT rooms FROM dt_specialist WHERE id = ?', [$user_id]);
$currentRooms = $currentRoomsRow ? (json_decode($currentRoomsRow['rooms'] ?? '[]', true) ?: []) : [];

// Шаг 1: проверка запрещённых слов в названиях залов — до обработки файлов
if (!empty($_POST['rooms']) && is_array($_POST['rooms'])) {
    $forbiddenWordsList = getForbiddenWords();
    $foundForbidden = checkRoomsForbiddenWords($_POST['rooms'], $forbiddenWordsList);

    if (!empty($foundForbidden)) {
        $json['error'] = 'Название зала содержит недопустимые слова или символы';
        $json['forbidden_words'] = $foundForbidden;
        echo json_encode($json);
        die();
    }
}

$uploadDirRooms = $_SERVER['DOCUMENT_ROOT'] . '/uploads/rooms/';
if (!is_dir($uploadDirRooms)) mkdir($uploadDirRooms, 0755, true);

$newRooms = [];

if (!empty($_POST['rooms']) && is_array($_POST['rooms'])) {
    foreach ($_POST['rooms'] as $roomKey => $roomData) {
        $roomName = clean_elem_blog_title($roomData['name'] ?? '');
        if (mb_strlen($roomName, 'UTF-8') > 35) {
            $roomName = mb_substr($roomName, 0, 35, 'UTF-8');
        }

        $existingPhotos = $roomData['existing_photos'] ?? [];
        $roomPhotos = [];

        for ($i = 0; $i < 4; $i++) {
            $hasNewFile = isset($_FILES['rooms']['tmp_name'][$roomKey]['photos'][$i])
                && $_FILES['rooms']['size'][$roomKey]['photos'][$i] > 0;

            if ($hasNewFile) {
                $fileArray = [
                    'name'     => $_FILES['rooms']['name'][$roomKey]['photos'][$i],
                    'type'     => $_FILES['rooms']['type'][$roomKey]['photos'][$i],
                    'tmp_name' => $_FILES['rooms']['tmp_name'][$roomKey]['photos'][$i],
                    'error'    => $_FILES['rooms']['error'][$roomKey]['photos'][$i],
                    'size'     => $_FILES['rooms']['size'][$roomKey]['photos'][$i],
                ];

                $error_msg = '';
                $uploadedName = process_room_photo($fileArray, $roomKey, $i, $user_id, $uploadDirRooms, $error_msg);

                if (!$uploadedName) {
                    $json['error'] = $error_msg;
                    $json['room_key'] = $roomKey;
                    $json['field'] = 'photo';
                    echo json_encode($json);
                    die();
                }

                $roomPhotos[] = $uploadedName;
            } else if (!empty($existingPhotos[$i])) {
                $roomPhotos[] = clean_elem_form($existingPhotos[$i]);
            }
        }

        // Полностью пустой зал (без имени и без единой фотографии) — это нормально, просто пропускаем
        if ($roomName === '' && empty($roomPhotos)) {
            continue;
        }

        // Загружены фото, но не указано название — ошибка, сохранение всей формы блокируется
        if ($roomName === '' && !empty($roomPhotos)) {
            $json['error'] = 'Укажите наименование зала';
            $json['room_key'] = $roomKey;
            $json['field'] = 'name';
            echo json_encode($json);
            die();
        }

        // Указано название, но не загружено ни одной фотографии — тоже ошибка
        if ($roomName !== '' && empty($roomPhotos)) {
            $json['error'] = 'Загрузите изображение';
            $json['room_key'] = $roomKey;
            $json['field'] = 'photo';
            echo json_encode($json);
            die();
        }

        $newRooms[] = [
            'name'   => $roomName,
            'photos' => $roomPhotos,
        ];
    }
}

// Удаление файлов тех фото залов, которых больше нет ни в одном зале
$oldRoomPhotosFlat = [];
foreach ($currentRooms as $oldRoom) {
    foreach (($oldRoom['photos'] ?? []) as $oldPhoto) {
        $oldRoomPhotosFlat[] = $oldPhoto;
    }
}

$newRoomPhotosFlat = [];
foreach ($newRooms as $room) {
    foreach ($room['photos'] as $photo) {
        $newRoomPhotosFlat[] = $photo;
    }
}

foreach ($oldRoomPhotosFlat as $oldPhoto) {
    if (!in_array($oldPhoto, $newRoomPhotosFlat)) {
        if (file_exists($uploadDirRooms . $oldPhoto)) @unlink($uploadDirRooms . $oldPhoto);
        if (file_exists($uploadDirRooms . 'catalog_' . $oldPhoto)) @unlink($uploadDirRooms . 'catalog_' . $oldPhoto);
    }
}

$final_rooms = !empty($newRooms) ? json_encode($newRooms, JSON_UNESCAPED_UNICODE) : '';
$rooms_updated = set_user_rooms($final_rooms, $user_id);

if ($rooms_updated) {
    $json['success'] = true;
    $json['rooms'] = $newRooms;
} else {
    $json['error'] = 'Изменений не обнаружено или произошла ошибка БД.';
}

echo json_encode($json);
die();