<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

function fail(string $msg): never {
    echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

$specialist = get_specialist($_SESSION['user']['id'] ?? 0);
if (!$specialist) {
    http_response_code(403);
    fail('Нет доступа');
}
$specialist_id = (int)$specialist['id'];

$from = (string)($_POST['from'] ?? '');
$to   = (string)($_POST['to'] ?? '');
$re   = '/^\d{4}-\d{2}-\d{2}$/';

if (!preg_match($re, $from) || !strtotime($from) || !preg_match($re, $to) || !strtotime($to)) {
    fail('Некорректная дата');
}
if ($from === $to) fail('Нельзя вставить занятия в тот же день');

$now   = new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow'));
$dicts = get_schedule_dicts($specialist);

// ВСЕ занятия исходного дня, включая прошедшие
$source = get_schedule_lessons_by_date($specialist_id, $from);   // подставьте свою функцию
if (!$source) fail('В скопированном дне нет занятий');

$ready = [];
$skipped = 0;
$skip_reasons = [];
foreach ($source as $row) {
    $in = [
        'status'       => $row['status'],
        'date'         => $to,
        'time'         => substr($row['start_time'], 0, 5),   // HH:MM
        'duration'     => $row['duration_min'],
        'direction'    => !empty($row['service_id']) ? (string)$row['service_id'] : 'custom',
        'custom_title' => $row['custom_title'] ?? '',
        'teacher'      => (string)$row['employee_id'],
        'hall'         => (string)$row['room_idx'],
        'level'        => $row['level'],
    ];
    [$data, $err] = validate_lesson($in, $dicts, $now);
    if ($err) {
        $skipped++;
        $skip_reasons[] = $err;   // для отладки
        continue;
    }
    $ready[] = $data;
}

if (!$ready) {
    error_log('schedule_paste skipped: ' . json_encode($skip_reasons, JSON_UNESCAPED_UNICODE));
    fail('Ни одно занятие нельзя вставить: время уже прошло или данные устарели');
}

$lessons = [];
foreach ($ready as $data) {
    $id = add_schedule_lesson($specialist_id, $data);
    if (!$id) { $skipped++; continue; }
    $lessons[] = [
        'id'   => (int)$id,
        'data' => [
            'status'       => (string)$data['status'],
            'time'         => (string)$data['time'],
            'duration'     => (string)$data['duration'],
            'direction'    => $data['service_id'] ? (string)$data['service_id'] : 'custom',
            'custom_title' => (string)($data['custom_title'] ?? ''),
            'teacher'      => (string)$data['employee_id'],
            'hall'         => (string)$data['room_idx'],
            'level'        => (string)$data['level'],
            'date'         => $to,
        ],
    ];
}

echo json_encode(['success' => true, 'lessons' => $lessons, 'skipped' => $skipped], JSON_UNESCAPED_UNICODE);