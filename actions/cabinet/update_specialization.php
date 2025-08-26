<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['specializations'])) {
    
    unset_specialist_specializations();

    foreach ($_POST['specializations'] as $id => $specialization) {
        set_specialist_specialization($_SESSION['user']['id'], $id);
    }
    $json['success'] = true;
} else {
    /* $json['error'] = 'Выберите хотя бы одно направление.'; */
    unset_specialist_specializations();
}
responseJson($json);