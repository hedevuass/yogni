<?php
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
if (isset($_POST['metro'])) {
    session_start();
    $_SESSION['metro'] = rtrim($_POST['metro'], ',');
    responseJson(true);
}