<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    $block = get_block((int)$id);

    $function = 'get_' . $block['table'];

    ${$block['table']} = $function();

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/block.php', [
        'block' => $block,
        $block['table'] => ${$block['table']}
    ]);

    responseJson($json);
}