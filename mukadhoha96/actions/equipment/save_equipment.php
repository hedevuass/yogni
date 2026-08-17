<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$category = $_POST['category'] ?? '';
$name = trim($_POST['name'] ?? '');

if ($name === '') {
    echo json_encode(['error' => 'Имя не может быть пустым']);
    exit;
}

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/equipment/';
// Создаем директорию, если её вдруг нет
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 1. Получаем текущие данные, если это редактирование
$existing = [];
if ($id !== 'new') {
    $existing = $data_base->selectRow("SELECT img, img_pink FROM dt_equipment WHERE id = ?", [$id]);
}

$img = $existing['img'] ?? null;
$img_pink = $existing['img_pink'] ?? null;

// 2. Обработка ОБЫЧНОЙ иконки
if (isset($_POST['delete_normal']) && $_POST['delete_normal'] === '1') {
    if ($img && file_exists($uploadDir . $img)) @unlink($uploadDir . $img);
    $img = null;
} elseif (isset($_FILES['img_normal']) && $_FILES['img_normal']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['img_normal']['name'], PATHINFO_EXTENSION);
    $newName = uniqid('eq_') . '.' . $ext; // Генерируем уникальное имя
    
    if (move_uploaded_file($_FILES['img_normal']['tmp_name'], $uploadDir . $newName)) {
        if ($img && file_exists($uploadDir . $img)) @unlink($uploadDir . $img); // Удаляем старую
        $img = $newName;
    }
}

// 3. Обработка РОЗОВОЙ иконки
if (isset($_POST['delete_pink']) && $_POST['delete_pink'] === '1') {
    if ($img_pink && file_exists($uploadDir . $img_pink)) @unlink($uploadDir . $img_pink);
    $img_pink = null;
} elseif (isset($_FILES['img_pink']) && $_FILES['img_pink']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['img_pink']['name'], PATHINFO_EXTENSION);
    $newNamePink = uniqid('eq_pink_') . '.' . $ext;
    
    if (move_uploaded_file($_FILES['img_pink']['tmp_name'], $uploadDir . $newNamePink)) {
        if ($img_pink && file_exists($uploadDir . $img_pink)) @unlink($uploadDir . $img_pink);
        $img_pink = $newNamePink;
    }
}

// 4. Запись в базу данных
if ($id === 'new') {
    $data_base->add(
        "INSERT INTO dt_equipment (category, name, img, img_pink) VALUES (?, ?, ?, ?)",
        [$category, $name, $img, $img_pink]
    );
} else {
    // В зависимости от вашей обертки БД, здесь может быть $data_base->query или $data_base->update
    $data_base->update(
        "UPDATE dt_equipment SET category = ?, name = ?, img = ?, img_pink = ? WHERE id = ?",
        [$category, $name, $img, $img_pink, $id]
    );
}

echo json_encode(['success' => true]);
exit;