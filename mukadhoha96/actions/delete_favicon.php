<?php
declare(strict_types=1);

// корень
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/fav-icon/';

// возможные расширения, под которыми лежит favicon
$exts = ['png','ico','svg','jpg','jpeg'];

// перебираем и удаляем
foreach ($exts as $ext) {
    $file = $uploadDir . 'favicon.' . $ext;
    if (is_file($file)) {
        @unlink($file);
    }
}

// редирект обратно на страницу настроек
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
exit;
