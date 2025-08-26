<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = []; // Используем $response для ответа

if (isset($_POST['pages']) && is_array($_POST['pages'])) {
    $data = [];
    foreach ($_POST['pages'] as $id => $pageFields) {
        $data[] = [
            'id'          => (int)$id,
            'title'       => clean_elem_form($pageFields['title']),
            'description' => clean_elem_form($pageFields['description']),
            'keywords'    => clean_elem_form($pageFields['keywords']),
        ];
    }

    // определяем URL возврата (реферер или дефолт)
    $returnUrl = $_SERVER['HTTP_REFERER'] ?? '/admin-dt/journal';
    $host      = parse_url($returnUrl, PHP_URL_HOST);
    $myHost    = $_SERVER['HTTP_HOST'];
    // на всякий случай проверяем, что referer — ваш же домен
    if ($host !== null && $host !== $myHost) {
        $returnUrl = '/admin-dt/journal';
    }

    try {
        foreach ($data as $page_data) {
            if (! update_page($page_data)) {
                // При ошибке внутри цикла сразу редиректим с фатальным сообщением
                $_SESSION['flash_error'] = 'Не удалось обновить страницу с ID ' . $page_data['id'];
                header('Location: ' . $returnUrl);
                exit;
            }
        }
        // Всё прошло успешно
        $_SESSION['flash_success'] = 'Страницы обновлены';
        header('Location: ' . $returnUrl);
        exit;

    } catch (Exception $e) {
        // Ловим исключения и тоже редиректим с ошибкой
        $_SESSION['flash_error'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
        header('Location: ' . $returnUrl);
        exit;
    }
}



