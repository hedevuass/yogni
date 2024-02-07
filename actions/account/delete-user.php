<?php
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
session_start();

delete_user($_SESSION['user']['id']);

unset($_SESSION['user']);

responseRedirect('/');