<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Метод не разрешён']));
}

global $data_base;

// Проверка и извлечение данных
if (empty($_POST['users']) || !is_array($_POST['users'])) {
    exit(json_encode(['status'=>'error','message'=>'Данные не переданы.']));
}

$user = reset($_POST['users']);
$id = (int)($user['id'] ?? 0);
$login = trim($user['login'] ?? '');
$password = $user['password'] ?? '';
$oldPassword = $user['old_password'] ?? '';

if (!$id || $login === '' || $oldPassword === '') {
    exit(json_encode(['status'=>'error','message'=>'Не заполнены обязательные поля.']));
}

// Получаем текущего пользователя
$current = $data_base->selectRow(
    "SELECT * FROM dt_admin_users WHERE id = ? LIMIT 1",
    [$id]
);
if (!$current) {
    exit(json_encode(['status'=>'error','message'=>'Пользователь не найден.']));
}

// 1) Обновляем логин (в любом случае)
try {
    $data_base->update(
        "UPDATE dt_admin_users SET username = ? WHERE id = ?",
        [$login, $id]
    );
} catch (PDOException $e) {
    error_log("Update login for #$id failed: " . $e->getMessage());
    exit(json_encode(['status'=>'error','message'=>'Ошибка при обновлении логина.']));
}

// 2) Если задан новый пароль — проверяем старый и обновляем
if ($password !== '') {
    if ($oldPassword != $current['password_hash']) {
        exit(json_encode(['status'=>'error','message'=>'Неверный текущий пароль.']));
    }

    try {
        $data_base->update(
            "UPDATE dt_admin_users SET password_hash = ? WHERE id = ?",
            [$password, $id]
        );
    } catch (PDOException $e) {
        error_log("Update password for #$id failed: " . $e->getMessage());
        exit(json_encode(['status'=>'error','message'=>'Ошибка при обновлении пароля.']));
    }
}

echo json_encode(['status'=>'success','message'=>'Изменения успешно сохранены.']);
exit;