<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    $faq_pages = get_faq_page((int)$id);

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/faq_modal.php', [
        'faq_pages' => $faq_pages,
    ]);

    responseJson($json);
}