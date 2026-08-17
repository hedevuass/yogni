<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    $pro_photo = get_pro_photo_by_id((int)$id);
    $pro_photo['title'] = $pro_photo['title'];
    $pro_photo['price'] = $pro_photo['price'];
    $pro_photo['price_old'] = $pro_photo['price_old'];
    $pro_photo['time'] = $pro_photo['time'];
    $pro_photo['number'] = $pro_photo['number'];
    $pro_photo['description'] = $pro_photo['description'];

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/photo.php', [
        'pro_photo' => $pro_photo,
    ]);

    responseJson($json);
}