<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';


if (isset($_GET['page'])) {
    foreach ($_GET as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    $start = ($data['page'] - 1) * $data['limit'];

    $companies = full_search_companies($data['requestion'], $start, $data['limit'], $data['filter']);
    $specialists = full_search_specialists($data['requestion'], $start, $data['limit'], $data['filter']);
    
    $json['specialists'] = $specialists ? block($specialists) : false;

    if ($json['specialists']) {
        $json['specialists'] .= $companies ? block($companies) : '';
    } else {
        $json['specialists'] = $companies ? block($companies) : false;
    }

    if (count($specialists) == $data['limit'] || count($companies) == $data['limit']) {
        $json['pagination'] = block_pagination_search($data['count'], $data['limit'], $data['page'], $data['requestion'], $data['filter']);
    }

    $json['page'] = $data['page']++;

    responseJson($json);
}