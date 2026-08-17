<?php

return [
    ADMIN_HREF => [
        'module' => 'index',
        'title'  => 'Общие настройки',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'index' => [
        'module' => 'index',
        'title'  => 'Общие настройки',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'menu' => [
        'module' => 'menu',
        'title'  => 'Меню',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'equipment' => [
        'module' => 'equipment',
        'title'  => 'Фильтры',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'services' => [
        'module' => 'services',
        'title'  => 'Полезные разделы',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'faq' => [
        'module' => 'faq',
        'title'  => 'FAQ',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'specialists' => [
        'module' => 'specialists',
        'title'  => 'Специалисты',
        'roles'  => ['superadmin', 'admin'],
    ],
    ADMIN_HREF . 'journal' => [
        'module' => 'journal',
        'title'  => 'Журнал',
        'roles'  => ['superadmin', 'admin', 'editor'],
    ],
    ADMIN_HREF . 'posts' => [
        'module' => 'posts',
        'title'  => 'Блоги',
        'badge'  => true,
        'roles'  => ['superadmin', 'admin', 'editor'],
    ],
    ADMIN_HREF . 'photo' => [
        'module' => 'photo',
        'title'  => 'Фотосъемка',
        'roles'  => ['superadmin', 'admin'],
    ],
    ADMIN_HREF . 'tariffs' => [
        'module' => 'tariffs',
        'title'  => 'Продвижение',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'banners' => [
        'module' => 'banners',
        'title'  => 'Баннеры',
        'roles'  => ['superadmin'],
    ],
    ADMIN_HREF . 'requests' => [
        'module' => 'requests',
        'title'  => 'Заявки',
        'roles'  => ['superadmin'],
    ],
];