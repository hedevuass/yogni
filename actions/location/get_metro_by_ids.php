<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

session_start();
header('Content-Type: application/json');
$ids = $_POST['ids'] ?? [];
if (!is_array($ids)) {
    $ids = explode(',', $ids);
}
$ids = array_filter($ids, function($v){
    return ctype_digit((string)$v);
});
// Приводим к integer
$ids = array_map('intval', $ids);

if (empty($ids)) {
    echo json_encode([]);
    exit;
}
// Подключаем вашу функцию
$ids_str = implode(',', $ids);
$metro = get_metro_by_ids($ids_str);
// Возвращаем только id и name
$out = array_map(function($m){
    return ['id'=> (int)$m['id'], 'name'=>$m['name']];
}, $metro);
echo json_encode($out);
