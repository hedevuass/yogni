<?php
// Подключаем файл с функциями
require_once 'functions.php'; // Убедитесь, что путь правильный и соответствует вашему проекту

header('Content-Type: application/json');

// Получаем параметры из запроса
$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
$post_theme = isset($_GET['post_theme']) ? intval($_GET['post_theme']) : 0;
$post_tag = isset($_GET['post_tag']) ? intval($_GET['post_tag']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 2;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

// Получаем посты
$posts = get_actual_posts($post_type, $post_theme, $post_tag, $limit, $start);

// Возвращаем посты в формате JSON
echo json_encode($posts);
