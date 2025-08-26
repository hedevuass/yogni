<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = ['success' => false];

if (isset($_POST['slide_id'], $_POST['banner_id'])) {
    $slideId = (int)$_POST['slide_id'];
    $bannerId = (int)$_POST['banner_id'];

    // Удаление слайда из базы данных
    $query = "DELETE FROM dt_banners_sliders WHERE id = ? AND banner_id = ?";
    $deleted = $data_base->delete($query, [$slideId, $bannerId]);

    if ($deleted) {
        $response['success'] = true;
        $response['message'] = 'Слайд успешно удалён.';
    } else {
        $response['message'] = 'Ошибка при удалении слайда.';
    }
} else {
    $response['message'] = 'Некорректные данные запроса.';
}

echo json_encode($response);