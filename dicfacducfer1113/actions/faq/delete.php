<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . '/actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $type = clean_elem_form($_POST['type']);

    try {
        if ($type === 'promo') {
            delete_faq_promo($id);
            $json['success'] = 'Раздел удалён';
        } elseif ($type === 'pro_photo') {
            delete_faq_pro_photo($id);
            $json['success'] = 'Раздел удалён';
        } else {
            delete_faq_page($id);
            $json['success'] = 'Раздел удалён';
        }
    } catch (Exception $e) {
        $json['error'] = 'Удаление раздела временно недоступно';
    }

    responseJson($json);
}