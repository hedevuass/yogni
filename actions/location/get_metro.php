<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

if (isset($_GET['term'])) {
    $search = clean_elem_form($_GET['term']);

    $metro = get_metro_by_name($search);

    responseJson($metro);
}
