<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

$specialist = get_specialist($_SESSION['user']['id'] ?? 0);
if (!$specialist) {
    http_response_code(403);
    echo json_encode(['error' => 'Нет доступа']);
    exit;
}
$specialist_id = (int)$specialist['id'];

$ops = json_decode($_POST['ops'] ?? '[]', true);
if (!is_array($ops) || !$ops || count($ops) > 100) {
    echo json_encode(['error' => 'Нет данных для сохранения']);
    exit;
}

$now   = new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow'));   // тот же пояс, что на странице
$dicts = get_schedule_dicts($specialist);

/* проверяем всё, ничего не записывая */
$prepared = [];
$errors   = [];

foreach ($ops as $op) {
    $type = $op['op'] ?? '';
    $key  = (string)($op['key'] ?? '');
    $id   = (int)($op['id'] ?? 0);

    if ($type === 'delete') {
        $lesson = $id ? get_schedule_lesson($id, $specialist_id) : null;
        if (!$lesson || schedule_lesson_is_done($lesson, $now)) { $errors[$key] = 'Это занятие нельзя удалить'; continue; }
        $prepared[] = ['op' => 'delete', 'key' => $key, 'id' => $id];
        continue;
    }

    if ($type !== 'add' && $type !== 'update') { $errors[$key] = 'Неизвестное действие'; continue; }

    [$data, $err] = validate_lesson((array)($op['data'] ?? []), $dicts, $now);
    if (!empty($err)) {
        echo json_encode([
            'success' => false,
            'errors'  => $err 
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($type === 'update') {
        $lesson = $id ? get_schedule_lesson($id, $specialist_id) : null;
        if (!$lesson || schedule_lesson_is_done($lesson, $now)) { 
            echo json_encode(['success' => false, 'error' => 'Это занятие нельзя изменить'], JSON_UNESCAPED_UNICODE); 
            exit; 
        }
    }

    $prepared[] = ['op' => $type, 'key' => $key, 'id' => $id, 'data' => $data];
}

if ($errors) {
    echo json_encode(['error' => reset($errors)], JSON_UNESCAPED_UNICODE);
    exit;
}

/* записываем */
$ids = [];
foreach ($prepared as $p) {
    if ($p['op'] === 'add') {
        $new_id = add_schedule_lesson($specialist_id, $p['data']);
        if (!$new_id) { echo json_encode(['error' => 'Не удалось сохранить занятие']); exit; }
        $ids[$p['key']] = (int)$new_id;
    } elseif ($p['op'] === 'update') {
        update_schedule_lesson($p['id'], $specialist_id, $p['data'], $now);
    } else {
        delete_schedule_lesson($p['id'], $specialist_id, $now);
    }
}

echo json_encode(['success' => true, 'ids' => $ids]);