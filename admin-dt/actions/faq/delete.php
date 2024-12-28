<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . '/actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $type = clean_elem_form($_POST['type']);

    if ($type === 'promo') {
            delete_faq_promo($id);
    } elseif ($type === 'pro_photo') {
            delete_faq_pro_photo($id);
    } else {
            delete_faq_page($id);
    }

    if (delete_service($id)) {
        $json['success'] = 'Раздел удалён';
    } else {
        $json['error'] = 'Удаление раздела временно недоступно';
    }

    responseJson($json);
}