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

// 1) Удаление пользователя
if (!empty($_POST['delete_user_id'])) {
    $id = (int) $_POST['delete_user_id'];
    try {
        $data_base->delete(
            "DELETE FROM dt_admin_users WHERE id = ?",
            [$id]
        );
        echo json_encode(['status' => 'success', 'message' => 'Пользователь удалён.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при удалении.']);
    }
    exit;
}

// 2) Обновление существующих пользователей
if (!empty($_POST['users']) && is_array($_POST['users'])) {
    foreach ($_POST['users'] as $rawId => $user) {
        $id    = (int) $rawId;
        $login = trim($user['login']  ?? '');
        $role  = trim($user['role']   ?? '');
        $pass  = trim($user['password'] ?? '');

        // Если оба поля (логин и пароль) пустые — удаляем пользователя
        if ($login === '' && $pass === '') {
            try {
                $data_base->delete(
                    "DELETE FROM dt_admin_users WHERE id = ?",
                    [$id]
                );
            } catch (PDOException $e) {
                error_log("Delete user #$id failed: " . $e->getMessage());
                continue;
            }
            continue;
        }

        // Пропускаем, если нет обязательных полей (логин или роль)
        if ($login === '' || $role === '') {
            continue;
        }

        // 2a) Обновление логина и роли
        try {
            $data_base->update(
                "UPDATE dt_admin_users
                   SET username = ?, role = ?
                 WHERE id = ?",
                [$login, $role, $id]
            );
        } catch (PDOException $e) {
            error_log("Update user #$id failed: " . $e->getMessage());
            continue;
        }

        // 2b) Обновление пароля (если было заполнено)
        if ($pass !== '') {
            try {
                $data_base->update(
                    "UPDATE dt_admin_users
                       SET password_hash = ?
                     WHERE id = ?",
                    [$pass, $id]
                );
            } catch (PDOException $e) {
                error_log("Update password for #$id failed: " . $e->getMessage());
            }
        }
    }
}

// Функция добавления нового пользователя
function addUser($data_base, $data)
{
    $login = trim($data['login'] ?? '');
    $pass  = trim($data['password'] ?? '');
    $role  = trim($data['role'] ?? 'editor');

    if ($login === '' || $pass === '') {
        return;
    }

    // Проверка на существование логина
    $exists = $data_base->selectRow(
        "SELECT 1 FROM dt_admin_users WHERE username = ? LIMIT 1",
        [$login]
    );

    if ($exists) {
        echo json_encode(['status' => 'error', 'message' => "Логин '{$login}' уже существует."]);
        exit;
    }

    // Добавление в БД
    try {
        $data_base->add(
            "INSERT INTO dt_admin_users (username, password_hash, role, is_active)
             VALUES (?, ?, ?, 1)",
            [$login, $pass, $role]
        );
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => "Ошибка при добавлении: {$e->getMessage()}"]);
        exit;
    }
}

// Обработка админа
if (!empty($_POST['new_admin']['login']) && !empty($_POST['new_admin']['password'])) {
    addUser($data_base, $_POST['new_admin']);
}

// Обработка редактора
if (!empty($_POST['new_editor']['login']) && !empty($_POST['new_editor']['password'])) {
    addUser($data_base, $_POST['new_editor']);
}

echo json_encode(['status' => 'success', 'message' => 'Изменения успешно сохранены.']);
exit;