<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

session_start();

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $name = clean_elem_form($_POST['name']);

    if (!$name) {
        $city = get_city($id);
        $name = $city['name'];
    }

    $_SESSION['city'] = [
        'id'    => $id,
        'name'  => $name,
    ];

    unset($_SESSION['metro']);

    session_write_close();

    responseJson(true);
}
