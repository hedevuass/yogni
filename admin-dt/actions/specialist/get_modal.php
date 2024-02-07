<?php

include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    $specialist = get_specialist($id) ?: [];
    $specialist['photo'] = $specialist['photo'] ? '/uploads/avatars/' . $specialist['photo'] : '/assets/img/cabinet/gray-circle.svg';

    $classifications = get_classifications();
    $cities = get_cities('name ASC');

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/specialist.php', [
        'specialist' => $specialist,
        'classifications' => $classifications,
        'cities' => $cities
    ]);

    responseJson($json);
}