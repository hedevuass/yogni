<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = [];

define('DELETED_IDS_KEY', 'delete_ids');

// Проверяем обязательные массивы
if (
    isset($_POST['title'], $_POST['meta_title'], $_POST['meta_description'], $_POST['meta_keywords'])
) {
    // Очищаем и нормализуем входные данные
    $titles       = array_map('clean_elem_form', $_POST['title']);
    $metaTitles   = array_map('clean_elem_form', $_POST['meta_title']);
    $metaDescs    = array_map('clean_elem_form', $_POST['meta_description']);
    $metaKeywords = array_map('clean_elem_form', $_POST['meta_keywords']);

    // Собираем ID удалённых тегов
    $deleted = isset($_POST[DELETED_IDS_KEY]) && is_array($_POST[DELETED_IDS_KEY])
        ? array_map('intval', $_POST[DELETED_IDS_KEY])
        : [];

    try {
        // Удаляем теги из БД
        foreach ($deleted as $delId) {
            $data_base->delete('dt_tags', ['id' => $delId]);
        }

        // Обновляем оставшиеся теги
        foreach ($titles as $id => $titleValue) {
            $id = (int) $id;
            if (in_array($id, $deleted, true)) {
                continue;
            }

            $titleClean = $titleValue !== '' ? $titleValue : null;
            $metaTitleClean = isset($metaTitles[$id]) && $metaTitles[$id] !== '' ? $metaTitles[$id] : null;
            $metaDescClean  = isset($metaDescs[$id]) && $metaDescs[$id] !== '' ? $metaDescs[$id] : null;
            $metaKeysClean  = isset($metaKeywords[$id]) && $metaKeywords[$id] !== '' ? $metaKeywords[$id] : null;

            // Обновляем запись
            update_tags_menu(
                $id,
                $titleClean,
                $metaTitleClean,
                $metaDescClean,
                $metaKeysClean
            );
        }

        $response['success'] = 'Данные обновлены';
    } catch (Exception $e) {
        $response['fatal'] = 'Ошибка: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Нет данных для обновления';
}

responseJson($response);
