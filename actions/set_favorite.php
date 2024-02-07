<?php
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
if (isset($_POST['favorite'])) {
    session_start();
    $_SESSION['favorite'] = json_decode($_POST['favorite']);
    if (isset($_POST['company_page']) && $_POST['company_page']) {
        responseJson(true);
    }
    $specialization = 'lawyers';
    $ids = $_SESSION['favorite']->lawyers;
    if(empty($ids)) {
        $json['specialists'] = "Не найдено избранных специалистов/компаний.";
        $count = 0;
    } else {
        $specialists =  get_specialists_by_ids($ids, 16, 0);
        $count = count_specialists_by_ids($ids); 
        $json['specialists'] = $specialists ? block($specialists) : false;
        if (count($specialists) >= 16) {
            $json['pagination'] = block_pagination_fav($count, 16, 1, $specialization);
        }
    }

    $json['page'] = 2;

    responseJson($json);
}