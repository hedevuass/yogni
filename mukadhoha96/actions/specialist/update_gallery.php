<?php
use Maestroerror\HeicToJpg;

include_once $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

session_start();
$json = [];

function fix_image_orientation($file_path) {
    if (!function_exists('exif_read_data')) {
        return; // Если на сервере не включено расширение EXIF, пропускаем
    }

    // Читаем метаданные только у файлов JPG/JPEG
    $mime = mime_content_type($file_path);
    if ($mime !== 'image/jpeg') {
        return; 
    }

    $exif = @exif_read_data($file_path);
    if (empty($exif['Orientation'])) {
        return; // Ориентация нормальная или тега нет
    }

    $orientation = $exif['Orientation'];
    if ($orientation <= 1) {
        return; // Поворот не требуется
    }

    // Загружаем исходник в память для вращения
    $image = imagecreatefromjpeg($file_path);
    if (!$image) return;

    $degrees = 0;
    switch ($orientation) {
        case 3: // Перевернуто вверх ногами (180°)
            $degrees = 180;
            break;
        case 6: // Повернуто на 90° по часовой стрелке
            $degrees = -90; // или 270
            break;
        case 8: // Повернуто на 90° против часовой стрелки
            $degrees = 90;
            break;
    }

    if ($degrees !== 0) {
        // Физически вращаем картинку
        $rotated = imagerotate($image, $degrees, 0);
        if ($rotated) {
            // Перезаписываем временный файл уже в правильном положении
            imagejpeg($rotated, $file_path, 95); 
            imagedestroy($rotated);
        }
    }
    
    imagedestroy($image);
}

if (!isset($_SESSION['user']['id'])) {
    $json['error'] = 'Сессия устарела. Пожалуйста, войдите заново.';
    echo json_encode($json);
    die();
}

$user_id = (int)$_SESSION['user']['id'];

// Настройки валидации файлов
$allow = array('jpg', 'jpeg', 'png', 'heic', 'jfif');
$deny = array('phtml','php','php3','php4','php5','php6','php7','phps','cgi','pl','asp','aspx','shtml','shtm','htaccess','htpasswd','ini','log','sh','js','html','htm','css','sql','spl','scgi','fcgi','exe');

/**
 * Универсальная функция обработки и конвертации фото
 */
function process_gallery_file($file, $is_avatar, $index, $user_id, &$error_msg)
{
    global $allow, $deny;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allow) || in_array($ext, $deny)) {
        $error_msg = 'Допустимы только jpeg, jpg, png, heic, jfif.';
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $error_msg = 'Файл слишком большой (максимальный размер - до 5 МБ).';
        return false;
    }

    $size = getimagesize($file['tmp_name']);
    if ($size[0] < 50 || $size[1] < 50) {
        $error_msg = 'Изображение слишком маленькое (минимум 50x50px).';
        return false;
    }

    // Определяем папку назначения
    $folder = $is_avatar ? 'avatars' : 'gallery';
    $path = dirname(__DIR__, 2) . '/uploads/' . $folder . '/';
    if (!is_dir($path)) mkdir($path, 0755, true);

    // Генерируем уникальное имя
    $prefix = $is_avatar ? 'avatar' : 'gallery_' . $index;
    $name = $prefix . '_' . $user_id . '_' . date('d-m-y_H-i-s');

    // Если HEIC -> сначала в JPG, затем в WebP
    if (HeicToJpg::isHeic($file['tmp_name'])) {
        $jpgName = $name . '.jpg';
        $jpgPath = $path . $jpgName;

        if (HeicToJpg::convert($file['tmp_name'])->saveAs($jpgPath)) {
            fix_image_orientation($jpgPath);

            $image = load_image_webp([
                'name' => $jpgName,
                'tmp_name' => $jpgPath,
                'size' => filesize($jpgPath),
                'type' => 'image/jpeg'
            ], $folder, $name, 5, ['width' => 250, 'height' => 250], [], 80, $is_avatar ? 300 : 1200);

            unlink($jpgPath); // Чистим временный JPG
        } else {
            $error_msg = 'Не удалось конвертировать HEIC → JPG.';
            return false;
        }
    } else {
        // Обычные JPG/PNG сразу гоним в WebP
        fix_image_orientation($file['tmp_name']);
        $image = load_image_webp($file, $folder, $name, 5, ['width' => 250, 'height' => 250], [], 80, $is_avatar ? 300 : 1200);
    }

    if (isset($image['error'])) {
        $error_msg = $image['error'];
        return false;
    }

    // --- Создание МИНИАТЮРЫ (Только для главной аватарки) ---
    if ($is_avatar && isset($image['name'])) {
        $avatarPath = $path . $image['name'];
        $thumbName = 'thumb_' . $image['name'];
        $thumbPath = $path . $thumbName;

        if (file_exists($avatarPath)) {
            $src = imagecreatefromwebp($avatarPath);
            if ($src) {
                $width = imagesx($src);
                $height = imagesy($src);

                $thumbSize = 250;
                $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);

                $minSide = min($width, $height);
                $srcX = (int)(($width - $minSide) / 2);
                $srcY = (int)(($height - $minSide) / 2);

                imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $minSide, $minSide);
                imagewebp($thumb, $thumbPath, 100);

                imagedestroy($thumb);
                imagedestroy($src);
            }
        }
    }

    return $image['name'];
}


// --- ОСНОВНАЯ ЛОГИКА ОБРАБОТКИ ФОРМЫ ---

$final_avatar = '';
$temp_gallery = [];
$error_msg = '';

// 1. Обрабатываем первый слот (Главное фото / Аватар)
if (!empty($_FILES['gallery_files']['tmp_name'][0])) {
    $file = [
        'name'     => $_FILES['gallery_files']['name'][0],
        'type'     => $_FILES['gallery_files']['type'][0],
        'tmp_name' => $_FILES['gallery_files']['tmp_name'][0],
        'error'    => $_FILES['gallery_files']['error'][0],
        'size'     => $_FILES['gallery_files']['size'][0]
    ];
    $uploaded = process_gallery_file($file, true, 0, $user_id, $error_msg);
    if ($uploaded) {
        $final_avatar = $uploaded;
    } else {
        echo json_encode(['error' => $error_msg]); die();
    }
} else {
    $final_avatar = !empty($_POST['existing_photos'][0]) ? clean_elem_form($_POST['existing_photos'][0]) : '';
}

// 2. Обрабатываем остальные слоты (1 - 11) для JSON-галереи
for ($i = 1; $i < 12; $i++) {
    if (!empty($_FILES['gallery_files']['tmp_name'][$i])) {
        $file = [
            'name'     => $_FILES['gallery_files']['name'][$i],
            'type'     => $_FILES['gallery_files']['type'][$i],
            'tmp_name' => $_FILES['gallery_files']['tmp_name'][$i],
            'error'    => $_FILES['gallery_files']['error'][$i],
            'size'     => $_FILES['gallery_files']['size'][$i]
        ];
        $uploaded = process_gallery_file($file, false, $i, $user_id, $error_msg);
        if ($uploaded) {
            $temp_gallery[] = $uploaded; 
        } else {
            echo json_encode(['error' => $error_msg]); die();
        }
    } else if (!empty($_POST['existing_photos'][$i])) {
        $temp_gallery[] = clean_elem_form($_POST['existing_photos'][$i]);
    }
}

// --- МАГИЯ РОКИРОВКИ: Перенос первого фото из галереи в аватар, если аватар удален ---
if (empty($final_avatar) && !empty($temp_gallery)) {
    // Извлекаем самое первое фото из галереи (оно пропадает из массива $temp_gallery)
    $promoted_photo = array_shift($temp_gallery);
    
    $gallery_dir = dirname(__DIR__, 2) . '/uploads/gallery/';
    $avatars_dir = dirname(__DIR__, 2) . '/uploads/avatars/';
    
    $old_file_path = $gallery_dir . $promoted_photo;
    $new_file_path = $avatars_dir . $promoted_photo;
    
    if (file_exists($old_file_path)) {
        if (!is_dir($avatars_dir)) mkdir($avatars_dir, 0755, true);
        
        // Физически перемещаем файл в папку аватарок
        if (rename($old_file_path, $new_file_path)) {
            $final_avatar = $promoted_photo;
            
            // Генерируем миниатюру thumb_ для продвинутого аватара
            $thumbName = 'thumb_' . $final_avatar;
            $thumbPath = $avatars_dir . $thumbName;
            
            $src = imagecreatefromwebp($new_file_path);
            if ($src) {
                $width = imagesx($src);
                $height = imagesy($src);
                $thumbSize = 250;
                $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                
                $minSide = min($width, $height);
                $srcX = (int)(($width - $minSide) / 2);
                $srcY = (int)(($height - $minSide) / 2);
                
                imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $minSide, $minSide);
                imagewebp($thumb, $thumbPath, 100);
                
                imagedestroy($thumb);
                imagedestroy($src);
            }
        }
    } else {
        // Если физического файла почему-то нет, но имя в массиве было — просто подставляем имя
        $final_avatar = $promoted_photo;
    }
}

// Переводим массив галереи в JSON формат
$gallery_json = !empty($temp_gallery) ? json_encode($temp_gallery, JSON_UNESCAPED_UNICODE) : '';

// 3. Записываем всё в базу данных
$avatar_updated = set_user_avatar($final_avatar, $user_id);
$gallery_updated = set_user_gallery($gallery_json, $user_id);

if ($avatar_updated || $gallery_updated) {
    // Обновляем имя аватара в сессии, если оно изменилось
    if ($final_avatar) {
        $_SESSION['user']['photo'] = $final_avatar;
    }

    // Собираем актуальную карту расположения картинок для фронтенда
    $response_gallery = [];
    $response_gallery[0] = $final_avatar; // Аватар всегда на нулевой позиции

    // Галерея распределяется строго по порядку с 1 по 11 слоты
    for ($i = 1; $i < 12; $i++) {
        $gallery_index = $i - 1;
        $response_gallery[$i] = isset($temp_gallery[$gallery_index]) ? $temp_gallery[$gallery_index] : '';
    }

    $json['success'] = 'Галерея успешно сохранена';
    $json['gallery'] = $response_gallery;
} else {
    $json['error'] = 'Изменений не обнаружено или произошла ошибка БД.';
}

header('Content-Type: application/json');
echo json_encode($json);
die();