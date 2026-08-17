<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

$json = [];

if (isset($_POST['equipment'])) {
    
    unset_specialist_equipment();

    foreach ($_POST['equipment'] as $id => $value) {
        set_specialist_equipment($_SESSION['user']['id'], (int)$id);
    }
    $json['success'] = true;
} else {
    unset_specialist_equipment();
    $json['success'] = true;
}

responseJson($json);