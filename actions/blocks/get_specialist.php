<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';


if (isset($_GET['page'])) {
    foreach ($_GET as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    $start = ($data['page'] - 1) * $data['limit'];

    if (!$data['company']) unset($_GET['company']);
    if (!$data['specialist']) unset($_GET['specialist']);

    if ($data['category']) {
        $specialists =  get_specialists_by_service($data['category'], $data['limit'], $start);
        $vars = $data['category'];
    }

    if ($data['classification']) {
        $data['classification'] = $vars['classification'] = explode(',', $data['classification']);
        $specialists = get_specialists_by_classification($data['classification'], $data['limit'], $start);
    }
    
    $json['specialists'] = $specialists ? block($specialists) : false;

    if (count($specialists) == $data['limit']) {
        $json['pagination'] = block_pagination($data['count'], $data['limit'], $data['page'], $data['requestion'], $vars);
    }

    $json['page'] = $data['page']++;

    responseJson($json);
}