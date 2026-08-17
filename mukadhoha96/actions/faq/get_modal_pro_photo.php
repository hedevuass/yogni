<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $type = 'pro_photo';
    $faq_promo = get_faq_pro_photo((int)$id);
    $faq_promo['id'] = $faq_promo['id'];
    $faq_promo['title'] = $faq_promo['title'];

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/faq_promo.php', [
        'faq_promo' => $faq_promo,
        'type' => $type,
    ]);

    responseJson($json);
}