<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $words = $_POST['forbidden_words'] ?? '';

    // Разбиваем строку по запятым, пробелам и переводам строк
    $wordsArray = preg_split('/[\s,]+/', trim($words));

    // Убираем пустые элементы
    $wordsArray = array_filter($wordsArray);

    if (empty($wordsArray)) {
        echo json_encode(['success' => false, 'error' => 'Список запрещённых слов не может быть пустым']);
        exit;
    }

    try {
        // Проверяем, есть ли уже слова в базе
        $existingWords = $data_base->count("SELECT COUNT(*) FROM dt_forbidden_words");

        if ($existingWords > 0) {
            // Удаляем только если есть новые слова
            $data_base->delete("DELETE FROM dt_forbidden_words");
        }

        // Добавляем новые слова
        foreach ($wordsArray as $word) {
            $data_base->add("INSERT INTO dt_forbidden_words (word) VALUES (?)", [$word]);
        }

        echo json_encode(['success' => true, 'message' => 'Запрещённые слова обновлены']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Ошибка: ' . $e->getMessage()]);
    }
}