<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $type = clean_elem_form($_POST['type']);

    // Подготовка данных для ответа
    if ($type === 'banner') {
        $banner = get_banner((int)$id);
        $slides = get_slides((int)$id);
        $template_path = 'modules/modal/banner.php';  
    } elseif ($type === 'journal-banner') {
        $banner = get_journal_banner((int)$id);
        $template_path = 'modules/modal/journal_banner.php'; 
    }

    // Создаём ответ JSON
    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . $template_path, [
        'banner' => $banner,
        'slides' => $slides
    ]);

    responseJson($json);
}

