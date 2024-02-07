<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $type = clean_elem_form($_POST['type']);

    $function = 'get_' . $type;

    $$type = $function((int)$id);

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/' . $type . '.php', [
        $type => $$type,
        'type' => $type
    ]);

    responseJson($json);
}