<?php
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

unset($_SESSION['user']);

responseRedirect('/');