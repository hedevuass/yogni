<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';


if (isset($_GET['page'])) {

    foreach ($_GET as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $start = ($data['page'] - 1) * $data['limit'];
    $blog_id = intval($data['blog_id']);

    $theme_id = 0;
    if (isset($_GET['theme'])) {
        $theme_id = get_service_by_slug($_GET['theme'])['id'];
    }

    if ($blog_id==-1) {
        $articles = get_main_blog_articles($theme_id, $data['limit'], $start);
    } else {
        $articles = get_blog_articles($blog_id, $theme_id, $data['limit'], $start);
    }

    $json['articles'] = $articles ? block_articles($articles) : false;

    if (count($articles) == $data['limit']) {
        $json['pagination'] = block_pagination_blog($data['count'], $data['limit'], $data['page'], $blog_id);
    }

    $json['page'] = $data['page']++;

    responseJson($json);
}