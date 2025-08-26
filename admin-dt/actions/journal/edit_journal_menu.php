<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = [];

if (
    isset($_POST['meta_title_journal']) &&
    isset($_POST['meta_description_journal']) &&
    isset($_POST['meta_keywords_journal'])  // <-- новый массив
) {
    $data = [];
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $response['debug'] = ['received_data' => $data];

    try {
        foreach ($data['meta_title_journal'] as $key => $metaTitle) {
            $id = (int) substr($key, 2);

            // Нормализация: пустая строка → NULL
            $metaTitle       = $metaTitle === '' ? null : $metaTitle;
            $metaDescription = isset($data['meta_description_journal'][$key]) && $data['meta_description_journal'][$key] !== ''
                ? $data['meta_description_journal'][$key]
                : null;
            $metaKeywords    = isset($data['meta_keywords_journal'][$key]) && $data['meta_keywords_journal'][$key] !== ''
                ? $data['meta_keywords_journal'][$key]
                : null;

            // Передаём в обновляющую функцию новый параметр $metaKeywords
            update_service_meta($id, $metaTitle, $metaDescription, $metaKeywords);
        }

        $response['success'] = 'Данные обновлены';
    } catch (Exception $e) {
        $response['fatal'] = 'Ошибка: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Нет данных';
}

responseJson($response);

?>
