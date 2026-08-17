<?php
header('Content-Type: application/json; charset=utf-8');

include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = ['success' => false, 'message' => ''];

// Проверяем все необходимые параметры
if (
    isset($_POST['id'], $_POST['type'], $_POST['price'])
    && in_array($_POST['type'], ['banner', 'journal-banner'], true)
) {
    $bannerId = (int) $_POST['id'];
    $priceRaw = $_POST['price'];
    $price = is_numeric($priceRaw) ? (float) $priceRaw : null;

    if ($price === null) {
        $response['message'] = 'Неверный формат цены.';
        echo json_encode($response);
        exit;
    }

    try {
        if ($_POST['type'] === 'banner') {
            // Обновляем цену в обычных баннерах
            $updated = $data_base->update(
                "UPDATE dt_banners SET price = ? WHERE id = ?",
                [$price, $bannerId]
            );
        } else {
            // Обновляем цену в баннерах журнала
            $updated = $data_base->update(
                "UPDATE dt_journal SET price = ? WHERE id = ?",
                [$price, $bannerId]
            );
        }

        if ($updated) {
            $response['success'] = true;
            $response['message'] = 'Цена успешно обновлена.';
        } else {
            $response['message'] = 'Не удалось сохранить новую цену. Попробуйте ещё раз.';
        }

    } catch (Exception $e) {
        // На случай ошибок БД
        $response['message'] = 'Серверная ошибка: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Некорректный запрос: отсутствуют обязательные параметры.';
}

echo json_encode($response);
