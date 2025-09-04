<?php

$data = [];
if (isset($_SESSION['user'])) {
    $user = get_specialist($_SESSION['user']['id']);
    $name = $user['company'] ?? $user['name'] . " " . $user['lastname'];
    $data['menu_user_links'] = "<li><a href='/cabinet'>Личный кабинет</a></li>";
    $data['mmenu_user_links'] = '<div class="header__profile-greeting">
    Привет, <a href="/cabinet" class="profile-link">' . $name . '</a>
    <a href="/actions/account/logout.php" class="logout">
        <img src="/assets/img/logout-mobile.svg" alt="favorites">	
    </a></div>';
} else {
    $data['menu_user_links'] = <<<EOF
        <li><a class="login">Вход</a></li>
        <li><a class="reg">Создать аккаунт</a></li>
    EOF;
    $data['mmenu_user_links'] = <<<EOF
    <ul><li class="create-account"><a class="reg">Создать аккаунт</a></li>
    <li class="login-point"><a class="login">Войти</a></li></ul>
    EOF;
}

// обрабатывает год в футере
$data['year'] = date('Y', strtotime('NOW'));

// Обрабатывает список городов для popup
$cities = get_cities_for_popup();

if (!in_array($_SESSION['city'], $cities)) {
    array_unshift($cities, $_SESSION['city']);
}

foreach ($cities as $key => $city) {
    $checked = ($city['id'] == $_SESSION['city']['id']) ? 'checked' : '';

    $data['cities_list'] .= <<<EOF
        <input type="radio" name="city" class="radio radio-city" id="city-{$city['id']}" data-id="{$city['id']}" $checked>
        <label for="city-{$city['id']}" class="l$key">{$city['name']}</label>
    EOF;
}

$data['city_input'] .= <<<EOF
        <div class="field">
            <input type="hidden" name="city_id" value="365" readonly>
            <input type="text" name="city" placeholder="Город*" data-id="{$user['city_id']}" value="Москва" readonly>
        </div>
EOF;

// Социальные сети
$social_links = get_all_social();
$data['social_links'] = '<ul class="footer__social">';
foreach ($social_links as $social) {
    $data['social_links'] .= '<li><a href="' . htmlspecialchars($social['link']) . '" class="' . htmlspecialchars($social['alt']) . '"></a></li>';
}
$data['social_links'] .= '</ul>';

//Мобильные
$data['social_links_mobile'] = '<div class="flex social__wrapper">';
foreach ($social_links as $social) {
    $data['social_links_mobile'] .= '<a href="' . htmlspecialchars($social['link']) . '" class="' . htmlspecialchars($social['alt']) . '"></a>';
}
$data['social_links_mobile'] .= '</div>';

$slugs = ['yuristy-i-advokaty', 'experty', 'notariusy'];

$data['mobile_service_parent'] = '<div class="m2__section"><ul>';

foreach ($slugs as $slug) {
    $service = get_service_parent_catalog($slug);
    if ($service) {
        $class = [
            'yuristy-i-advokaty' => 'lawyer',
            'experty' => 'expert',
            'notariusy' => 'notarius'
        ][$slug];

        $style = ' style="background-image: url(assets/img/' . htmlspecialchars($service['icon']) . '); background-size: 16px 14px;"';
        $title = htmlspecialchars($service['title']);
        $url = '/' . $slug;

        $data['mobile_service_parent'] .= '<li class="' . $class . '"' . $style . '>';
        $data['mobile_service_parent'] .= '<a href="' . $url . '">' . $title . '</a></li>';
    }
}

$data['mobile_service_parent'] .= '</ul></div>';

$data['footer_mobile_menu'] = '<ul class="footer__mobile-menu">';

foreach ($slugs as $slug) {
    $service = get_service_parent_catalog($slug);
    if ($service) {
        $title = htmlspecialchars($service['title']);
        $data['footer_mobile_menu'] .= '<li><a href="/' . $slug . '">' . $title . '</a></li>';
    }
}

// Журнал добавляем вручную
$data['footer_mobile_menu'] .= '<li><a href="/journal">Журнал</a></li>';
$data['footer_mobile_menu'] .= '</ul>';

$items = [
    'first_lawyer' => [
        'slug' => 'yuristy-i-advokaty',
        'class' => 'find_lawyer',
        'text' => 'Все юристы и адвокаты'
    ],
    'first_expert' => [
        'slug' => 'experty',
        'class' => 'find_expert',
        'text' => 'Все эксперты'
    ],
    'first_notarius' => [
        'slug' => 'notariusy',
        'class' => 'find_notarius',
        'text' => 'Все нотариусы'
    ],
];

// Генерация трёх блоков
foreach ($items as $key => $item) {
    $service = get_service_parent_catalog($item['slug']);
    $icon = $service['icon'] ?? '';
    $title = $service['title'] ?? '';

    // Приведение к нижнему регистру, если title есть
    $text = $title ? 'Все ' . mb_strtolower($title) : $item['text'];

    $style = $icon
        ? ' style="background-image: url(assets/img/' . htmlspecialchars($icon) . '); background-size: 16px 14px;"'
        : '';

    $data[$key] = '<li class="' . $item['class'] . '"' . $style . '><a href="/' . $item['slug'] . '">' . htmlspecialchars($text) . '</a></li>';

    $HTML_ = explode("<!--data:$key-->", $HTML, 3);
    if (count($HTML_) === 3) {
        $HTML = $HTML_[0] . "<!--data:$key-->" . $data[$key] . "<!--data:$key-->" . $HTML_[2];
    }
}

foreach ($items as $key => $item) {
    $service = get_service_parent_catalog($item['slug']);
    $title = htmlspecialchars($service['title'] ?? '');

    // Переменная для заголовка (только title)
    $data['title_' . $key] = $title;

    // Вставляем в HTML для каждой новой переменной
    $HTML_ = explode("<!--data:title_$key-->", $HTML, 3);
    if (count($HTML_) === 3) {
        $HTML = $HTML_[0] . "<!--data:title_$key-->" . $data['title_' . $key] . "<!--data:title_$key-->" . $HTML_[2];
    }
}

// Дополнительные переменные с заголовками без "Все"
foreach ($items as $key => $item) {
    $service = get_service_parent_catalog($item['slug']);
    $title = htmlspecialchars($service['title'] ?? '');

    switch ($key) {
        case 'first_lawyer':
            $data['title_lawyer'] = $title;
            $HTML_ = explode("<!--data:title_lawyer-->", $HTML, 3);
            if (count($HTML_) === 3) {
                $HTML = $HTML_[0] . "<!--data:title_lawyer-->" . $data['title_lawyer'] . "<!--data:title_lawyer-->" . $HTML_[2];
            }
            break;

        case 'first_expert':
            $data['title_expert'] = $title;
            $HTML_ = explode("<!--data:title_expert-->", $HTML, 3);
            if (count($HTML_) === 3) {
                $HTML = $HTML_[0] . "<!--data:title_expert-->" . $data['title_expert'] . "<!--data:title_expert-->" . $HTML_[2];
            }
            break;

        case 'first_notarius':
            $data['title_notarius'] = $title;
            $HTML_ = explode("<!--data:title_notarius-->", $HTML, 3);
            if (count($HTML_) === 3) {
                $HTML = $HTML_[0] . "<!--data:title_notarius-->" . $data['title_notarius'] . "<!--data:title_notarius-->" . $HTML_[2];
            }
            break;
    }
}

$footer_titles = [
    'desctop_footer_lawyers_title' => 'yuristy-i-advokaty',
    'desctop_footer_experts_title' => 'experty',
    'desctop_footer_notarius_title' => 'notariusy',
];

foreach ($footer_titles as $data_key => $slug) {
    $service = get_service_parent_catalog($slug);
    if ($service) {
        $title = htmlspecialchars($service['title']);
        $data[$data_key] = $title;

        $HTML_ = explode("<!--data:$data_key-->", $HTML, 3);
        if (count($HTML_) === 3) {
            $HTML = $HTML_[0] . "<!--data:$data_key-->" . $data[$data_key] . "<!--data:$data_key-->" . $HTML_[2];
        }
    }
}

// Обрабатывает список категорий для popup меню
$services = get_services_for_menu();

foreach ($services as $key => $service) {
    // Если юрист или адвокат
    // Если юрист или адвокат
    if ($key == 1 || $key == 2) {
        // Обнуляем вывод
        $data['desctop_menu_lawyers'] = '';

        // Получаем общее число родительских элементов
        $total = count($service[0]);
        if ($total > 0) {
            // Определяем процент элементов для каждой колонки
            $col1_count = round($total * 0.25);
            $col2_count = round($total * 0.3);
            $col3_count = round($total * 0.25);
            // Четвёртую колонку берём «остатком»
            $col4_count = $total - ($col1_count + $col2_count + $col3_count);

            // Разбиваем массив на 4 части
            $col1 = array_slice($service[0], 0, $col1_count);
            $col2 = array_slice($service[0], $col1_count, $col2_count);
            $col3 = array_slice($service[0], $col1_count + $col2_count, $col3_count);
            $col4 = array_slice($service[0], $col1_count + $col2_count + $col3_count, $col4_count);

            // Собираем колонки в один массив
            $columns = [$col1, $col2, $col3, $col4];

            // Оборачиваем всё в общий контейнер (например)
            $data['desctop_menu_lawyers'] .= '<div class="desktop-menu-lawyers">';

            // Генерируем каждую колонку
            foreach ($columns as $colIndex => $colItems) {
                $data['desctop_menu_lawyers'] .= '<div class="nav">';

                foreach ($colItems as $parrent) {
                    // Определяем, есть ли у родителя потомки
                    $single = !isset($service[$parrent['id']]) ? 'single' : '';

                    // Родитель
                    $data['desctop_menu_lawyers'] .= <<<EOF
<p class="$single"><a href="/yuristy-i-advokaty/{$parrent['slug']}">{$parrent['title']}</a></p>
EOF;

                    // Мобильное меню (как у вас было)
                    $data['mobile_menu_lawyers'] .= '<li class="' . $parrent["slug"] . '"' .
                        (!empty($parrent['icon']) ? ' style="background-image: url(../assets/img/' . $parrent['icon'] . ');"' : '') . '>
                <a href="/yuristy-i-advokaty/' . $parrent["slug"] . '">' . $parrent["title"] . '</a></li>';

                    // Дочерние элементы
                    if (!$single) {
                        $data['desctop_menu_lawyers'] .= '<ul>';
                        $last_sub = '';
                        foreach ($service[$parrent['id']] as $child) {
                            if (mb_stripos($child['title'], 'прочие') !== false) {
                                $last_sub = $child;
                            } else {
                                $data['desctop_menu_lawyers'] .= "<li><a href='/yuristy-i-advokaty/{$parrent["slug"]}/{$child['slug']}'>{$child['title']}</a></li>";
                            }
                        }
                        if (!empty($last_sub)) {
                            $data['desctop_menu_lawyers'] .= "<li><a href='/yuristy-i-advokaty/{$parrent["slug"]}/{$last_sub['slug']}'>{$last_sub['title']}</a></li>";
                        }
                        $data['desctop_menu_lawyers'] .= '</ul>';
                    }
                }

                $data['desctop_menu_lawyers'] .= '</div>'; // Закрыли .nav
            }

            $data['desctop_menu_lawyers'] .= '</div>'; // Закрыли .desktop-menu-lawyers
        }
    }

    if ($key == 3) {
        $last_cat = '';

        foreach ($service[0] as $child) {
            $style = !empty($child['icon']) ? ' style="background-image: url(../assets/img/' . $child['icon'] . ');"' : '';

            if (mb_stripos($child['title'], 'прочие') !== false) {
                $last_cat = $child;
            } else {
                $data['menu_expertise'] .= "<li><a href='/experty/{$child['slug']}'>{$child['title']}</a></li>";
                $data['mobile_menu_expertise'] .= '<li class="' . $child["slug"] . '"' . $style . '><a href="/experty/' . $child["slug"] . '">' . $child["title"] . '</a></li>';
            }
        }

        if (isset($last_cat) && $last_cat != '') {
            $style = !empty($last_cat['icon']) ? ' style="background-image: url(../assets/img/' . $last_cat['icon'] . ');"' : '';

            $data['menu_expertise'] .= "<li><a href='/experty/{$last_cat['slug']}'>{$last_cat['title']}</a></li>";
            $data['mobile_menu_expertise'] .= "<li class='{$last_cat['slug']}'{$style}><a href='/experty/{$last_cat['slug']}'>{$last_cat['title']}</a></li>";
        }
    }
}

// Обрабатывает popup metro
if (isset($_SESSION['metro']) && $_SESSION['metro']) {
    // Получаем только выбранные метро из сессии
    $metro = get_metro_by_ids($_SESSION['metro']);
}

foreach ($metro as $value) {
    $checked = $value['checked'] ?: '';

    $data['metro_list'] .= '
        <input type="checkbox" class="checkbox" id="metro-' . $value['id'] . '" name="' . $value['id'] . '" data-id="' . $value['id'] . '" ' . $checked . '>
        <label for="metro-' . $value['id'] . '">' . $value['name'] . '</label>';
}
// Обрабатывает почту в попапе фидбэка
$data['feedback_email'] = '<a href="mailto:' . EMAIL_FEEDBACK . '" class="blue-link">' . EMAIL_FEEDBACK . '</a>';

//Выводит блок регистрации 

if (isset($_SESSION['user'])) {
    $footer_reg_btn = '<a href="/cabinet" class="btn btn__lk-footer">Перейти в личный кабинет</a>';
} else {
    $footer_reg_btn = '<button class="btn btn__reg-footer">Создать аккаунт</button>';
}

$data['footer_registration'] = '<div class="footer__reg-wrapper">
<div class="wrap">
    <div class="flex">
        <span>Вы юрист, адвокат, эксперт или у вас компания соответствующего профиля?</span>
        ' . $footer_reg_btn . '
    </div>
</div>
</div>';

$randId = 'id' . bin2hex(random_bytes(6));
$data['captcha'] = '
<div class="field captcha-field">
    <div class="captcha-row">
        <div class="captcha-left">
        <img id="captcha2" src="/libs/securimage/securimage_show.php?id=' . $randId . '" alt="CAPTCHA" aria-hidden="false" />
        <button type="button" id="captcha_refresh2" class="captcha-refresh" title="Обновить каптчу" aria-label="Обновить каптчу">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M21 12a9 9 0 10-3.2 6.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        </div>

        <div class="captcha-right">
        <label class="captcha-label" for="captcha_code">Подтвердите, что вы не робот</label>
        <input class="captcha-input" id="captcha_code2" name="captcha_code" type="text" placeholder="Введите код" autocomplete="off" />
        <input type="hidden" id="captcha_captcha_id2" name="captcha_id2" value="' . $randId . '" />
        </div>
    </div>
    <div class="captcha-error"></div>
</div>
';

$HTML_ = explode('<!--data:captcha-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:captcha-->{$data['captcha']}<!--data:captcha-->" . $HTML_[2];


// Обрабатывает мобильный фильтр
$data['city'] = $_SESSION['city']['name'];


// Подставляем ссылки пользователя
$HTML_ = explode('<!--data:menu_user_links-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:menu_user_links-->{$data['menu_user_links']}<!--data:menu_user_links-->" . $HTML_[2];

$HTML_ = explode('<!--data:mmenu_user_links-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:mmenu_user_links-->{$data['mmenu_user_links']}<!--data:mmenu_user_links-->" . $HTML_[2];

// Подставляем год
$HTML_ = explode('<!--data:year-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:year-->{$data['year']}<!--data:year-->" . $HTML_[2];

// Подставляем почту
$HTML_ = explode('<!--data:feedback_email-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:feedback_email-->{$data['feedback_email']}<!--data:feedback_email-->" . $HTML_[2];

// Подставляем список городов в popup
$HTML_ = explode('<!--data:cities_list-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:cities_list-->{$data['cities_list']}<!--data:cities_list-->" . $HTML_[2];

// Подставляем список юристов и адвокатов в desctop меню
$HTML_ = explode('<!--data:desctop_menu_lawyers-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:desctop_menu_lawyers-->{$data['desctop_menu_lawyers']}<!--data:desctop_menu_lawyers-->" . $HTML_[2];

// Подставляем список юристов и адвокатов в футер
$HTML_ = explode('<!--data:desctop_footer_lawyers-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:desctop_footer_lawyers-->{$data['desctop_menu_lawyers']}<!--data:desctop_footer_lawyers-->" . $HTML_[2];

// Подставляем блок регистрации в футер
$HTML_ = explode('<!--data:footer_registration-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:footer_registration-->{$data['footer_registration']}<!--data:footer_registration-->" . $HTML_[2];

// Подставляем список юристов и адвокатов в мобильное меню
$HTML_ = explode('<!--data:mobile_menu_lawyers-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:mobile_menu_lawyers-->{$data['mobile_menu_lawyers']}<!--data:mobile_menu_lawyers-->" . $HTML_[2];

$HTML_ = explode('<!--data:footer_mobile_menu-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:footer_mobile_menu-->{$data['footer_mobile_menu']}<!--data:footer_mobile_menu-->" . $HTML_[2];

// Подставляем список экспертиз в меню
$HTML = str_replace(
    '<!--data:menu_expertise--><!--data:menu_expertise-->',
    '<!--data:menu_expertise-->' . $data['menu_expertise'] . '<!--data:menu_expertise-->',
    $HTML
);

$HTML = str_replace(
    '<!--data:mobile_menu_expertise--><!--data:mobile_menu_expertise-->',
    '<!--data:mobile_menu_expertise-->' . $data['mobile_menu_expertise'] . '<!--data:mobile_menu_expertise-->',
    $HTML
);

// Подставляем список метро в popup
$HTML = str_replace(
    '<!--data:metro_list--><!--data:metro_list-->',
    '<!--data:metro_list-->' . $data['metro_list'] . '<!--data:metro_list-->',
    $HTML
);

// Подставляем выбранный город
$HTML = str_replace(
    '<!--data:city--><!--data:city-->',
    '<!--data:city-->' . $data['city'] . '<!--data:city-->',
    $HTML
);

$HTML = str_replace(
    '<!--data:city_input--><!--data:city_input-->',
    '<!--data:city_input-->' . $data['city_input'] . '<!--data:city_input-->',
    $HTML
);

$HTML_ = explode('<!--data:content-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:content-->{$data['content']}<!--data:content-->" . $HTML_[2];

$HTML_ = explode('<!--data:social_links-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:social_links-->{$data['social_links']}<!--data:social_links-->" . $HTML_[2];

$HTML_ = explode('<!--data:social_links_mobile-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:social_links_mobile-->{$data['social_links_mobile']}<!--data:social_links_mobile-->" . $HTML_[2];

$HTML_ = explode('<!--data:mobile_service_parent-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:mobile_service_parent-->{$data['mobile_service_parent']}<!--data:mobile_service_parent-->" . $HTML_[2];
