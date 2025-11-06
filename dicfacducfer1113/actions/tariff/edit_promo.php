<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$prices = $_POST['prices'];
$priceField = $_POST['price_id']; // Получаем поле для обновления

$log = []; // Лог операций

// Допустимые поля
$validFields = ['price_block1', 'price_block2'];

// Проверяем, что поле для обновления валидное
if (!in_array($priceField, $validFields)) {
    $json['error'] = 'Недопустимое поле для обновления.';
    responseJson($json);
}

foreach ($prices as $priceData) {
    if (!isset($priceData['id'], $priceData['value'])) {
        $log[] = "Пропущен некорректный элемент: " . json_encode($priceData);
        continue;
    }

    $idWithPrefix = $priceData['id'];
    $value = (int)$priceData['value'];

    // Определяем таблицу по префиксу
    if (strpos($idWithPrefix, 's_') === 0) {
        $id = str_replace('s_', '', $idWithPrefix);
        $table = 'dt_service';
    } elseif (strpos($idWithPrefix, 'p_') === 0) {
        $id = str_replace('p_', '', $idWithPrefix);
        $table = 'dt_promo_block';
    } else {
        $log[] = "Неверный формат ID: $idWithPrefix";
        continue;
    }

    $log[] = "Готовлюсь обновить ID: $id, Поле: $priceField, Значение: $value в таблице: $table";

    $query = "UPDATE $table SET $priceField = ? WHERE id = ?";
    $params = [$value, $id];

    try {
        $result = $data_base->update($query, $params);

        if ($result) {
            $log[] = "Успешно обновлено: ID $id, Поле $priceField, Значение $value";
        } else {
            $log[] = "Ошибка обновления для ID: $id, Поле: $priceField";
        }
    } catch (Exception $e) {
        $log[] = "SQL-ошибка для ID: $id. Сообщение: " . $e->getMessage();
    }
}

$json['log'] = $log;
$json['success'] = 'Цены успешно обновлены.';
responseJson($json);
