<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $document = get_document_o_proekte((int)$id);

    $document['title'] = $document['title'];
    $document['content'] = $document['content'];

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/index_modal.php', [
        'document' => $document,
    ]);

    responseJson($json);
}
