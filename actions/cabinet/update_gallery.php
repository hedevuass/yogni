<?php
use Maestroerror\HeicToJpg;

include_once $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

session_start();
$json = [];

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
        $error_msg = 'Недопустимый формат изображения';
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $error_msg = 'Файл слишком большой (максимальный размер - до 5 МБ)';
        return false;
    }

    $size = getimagesize($file['tmp_name']);
    if ($size[0] < 50 || $size[1] < 50) {
        $error_msg = 'Изображение слишком маленькое (минимум 50x50px)';
        return false;
    }

    $folder = $is_avatar ? 'avatars' : 'gallery';
    $path = dirname(__DIR__, 2) . '/uploads/' . $folder . '/';
    if (!is_dir($path)) mkdir($path, 0755, true);

    $prefix = $is_avatar ? 'avatar' : 'gallery_' . $index;
    $name = $prefix . '_' . $user_id . '_' . date('d-m-y_H-i-s');

    // Конвертация HEIC
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

            unlink($jpgPath);
        } else {
            $error_msg = 'Не удалось конвертировать HEIC → JPG';
            return false;
        }
    } else {
        fix_image_orientation($file['tmp_name']);
        $image = load_image_webp($file, $folder, $name, 5, ['width' => 250, 'height' => 250], [], 80, $is_avatar ? 300 : 1200);
    }

    if (isset($image['error'])) {
        $error_msg = $image['error'];
        return false;
    }

    // Генерация миниатюр
    if (isset($image['name'])) {
        $fullPath = $path . $image['name'];
        $src = @imagecreatefromwebp($fullPath);

        if ($src) {
            $width = imagesx($src);
            $height = imagesy($src);

            // Квадратная миниатюра (только для аватара)
            if ($is_avatar) {
                $thumbSize = 250;
                $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);

                $minSide = min($width, $height);
                $srcX = (int)(($width - $minSide) / 2);
                $srcY = (int)(($height - $minSide) / 2);

                imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $minSide, $minSide);
                imagewebp($thumb, $path . 'thumb_' . $image['name'], 100);
                imagedestroy($thumb);
            }

            // Миниатюра 16:9 (для всех)
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
            imagewebp($catalog, $path . 'catalog_' . $image['name'], 85);
            imagedestroy($catalog);
            imagedestroy($src);
        }
    }

    return $image['name'];
}


// --- ЛОГИКА ОБРАБОТКИ ФОРМЫ ---

$final_avatar = '';
$temp_gallery = [];
$error_msg = '';

// Слот 0: Аватар
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

// Слоты 1-11: Галерея
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

// Рокировка из галереи в аватар
if (empty($final_avatar) && !empty($temp_gallery)) {
    $promoted_photo = array_shift($temp_gallery);
    
    $gallery_dir = dirname(__DIR__, 2) . '/uploads/gallery/';
    $avatars_dir = dirname(__DIR__, 2) . '/uploads/avatars/';
    
    $old_file_path = $gallery_dir . $promoted_photo;
    $new_file_path = $avatars_dir . $promoted_photo;
    
    if (file_exists($old_file_path)) {
        if (!is_dir($avatars_dir)) mkdir($avatars_dir, 0755, true);
        
        if (rename($old_file_path, $new_file_path)) {
            $final_avatar = $promoted_photo;
            
            $src = @imagecreatefromwebp($new_file_path);
            if ($src) {
                $width = imagesx($src);
                $height = imagesy($src);
                
                // Квадратный thumb для нового аватара
                $thumbSize = 250;
                $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $minSide = min($width, $height);
                $srcX = (int)(($width - $minSide) / 2);
                $srcY = (int)(($height - $minSide) / 2);
                imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $minSide, $minSide);
                imagewebp($thumb, $avatars_dir . 'thumb_' . $final_avatar, 100);
                imagedestroy($thumb);

                // Новая миниатюра 16:9 в папке аватаров
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
                imagewebp($catalog, $avatars_dir . 'catalog_' . $final_avatar, 85);
                imagedestroy($catalog);
                imagedestroy($src);
            }

            // Удаление старой миниатюры из галереи
            $old_catalog_path = $gallery_dir . 'catalog_' . $promoted_photo;
            if (file_exists($old_catalog_path)) @unlink($old_catalog_path);
        }
    } else {
        $final_avatar = $promoted_photo;
    }
}

$gallery_json = !empty($temp_gallery) ? json_encode($temp_gallery, JSON_UNESCAPED_UNICODE) : '';

// Запись в БД
$avatar_updated = set_user_avatar($final_avatar, $user_id);
$gallery_updated = set_user_gallery($gallery_json, $user_id);

if ($avatar_updated || $gallery_updated) {
    if ($final_avatar) {
        $_SESSION['user']['photo'] = $final_avatar;
    }

    $response_gallery = [];
    $response_gallery[0] = $final_avatar;

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