<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

require_once __DIR__ . '/../libs/securimage/securimage.php';

$forbidden_words = getForbiddenWords();

/* $securimage = new Securimage();

$captchaCode = $_POST['captcha_code'] ?? '';
$captchaId = $_POST['captcha_id'] ?? null;

if ($securimage->check($captchaCode, $captchaId) == false) {
    echo json_encode([
        'success' => false,
        'message' => 'Неправильный код каптчи. Попробуйте снова.'
    ]);
    exit;
} */

if (isset($_POST['specialist'])) {
    $fields_to_check = ['name', 'contact', 'review'];

    foreach ($_POST as $key => $value) {
        if (in_array($key, $fields_to_check)) {
            $foundItems = containsForbiddenWords($value, $forbidden_words);
    
            if ($foundItems) {
                $elements = [];
                $words = [];
    
                foreach ($foundItems as $item) {
                    if ($item['type'] === 'element') {
                        $elements[] = '"' . $item['value'] . '"';
                    } elseif ($item['type'] === 'word') {
                        $words[] = '"' . $item['value'] . '"';
                    }
                }
    
                if (!empty($elements) && !empty($words)) {
                    $allItems = array_unique(array_merge($elements, $words));
                    $json['error'][$key][] = 'Обнаружены запрещённые слова и элементы: ' . implode(', ', $allItems) . '.';
                } elseif (!empty($elements)) {
                    $uniqueElements = array_unique($elements);
                    $json['error'][$key][] = 'Обнаружены запрещённые элементы: ' . implode(', ', $uniqueElements) . '.';
                } elseif (!empty($words)) {
                    $uniqueWords = array_unique($words);
                    $json['error'][$key][] = 'Обнаружены запрещённые слова: ' . implode(', ', $uniqueWords) . '.';
                }
            }
        }
    
        $data[$key] = clean_elem_form($value);
    }    

    if (!$data['contact']) $json['error']['contact'] = 'Укажите email';
    if (!empty($data['contact'])) {
        if (!filter_var($data['contact'], FILTER_VALIDATE_EMAIL)) {
            $json['error']['contact'] = 'Некорректный email';
        }
    }
    if (!$data['name']) $json['error']['name'] = 'Укажите ваше имя';
    if (!$data['rating']) $json['error']['rating'] = 'Поставьте оценку';
    if (!$data['review']) {
        $json['error']['review'] = 'Введите текст отзыва';
    }

    if (isset($json['error'])) responseJson($json);

    $review_status = set_review($data);

    if ($review_status === "duplicate") {
        $json['error']['fatal_error'] = 'Вы уже оставляли отзыв для этого специалиста.';
    } elseif ($review_status) {
        $json['success'] = 'Спасибо за отзыв!';
    } else {
        $json['error']['fatal_error'] = 'Добавление отзывов временно не работает';
    }

    responseJson($json);
}
