<?php

require $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

session_start();

/**
 * Получаем данные пользователя с БД по логину
 * @param string $username
 * @return array|null
 */
function get_admin_credentials_by_username(string $username): ?array
{
    global $data_base;

    if (!$data_base) {
        return null;
    }

    $result = $data_base->selectRow(
        "SELECT * FROM dt_admin_users WHERE is_active = 1 AND username = ? LIMIT 1",
        [$username]
    );

    return is_array($result) ? $result : null;
}

// Обработка POST-запроса авторизации (форма логина)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $user = get_admin_credentials_by_username($_POST['username']);

    if ($user && $_POST['password'] === $user['password_hash']) {
        // Логин и пароль корректны — сохраняем сессию
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['user_name']     = $user['username'];
        $_SESSION['user_role']     = $user['role'];

        header('Location: ' . ADMIN_HREF);
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    // Если не авторизован, показываем форму логина
?>
    <!DOCTYPE html>
    <html lang="ru">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Вход в админку</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #212529;
                color: #f8f9fa;
            }

            .card {
                background-color: #343a40;
                border: none;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            }

            .form-control {
                background-color: #495057 !important;
                border: 1px solid #6c757d;
                color: #f8f9fa !important;
                border-radius: 8px;
            }

            .form-control:focus {
                background-color: #495057;
                border-color: #adb5bd;
                box-shadow: 0 0 0 0.25rem rgba(108, 117, 125, 0.25);
                color: #f8f9fa;
            }

            .form-label {
                color: #adb5bd;
            }

            .btn-primary {
                background-color: #0d6efd;
                border-color: #0d6efd;
                border-radius: 8px;
                padding: 10px;
                transition: background-color 0.3s ease;
            }

            .btn-primary:hover {
                background-color: #0b5ed7;
                border-color: #0a58ca;
            }

            .alert-danger {
                background-color: #dc3545;
                border-color: #dc3545;
                color: #f8f9fa;
                border-radius: 8px;
            }

            .card-body {
                padding: 2rem;
            }

            h3 {
                color: #f8f9fa;
                font-weight: 500;
            }

            .input-group-text {
                background-color: #495057;
                border: 1px solid #6c757d;
                color: #adb5bd;
                border-radius: 0 8px 8px 0;
                cursor: pointer;
            }
        </style>
    </head>

    <body class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title text-center text-white mb-4">Административная панель</h3>
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Логин</label>
                                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <span class="input-group-text" onclick="togglePassword()">
                                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Войти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bootstrap JS -->
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.getElementById('togglePasswordIcon');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                }
            }
        </script>
    </body>

    </html>
    <?php exit; ?>
<?php
    exit; // дальше код не выполняем, пока не авторизован
}

// Если авторизован — определяем константы и загружаем маршруты
define('LOGIN', $_SESSION['user_name']);
define('USER_ROLE', $_SESSION['user_role']);

$routes = include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'routes.php';

// Фильтруем маршруты по правам
$routes = array_filter($routes, function ($item) {
    $allowed = $item['roles'] ?? ['admin'];
    return in_array(USER_ROLE, $allowed, true);
});

// Получаем текущий URI без параметров
$urn = strtok($_SERVER['REQUEST_URI'], '?');

// Получаем список разрешённых URI
$allowedUris = array_keys($routes);

// Если пользователь пытается открыть неразрешённый маршрут — перенаправляем на первый разрешённый
if (!isset($routes[$urn])) {
    if (!empty($allowedUris)) {
        $defaultUri = $allowedUris[0];
        header("Location: $defaultUri");
        exit;
    } else {
        http_response_code(403);
        echo "<h1>403 Доступ запрещён</h1>";
        exit;
    }
}

// Подключаем интерфейс
require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/header.php';
require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/' . $routes[$urn]['module'] . '.php';
require $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/footer.php';
