<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$specialistId = $_POST['specialist_id'] ?? null;
$optionId     = $_POST['option_id']     ?? null;
$orderId      = $_POST['order_id']      ?? null;
$amount       = $_POST['amount']        ?? null;

// Проверяем, что все данные переданы
if (!$specialistId || !$optionId || !$orderId || !$amount) {
    echo json_encode([
        'success' => false,
        'error'   => 'Недостаточно данных'
    ]);
    exit;
}

// Рассчитываем дату окончания опции (1 месяц вперёд)
$endDate = date('Y-m-d', strtotime('+1 month'));

try {
    // 1) Вставляем запись в dt_options_list вместе с orderId
    $sql = "
        INSERT INTO dt_options_list
            (specialist_id, option_id, end_date, moderation, orderId)
        VALUES
            (?, ?, ?, 0, ?)
    ";
    $result = $data_base->add($sql, [
        $specialistId,
        $optionId,
        $endDate,
        $orderId
    ]);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'error'   => 'Ошибка при подключении опции'
        ]);
        exit;
    }

    $dateStart = date('Y-m-d');
    $dateEnd = date('Y-m-d', strtotime('+1 month', strtotime($dateStart)));

    // Получаем имя опции
    $option = $data_base->selectRow("
    SELECT name 
    FROM dt_option 
    WHERE id = ?
", [$optionId]);

    $serviceName = $option ? 'Опция "' . $option['name'] . '"' : 'Опция';

    // 2) Логируем платёж в dt_payments
    $data_base->add(
        "INSERT INTO dt_payments 
            (qrcId, orderId, tableType, status, specialist_id, amount, paymentDate, date_start, date_end, serviceName)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
        [
            null,
            $orderId,
            'option',
            'payment_paid',
            $specialistId,
            $amount,
            $dateStart,
            $dateEnd,
            $serviceName
        ]
    );

    // 3) Возвращаем успех
    echo json_encode([
        'success' => true,
        'message' => 'Опция подключена',
        'id'      => $result
    ]);

    clearCache('specialists_lawyers');
    clearCache('specialists_experts');
    clearCache('specialists_notaries');
    clearCache('promo_block_top_specialists');

    clearCache('catalog_by_service');
    clearCache('catalog_by_classification');
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
    exit;
}
