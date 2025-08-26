<?php
$emails = $_POST['recipients'] ?? [];

$file = $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/note_notification.php';
$contents = file_get_contents($file);

// Генерируем новый PHP-код массива с вложенными массивами email-ов
$new_array_code = "\$recipient_by_type = [\n";

foreach ($emails as $key => $emailList) {
    if (!is_array($emailList)) {
        $emailList = [$emailList];
    }
    $emailList = array_filter($emailList, fn($e) => trim($e) !== '');
    $emails_code = array_map(fn($e) => "'" . addslashes($e) . "'", $emailList);
    $emails_code_str = '[' . implode(', ', $emails_code) . ']';

    $new_array_code .= "    '" . addslashes($key) . "' => " . $emails_code_str . ",\n";
}

$new_array_code .= "];";

// Заменяем старый массив на новый
$updated = preg_replace(
    '/\$recipient_by_type\s*=\s*\[\s*(.*?)\s*\];/s',
    $new_array_code,
    $contents
);

if ($updated === null) {
    die('Ошибка при обработке preg_replace');
}

if (file_put_contents($file, $updated) === false) {
    die('Ошибка при записи файла');
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
