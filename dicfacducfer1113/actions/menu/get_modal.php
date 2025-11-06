<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $category = $_POST['category'] ?? null; // Получаем категорию
    $menu_item = get_menu_item((int)$id);
    $classifications = get_filtered_classifications();
    $menu_items = get_menu_items();
    $menu_id = $menu_item['id'] ?? null;
    $slug = $_POST['slug'] ?? null;
    $page_id = $_POST['page_id'] ?? null;

    // Если передан slug, вызываем шаблон для service_parent
    if (!empty($slug)) {
        $service_parent = get_service_parent($page_id);
        $alternatives = get_alternative_page($page_id);
        $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/menu_modal_parent.php', [
            'service_parent' => $service_parent,
            'alternatives' => $alternatives,
        ]);
    } elseif (!empty($category)) {
        // Если slug не передан, вызываем стандартный шаблон menu_modal.php
        // Проверяем, существует ли изображение
        $menu_item['img'] = !empty($menu_item['img'])
            ? '/assets/img/' . $menu_item['img']
            : '/assets/img/cabinet/gray-circle.svg';

        $menu_item['title'] = $menu_item['title'] ?? '';
        $menu_item['slug'] = $menu_item['slug'] ?? '';

        $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/menu_modal.php', [
            'menu_item' => $menu_item,
            'menu_items' => $menu_items,
            'classifications' => $classifications,
            'menu_id' => $menu_id,
            'category' => $category,
        ]);
    }
    
    else {
        // Если slug не передан, вызываем стандартный шаблон menu_modal.php
        // Проверяем, существует ли изображение
        $menu_item['img'] = !empty($menu_item['img'])
            ? '/assets/img/' . $menu_item['img']
            : '/assets/img/cabinet/gray-circle.svg';

        $menu_item['title'] = $menu_item['title'] ?? '';
        $menu_item['slug'] = $menu_item['slug'] ?? '';

        $alternatives = get_alternative_services($menu_item['id']);

        $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/menu_modal.php', [
            'menu_item' => $menu_item,
            'menu_items' => $menu_items,
            'classifications' => $classifications,
            'menu_id' => $menu_id,
            'category' => $category,
            'alternatives' => $alternatives,
        ]);
    }

    responseJson($json);
}
