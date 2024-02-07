<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';


if (isset($_GET['page'])) {
    session_start();
    foreach ($_GET as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $start = ($data['page'] - 1) * $data['limit'];

    if (!$data['specialization']) {
        $data['specialization'] = 'lawyers';
        $ids = $_SESSION['favorite']->lawyers;
    }
    if ($data['specialization'] == 'lawyers') {
        $ids = $_SESSION['favorite']->lawyers;
    } else if ($data['specialization'] == 'experts') {
        $ids = $_SESSION['favorite']->experts;
    }
    if ($data['specialization'] == 'notaries') {
        $ids = $_SESSION['favorite']->notaries;
    }
    
    $specialists =  get_specialists_by_ids($ids, $data['limit'], $start);

    $json['specialists'] = $specialists ? block($specialists) : false;

    if (count($specialists) == $data['limit']) {
        $json['pagination'] = block_pagination_fav($data['count'], $data['limit'], $data['page'], $data['specialization']);
    }

    $json['page'] = $data['page']++;

    responseJson($json);
}