<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $menu_item = get_menu_item((int)$id);
    $classifications = get_filtered_classifications();
    $menu_items = get_menu_items();
    $menu_id = $menu_item['id'];
    $menu_item['img'] = $menu_item['img'] ? '/assets/img/' . $menu_item['img'] : '/assets/img/cabinet/gray-circle.svg';
    $menu_item['title'] = $menu_item['title'];
    $menu_item['slug'] = $menu_item['slug'];

    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/menu_modal.php', [
        'menu_item' => $menu_item,
        'menu_items' => $menu_items,
        'classifications' => $classifications,
        'menu_id' => $menu_id,
    ]);

    responseJson($json);
}