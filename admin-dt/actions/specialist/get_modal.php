<?php

include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $specialist_id = $id;
    $specialist = get_specialist($id);
    if (!$specialist) {
        $specialist = [];
    }
    $specialist['photo'] = $specialist['photo'] ? '/uploads/avatars/' . $specialist['photo'] : '/assets/img/cabinet/gray-circle.svg';

    $employee['photo'] = $employee['photo'] ? '/uploads/avatars/' . $employee['photo'] : '/assets/img/cabinet/gray-circle.svg';

    $classifications = get_classifications();
    $cities = get_cities('name ASC');
    $specializations = get_specialist_specialization($id);
    $all_specializations = get_services_by_classification($id);

    $specialization_html .= renderSpecializations($all_specializations, $specializations);


    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/specialist.php', [
        'specialist' => $specialist,
        'specialist_id' => $specialist_id,
        'classifications' => $classifications,
        'cities' => $cities,
        'specialization_html' => $specialization_html
    ]);

    responseJson($json);
}