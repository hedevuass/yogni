<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if(isset($_POST['start'])) {
    $start = clean_elem_form($_POST['start']);
    $blog = get_specialist_blog($_SESSION['user']['id']);
    if ($blog) {
        $blog_id = $blog['id'];
        $articles = get_blog_articles($blog_id, 0, 5, $start, [0, 1]);
        $json['articles'] = block_articles_cabinet($articles, false);
        if (count($articles) < 5) {
            $json['no_more'] = true;
        }
        responseJson($json);
    }
}