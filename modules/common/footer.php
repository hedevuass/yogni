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
        <input type="radio" name="city" class="radio" id="city-{$city['id']}" data-id="{$city['id']}" $checked>
        <label for="city-{$city['id']}" class="l$key">{$city['name']}</label>
    EOF;
}


// Обрабатывает список категорий для popup меню
$services = get_services_for_menu();

foreach ($services as $key => $service) {
    // Если юрист или адвокат
    if ($key == 1 || $key == 2) {
        $curr_col = 0;
        $started_col = false; // Флаг для начала столбца

        // Заголовки, с которых начинаются столбцы
        $col_start_titles = [
            0 => "Административное право",
            1 => "Жилищное право",
            2 => "Международное право",
            3 => "Споры с призывной комиссией"
        ];

        $current_col_start = 0; // Индекс текущего заголовка для начала столбца

        // Создание первого столбца, который начинается с "Административное право"
        if (isset($col_start_titles[$current_col_start])) {
            $data['desctop_menu_lawyers'] .= '<div class="nav">';
            $curr_col++;
            $started_col = true;
            $current_col_start++;
        }

        foreach ($service[0] as $index => $parrent) {
            $single = !isset($service[$parrent['id']]) ? 'single' : '';
            $with_sub = !$single ? 'with_sub' : '';

            // Проверяем, нужно ли начать новый столбец
            if ($started_col && isset($col_start_titles[$current_col_start]) && $parrent['title'] == $col_start_titles[$current_col_start]) {
                // Закрываем текущий столбец и открываем новый, если заголовок совпадает
                $data['desctop_menu_lawyers'] .= "</div><div class='nav'>";
                $curr_col++;
                $current_col_start++;
            }

            // Добавляем элемент в текущий столбец
            $data['desctop_menu_lawyers'] .= <<<EOF
                <p class="$single"><a href="/yuristy-i-advokaty/{$parrent['slug']}">{$parrent['title']}</a></p>
            EOF;
            $data['mobile_menu_lawyers'] .= '<li class="' . $parrent["slug"] . '"><a href="/yuristy-i-advokaty/' . $parrent["slug"] . '">' . $parrent["title"] . '</a></li>';

            if (!$single) {
                $data['desctop_menu_lawyers'] .= "<ul>";

                $last_sub = '';
                foreach ($service[$parrent['id']] as $child) {
                    if (mb_stripos($child['title'], 'прочие') !== false) {
                        $last_sub = $child;
                    } else {
                        $data['desctop_menu_lawyers'] .= "<li><a href='/yuristy-i-advokaty/{$parrent["slug"]}/{$child['slug']}'>{$child['title']}</a></li>";
                    }
                }
                if (isset($last_sub) && $last_sub != '') {
                    $data['desctop_menu_lawyers'] .= "<li><a href='/yuristy-i-advokaty/{$parrent["slug"]}/{$last_sub['slug']}'>{$last_sub['title']}</a></li>";
                }

                $data['desctop_menu_lawyers'] .= "</ul>";
            }
        }

        if ($curr_col > 0) {
            $data['desctop_menu_lawyers'] .= "</div>";
        }
    }

    // Если экспертиза
    if ($key == 3) {
        $last_cat = '';

        foreach ($service[0] as $child) {

            if (mb_stripos($child['title'], 'прочие') !== false) {
                $last_cat = $child;
            } else {
                $data['menu_expertise'] .= "<li><a href='/experty/{$child['slug']}'>{$child['title']}</a></li>";
                $data['mobile_menu_expertise'] .= '<li class="' . $child["slug"] . '"><a href="/experty/' . $child["slug"] . '">' . $child["title"] . '</a></li>';
            }
        }
        if (isset($last_cat) && $last_cat != '') {
            $data['menu_expertise'] .= "<li><a href='/experty/{$last_cat['slug']}'>{$last_cat['title']}</a></li>";
            $data['mobile_menu_expertise'] .= "<li class='{$last_cat['slug']}'><a href='/experty/{$last_cat['slug']}'>{$last_cat['title']}</a></li>";
        }
    }
}


// Обрабатывает popup metro
if (isset($_SESSION['metro']) && $_SESSION['metro']) {
    $metro = get_metro_by_ids($_SESSION['metro']);
    if (count($metro) < 4) {
        $metro1 = get_extra_metro($_SESSION['metro'], 4 - count($metro));
        foreach ($metro1 as $station) {
            $metro[] = $station;
        }
    }
} else {
    $metro = get_metro_in_city($_SESSION['city']['id']);
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

$HTML_ = explode('<!--data:content-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:content-->{$data['content']}<!--data:content-->" . $HTML_[2];
