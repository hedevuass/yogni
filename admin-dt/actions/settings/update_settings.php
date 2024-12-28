<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newLogin = isset($_POST['login']) ? trim($_POST['login']) : '';
    $newPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
    $oldPassword = isset($_POST['old_password']) ? trim($_POST['old_password']) : '';

    if (empty($newLogin) || empty($newPassword) || empty($oldPassword)) {
        echo 'Все поля обязательны для заполнения.';
        header('Location: /admin-dt/');
        exit;
    }

    $settingsFile = $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
    $settingsContent = file_get_contents($settingsFile);

    // Извлечение текущего пароля
    preg_match("/'password' => '(.*?)'/", $settingsContent, $matches);
    $currentPassword = $matches[1] ?? '';

    if ($oldPassword !== $currentPassword) {
        echo 'Старый пароль неверен.';
        header('Location: /admin-dt/');
        exit;
    }

    // Обновление логина и пароля
    $settingsContent = preg_replace("/'login' => '.*?'/", "'login' => '$newLogin'", $settingsContent);
    $settingsContent = preg_replace("/'password' => '.*?'/", "'password' => '$newPassword'", $settingsContent);

    if (file_put_contents($settingsFile, $settingsContent)) {
        echo 'Данные для входа успешно обновлены.';
        header('Location: /admin-dt/');
        exit;
    } else {
        header('Location: /admin-dt/');
        echo 'Произошла ошибка при обновлении данных.';
    }
}

?>
