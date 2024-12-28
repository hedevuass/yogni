<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['socials'])) {
    $socials = $_POST['socials'];

    try {
        foreach ($socials as $social) {
            // Очищаем и проверяем данные
            $id = isset($social['id']) ? (int)clean_elem_form($social['id']) : 0;
            $link = isset($social['link']) ? trim(clean_elem_form($social['link'])) : '';

            // Пропускаем некорректные данные
            if ($id > 0 && !empty($link)) {
                update_social(['id' => $id, 'link' => $link]);
            }
        }

        // После успешного обновления перенаправляем
        header('Location: /admin-dt/');
        exit;

    } catch (Exception $e) {
        // В случае ошибки записываем её в JSON для отладки
        $json['fatal'] = 'Ошибка обработки Соц. сети: ' . $e->getMessage();
        header('Location: /admin-dt/');
        exit;
    }
}
