<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$specialistId = $_POST['specialist_id'] ?? null;
$tariffId = $_POST['tariff_id'] ?? null;
$duration = $_POST['duration'] ?? 1;
$amount = $_POST['amount'];
$orderId = $_POST['order_id'];

if (!$specialistId || !$tariffId) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}

$endDate = date('Y-m-d', strtotime("+{$duration} months"));

try {
    $existingTariff = $data_base->selectRow("
        SELECT id 
        FROM dt_tariff_list 
        WHERE specialist_id = ?
    ", [$specialistId]);

    if ($existingTariff) {
        $sql = "UPDATE dt_tariff_list 
        SET 
            tariff_id = ?, 
            end_date = ?, 
            duration = ?,
            moderation = 0
        WHERE specialist_id = ? 
        ORDER BY (moderation = 0) DESC, id DESC
        LIMIT 1";

        $result = $data_base->update($sql, [
            $tariffId,
            $endDate,
            $duration,
            $specialistId
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Тариф обновлен']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка при обновлении тарифа']);
        }

        $dateStart = date('Y-m-d');
        $dateEnd = date('Y-m-d', strtotime("+{$duration} months"));

        // Получаем имя тарифа
        $tariff = $data_base->selectRow("
            SELECT name 
            FROM dt_tariff 
            WHERE id = ?
        ", [$tariffId]);

        $serviceName = $tariff ? 'Тариф "' . $tariff['name'] . '"' : 'Тариф';

        $data_base->add(
            "INSERT INTO dt_payments 
                (qrcId, orderId, tableType, status, specialist_id, amount, paymentDate, date_start, date_end, serviceName)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
            [
                null,
                $orderId,
                'tariff',
                'payment_paid',
                $specialistId,
                $amount,
                $dateStart,
                $dateEnd,
                $serviceName
            ]
        );
    } else {
        $dateStart = date('Y-m-d');
        $dateEnd = date('Y-m-d', strtotime("+{$duration} months"));

        // Получаем имя тарифа
        $tariff = $data_base->selectRow("
            SELECT name 
            FROM dt_tariff 
            WHERE id = ?
        ", [$tariffId]);

        $serviceName = $tariff ? 'Тариф "' . $tariff['name'] . '"' : 'Тариф';

        $data_base->add(
            "INSERT INTO dt_payments 
                (qrcId, orderId, tableType, status, specialist_id, amount, paymentDate, date_start, date_end, serviceName)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
            [
                null,
                $orderId,
                'tariff',
                'payment_paid',
                $specialistId,
                $amount,
                $dateStart,
                $dateEnd,
                $serviceName
            ]
        );

        $sql = "INSERT INTO dt_tariff_list (specialist_id, tariff_id, end_date, duration, orderId, moderation)
                VALUES (?, ?, ?, ?, ?, 0)";
        $result = $data_base->add($sql, [
            $specialistId,
            $tariffId,
            $endDate,
            $duration,
            $orderId
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Тариф подключен', 'id' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка при подключении тарифа']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
