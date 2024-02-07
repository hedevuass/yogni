<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();
if (isset($_POST['article_id']) && isset($_POST['blog_id'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $blog = get_blog_by_id(intval($data['blog_id']))['specialist_id'];
    if (intval($blog) == intval($_SESSION['user']['id'])) {
        $article_id = intval($data['article_id']);
        delete_article($article_id);
        unset_article_themes($article_id);
        $json['success'] = true;
        responseJson($json);
    }
}