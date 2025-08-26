<?php
declare(strict_types=1);

include $_SERVER['DOCUMENT_ROOT'] . '/functions.php'; // Предполагается, что здесь определена функция load_image

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/fav-icon/';
@mkdir($uploadDir, 0755, true);

// Удаляем старые фавиконки
foreach (['png', 'ico', 'svg', 'jpg', 'jpeg'] as $ext) {
    $file = $uploadDir . 'favicon.' . $ext;
    if (is_file($file)) @unlink($file);
}
foreach ([16, 32] as $size) {
    $file = $uploadDir . "favicon-{$size}.png";
    if (is_file($file)) @unlink($file);
}

// Проверяем, является ли загруженный файл SVG
if (isset($_FILES['favicon']) && strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION)) === 'svg') {
    $srcName = 'favicon.svg';
    $srcPath = $uploadDir . $srcName;
    if (move_uploaded_file($_FILES['favicon']['tmp_name'], $srcPath)) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=Не+удалось+загрузить+файл');
    }
    exit;
}

// Для других форматов (PNG, JPG) используем существующую логику через load_image
$res = load_image(
    $_FILES['favicon'] ?? [],
    'fav-icon',
    'favicon_src',
    5.0,
    ['width' => 10, 'height' => 10],
    ['width' => 4000, 'height' => 4000]
);

if (!is_array($res) || empty($res['name'])) {
    $err = $res['error'] ?? 'Неизвестная ошибка';
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=' . urlencode($err));
    exit;
}

$srcName = $res['name'];
$srcPath = $uploadDir . $srcName;
$ext = strtolower(pathinfo($srcName, PATHINFO_EXTENSION));

// Обрабатываем растровые изображения (PNG, JPG, JPEG)
if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
    copy($srcPath, $uploadDir . 'favicon.png');
    if (create_ico($uploadDir . 'favicon.ico', [$srcPath])) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=Не+удалось+создать+ICO');
    }
    exit;
}

// Неподдерживаемый формат
header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=Неподдерживаемый+формат');
exit;

// Функция создания ICO (оставлена без изменений)
function create_ico(string $filename, array $pngFiles): bool {
    $icoData = pack('vvv', 0, 1, count($pngFiles));
    $offset = 6 + (16 * count($pngFiles));
    $entries = '';
    $imagesData = '';

    foreach ($pngFiles as $file) {
        $imgData = file_get_contents($file);
        if (!$imgData) return false;

        $width = (ord($imgData[16]) << 24) + (ord($imgData[17]) << 16) + (ord($imgData[18]) << 8) + ord($imgData[19]);
        $height = (ord($imgData[20]) << 24) + (ord($imgData[21]) << 16) + (ord($imgData[22]) << 8) + ord($imgData[23]);

        $icoWidth = ($width > 255) ? 0 : $width;
        $icoHeight = ($height > 255) ? 0 : $height;

        $size = strlen($imgData);

        $entries .= pack('CCCCvvVV', $icoWidth, $icoHeight, 0, 0, 1, 32, $size, $offset);
        $offset += $size;
        $imagesData .= $imgData;
    }

    return file_put_contents($filename, $icoData . $entries . $imagesData) !== false;
}