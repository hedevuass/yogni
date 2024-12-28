<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    $service = get_actual_service((int)$id);
    $service['img'] = $service['img'] ? '/assets/img/' . $service['img'] : '/assets/img/cabinet/gray-circle.svg';
    $service['title'] = $service['title'];
    $service['slug'] = $service['slug'];

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/service.php', [
        'service' => $service,
    ]);

    responseJson($json);
}