<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . '/actions/database/functions.php';

session_start();
$userRole = $_SESSION['user_role'] ?? 'editor';

$draw = $_POST['draw'] ?? 0;
$row = $_POST['start'] ?? 0;
$rowperpage = $_POST['length'] ?? 50;

$columnIndex = $_POST['order'][0]['column'] ?? 0;
$columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';

// Безопасно определяем имя колонки
$allowedColumns = ['id', 'name', 'lastname', 'email', 'phone', 'classification', 'city', 'rating', 'computed_rating'];
$columnName = 'id'; // по умолчанию

if (isset($_POST['columns'][$columnIndex]['data']) && in_array($_POST['columns'][$columnIndex]['data'], $allowedColumns)) {
    $columnName = $_POST['columns'][$columnIndex]['data'];
}

$searchValue = $_POST['search']['value'] ?? '';

$searchArray = array();

$searchQuery = " ";


if ($searchValue != '') {
    $searchQuery = " AND (ds.id LIKE ? OR
        ds.name LIKE ? OR
        ds.lastname LIKE ? OR
        ds.email LIKE ? OR
        dsc.phone LIKE ? OR
        dc.title LIKE ? OR
        dc2.name LIKE ? OR
        dcom.title LIKE ? OR
        ds.rating LIKE ?)";
    $searchArray = array(
        "%$searchValue%",
        "%$searchValue%",
        "%$searchValue%",
        "%$searchValue%",
        "%$searchValue%",
        "%$searchValue%",
        "%$searchValue%",
        "%$searchValue%",
        "%$searchValue%"
    );
}

$totalRecords = count_specialists();
$totalRecordwithFilter = count_specialists_filter($searchQuery, $searchArray);
$allowedColumns = ['id', 'name', 'lastname', 'email', 'phone', 'classification', 'city', 'rating', 'computed_rating'];

// Если поле не разрешено — сортируем по ID
if (!in_array($columnName, $allowedColumns)) {
    $columnName = 'id';
}
$empRecords = get_specialists_filter($searchQuery, $columnName, $columnSortOrder, $row, $rowperpage, $searchArray);

$data = [];

foreach ($empRecords as $row) {
    if ($row['active']) $row['active'] = 'checked';
    $row['photo'] = $row['photo'] ? '/uploads/avatars/' . $row['photo'] : '/assets/img/cabinet/gray-circle.svg';

    $registered_at = new DateTime($row['registered_at']);
    $formatted = $registered_at->format('d.m.Y');

    $formatted_activated_at = '';
    if (!empty($row['activated_at'])) {
        $activated_at = new DateTime($row['activated_at']);
        $formatted_activated_at = $activated_at->format('d.m.Y');
    }

    $displayName = $row['company_name'] ? $row['company_name'] : $row['name'] . ' ' . $row['lastname'];

    $deleteButton = '';
    if ($userRole === 'superadmin') {
        $deleteButton = '<button type="submit" class="btn btn-danger delete" data-id="' . $row['id'] . '">Удалить</button>';
    }

    $data[] = array(
        "id" => $row['id'],
        "photo" => '<img src="' . $row['photo'] . '" class="td-photo">',
        "name" => $displayName,
        "email" => $row['email'],
        "phone" => $row['phone'],
        "classification" => $row['classification'],
        "city" => $row['city'],
        "rating" => $row['rating'],
        "computed_rating" => $row['computed_rating'],
        "registered_at" => $formatted,
        "active" => '<input class="form-check-input" type="checkbox" value="" id="flexCheckDisabled" ' 
            . $row['active'] . ' disabled>' 
            . ' ' .($formatted_activated_at ? $formatted_activated_at : ''),
        "action" => '<div class="btn-group" role="group" aria-label="Basic mixed styles example">
                        <button type="submit" class="btn btn-info modalAdd" data-id="' . $row['id'] . '">Редактировать</button>
                        ' . $deleteButton . '
                     </div>'
    );
}

$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

responseJson($response);
