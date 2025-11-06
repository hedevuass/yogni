<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $journal = get_post((int)$id);
    $journal['photo'] = $journal['photo'] ? '/uploads/articles/' . $journal['photo'] : '/assets/img/cabinet/gray-circle.svg';
    $journal_id = $journal['id'];
    $journal_theme_id = $journal['theme_id'];
    $journal_theme_title = $journal['theme_title'];

    $journal_themes = get_blog_filter_themes();

    $journal['title'] = $journal['title'];
    $journal['alias'] = $journal['alias'];
    $journal['content'] = $journal['content'];
    $journal['theme_title'] = $journal_theme_title; 

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/post.php', [
        'journal' => $journal,
        'journal_id' => $journal_id,
        'journal_theme_title' => $journal_theme_title,
        'journal_themes' => $journal_themes,
    ]);

    responseJson($json);
}
