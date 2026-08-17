<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$all_tags = get_all_tags();

// Сортируем теги по алфавиту
usort($all_tags, function ($a, $b) {
    return strcmp($a['title'], $b['title']);
});

$tags_html = renderTagsWithMetaJournal($all_tags);

$json = [];
$json['code'] = get_view(
    $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/journal_tags_menu.php',
    ['tags_html' => $tags_html]
);

responseJson($json);