<?php

$data = [];

$user = get_specialist($_SESSION['user']['id']);

$data['index_user_info'] = user_info_block($user);

$blog_info = blog_info_block($user);
$blog_info_block = $blog_info['code'];
$status = $blog_info['status'];
$status_class = $blog_info['class'];

$data['cabinet_photo_mob'] .= mob_input_photo($user);

$photo = '';

if (!empty($user['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/thumb_' . $user['photo'])) {
    $photo = '/uploads/avatars/thumb_' . $user['photo'];
} elseif (!empty($user['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $user['photo'])) {
    $photo = '/uploads/avatars/' . $user['photo'];
} else {
    $photo = '/assets/img/cabinet/no-photo.svg';
}
$photo_class = $user['photo'] ? '' : 'default';
$photo_edit_class = $user['photo'] ? '' : ' no-photo';

//выводим фото для компа
/* $data['index_photo_edit'] = '<a href="/cabinet/redaktirovat-informatsiyu" class="photo__edit ' . $photo_edit_class . '" style="background-image: url(' . $photo . ')">
</a>'; */



$faq_page = get_faq_page_by_id(22);
$data['faq_page'] = '<a href="/cabinet/faq/' . $faq_page . '" class="pro-photo_block-background">';
$data['faq_page2'] = '<a href="/cabinet/faq/' . $faq_page . '" class="photo__faq-link">';

/**
 * @param array $user
 * @return string
 */
function input_photo(array $user)
{
    if ($user['photo']) {
        $class = 'refresh';
        $btn = '<i></i>Загрузить фото профиля';
    } else {
        $class = '';
        $btn = '<i></i>Загрузить фото профиля';
    }
    $code = <<<EOF
        <input type="file" name="photo_file" id="photo_file_obzor">
        <label for="photo_file_obzor" class="pro-photo__btn photo__btn {$class}">
            {$btn}
        </label>           
    EOF;
    return $code;
}

$name = $user['company'] ?? $user['name'] . " " . $user['lastname'];
//выводим фото и инфу для верха мобилки
$data['index_mob_info'] = '<div class="index-mob__info-block"><div class="photo-block profile-photo-mmenu">
<div class="photo ' . $photo_class . '" style="background-image: url(' . $photo . ')"></div>
<div class="photo-btn"></div>
</div>
<div class="info-block">
<div class="name">' . $name . '</div>
<div class="classification">' . $user['classification'] . '</div>
</div></div>';

$pro_photo2 = get_pro_photo_by_id(2);

if ($user['company']) {
    $old_price = strip_tags(html_entity_decode($pro_photo2['price_old']));
    $actual_price = strip_tags(html_entity_decode($pro_photo2['price']));
} else {
    $old_price = strip_tags(html_entity_decode(string: $pro_photo2['price_old']));
    $actual_price = strip_tags(html_entity_decode($pro_photo2['price']));
}

$pro_photo_records = get_pro_photo_records($user['id']);
$pro_photo_status = generate_pro_photo_status($pro_photo_records, $user['activated_at']);

// Берём флаг напрямую
if ($pro_photo_status['show_discount']) {
    $actual__name = '
        <div class="actual__name">
            <span class="bold orange">PRO</span><span class="bold">-ФОТО</span> со скидкой <span class="orange">50%</span>
        </div>';

    // Блок с выделением скидки
    $data['highlight_discount'] = '<span class="highlight">&nbsp;со скидкой!</span><div class="circle-pro-photo-text">-50%</div>';
    $data['postdescr_discount'] = '<div class="pro-photo_postscr pro-photo_postscr-abs">*В первые 2 недели после регистрации</div>';

    // Добавляем весь баннер
    $data['index_mob_info'] .= '<div class="mob-menu__actual">
    ' . $actual__name . '
    <div class="actual__descr">
        Первый инструмент для продвижения страницы
    </div>
    <div class="actual__price">
        <span class="high-price">
            ' . $old_price . '
            <div class="cross-line"></div>
        </span>
        <span class="low-price">' . $actual_price . '</span>
    </div>
    <div class="actual__postscr">
        *Первые 2 недели после регистрации
    </div>

    <a href="/cabinet/pro-photo" class="actual__link"></a>
    </div>';
} else {
    // Скрываем всё — баннер не добавляется вообще
    $data['highlight_discount'] = '';
    $data['postdescr_discount'] = '';
}

//выводим цену на фото

$data['index_mob_edit_menu'] = mob_edit_menu($user);

$HTML = html_explode($data, $HTML);


/**
 * @param array $user
 * @return string
 */
function user_info_block(array $user)
{
    if ($user['company']) {
        $name = $user['company'];
        $foundation_name = 'Год основания';
    } else {
        $name = $user['name'] . " " . $user['lastname'];
        $foundation_name = 'Опыт работы (лет)';
    }
    $otherData = json_decode($user['other'], true);
    if (json_last_error() === JSON_ERROR_NONE && isset($otherData['email'])) {
        $email = $otherData['email'];
    }
    $photo = ($user['photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/thumb_' . $user['photo'])) 
    ? '/uploads/avatars/thumb_' . $user['photo'] 
    : (($user['photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $user['photo'])) 
        ? '/uploads/avatars/' . $user['photo'] 
        : '/assets/img/cabinet/no-photo.svg');
    $photo_class = $user['photo'] ? '' : 'default';
    $phone = is_null($user['phone']) ? "Не указан" : format_phone($user['phone']);
    $foundation = is_null($user['foundation']) ? "Не указано" : $user['foundation'];
    $name = cut_text($name, 27);

    $code = <<<EOF
    <div class="profile-photo {$photo_class}" style="background-image: url('{$photo}')"></div>
    <div class="profile-info"> 
        <div href="/cabinet/redaktirovat-informatsiyu" class="block__title">{$name}</div>
	    <div class="block__subtitle">{$user['classification']}</div>
        <div class="other-info__wrapper">
        <div class="info-pair">
            <span class="grey">Телефон для звонков:</span>
            <span>{$phone}</span>
        </div>
        <div class="info-pair">
            <span class="grey">Публичный email:</span>
            <span>{$email}</span>
        </div>
    </div>
    </div>
EOF;
    return $code;
}

/**
 * @param array $user
 * @return string
 */
function blog_info_block(array $user)
{
    $blog_id = get_specialist_blog($user['id'])['id'] ?? null;
    $count = 0;
    $views = 0;
    $rating = is_null($user['rating']) ? 0 : round($user['rating'], 1);
    $status = 'Не ведется';
    $status_class = 'red-dot';

    if ($blog_id !== null || $count < 0) {
        $count = count_blog_articles($blog_id);
        $views_db = count_blog_views($blog_id)[0];
        $views = $views_db['SUM(views)'] ?? '0';

        if ($count > 0) {
            $status = 'Ведется';
            $status_class = 'blue-dot';
        }
    }

    $code = <<<EOF
    <div class="other-info__wrapper">
        <div class="info-item">
            <span class="grey">Записей:</span>
            <span>{$count}</span>
        </div>
        <div class="info-item">
            <span class="grey">Рейтинг:</span>
            <span>{$rating}</span>
        </div>
    </div>
EOF;


    return ['code' => $code, 'status' => $status, 'class' => $status_class];
}


/**
 * @param array $user
 * @return string
 */
function mob_edit_menu(array $user)
{
    //получаем страницы меню
    $array = get_cabinet_top_menu();
    $code = '';
    foreach ($array as $page) {
        if (!str_starts_with($page['alias'], 'redaktirovat-'))
            continue;
        if (!$user['company'] && $page['id'] == 17)
            continue;
        if (($user['classification_id'] == 4) && $page['id'] == 6)
            continue;
        $code .= '<a class="menu-item" href="/cabinet/' . $page['alias'] . '">' . $page['menu_title'] . '</a>';
    }
    return $code;
}


/**
 * @param array $user
 * @return string
 */
function mob_input_photo(array $user)
{
    if ($user['photo']) {
        $photo = '/uploads/avatars/' . $user['photo'];
        $class = 'refresh';
        $btn = '<i></i>Изменить';
    } else {
        $photo = '/assets/img/cabinet/no-photo.svg';
        $class = '';
        $btn = '<i></i>Загрузить';
    }
    $code = <<<EOF
        <div class="photo__wrapper">
            <div class="photo__image {$class}" style="background-image: url('{$photo}')"></div>
                <input type="file" name="photo_file" id="photo_file">
                <label for="photo_file" class="photo__btn {$class}">
                    {$btn}
                </label>
        </div>
    EOF;
    return $code;
}
//Тарифы
$tariffs = [
    'basic' => get_tariff_data(1),
    'pro' => get_tariff_data(2),
    'expert' => get_tariff_data(3)
];

$specialist = get_specialist($_SESSION['user']['id']);
$connected_tariffs = get_connected_tariffs($specialist['id']);

$connected_tariffs_map = [];
$connected_tariffs_id = [];
$connected_tariffs_duration = [];
foreach ($connected_tariffs as $tariff) {
    if (is_array($tariff)) {
        $connected_tariffs_map[$tariff['tariff_id']] = date('d.m.y', strtotime($tariff['end_date']));
        $connected_tariffs_id[$tariff['tariff_id']] = $tariff['tariff_id'];
        $connected_tariffs_duration[$tariff['tariff_id']] = $tariff['duration'];
    }
}

$tariff_status = generate_tariff_status($tariffs, $connected_tariffs_map);

if (isset($connected_tariffs_map[1])) {
    $discount = 10;
} elseif (isset($connected_tariffs_map[2])) {
    $discount = 15;
} elseif (isset($connected_tariffs_map[3])) {
    $discount = 20;
}

$discount_html = '';
if ($discount > 0) {
    $discount_html = '<div class="circle-pro-photo">-' . $discount . '%</div>';
}


$connected_options = get_connected_options($user['id']);

$connected_options_map = [];
foreach ($connected_options as $option) {
    $connected_options_map[$option['option_id']] = $option['end_date'];
}

$connected_promo = get_connected_promo($user['id']);

$connected_promo_map = [];
foreach ($connected_promo as $promo) {
    if (is_array($promo)) {
        $promo_details = get_promo_details($promo['promo_id']);

        $connected_promo_map[] = [
            'title' => $promo_details['title']
        ];
    }
}

$promo_status_data = generate_promo_status($connected_promo_map);
$tariff_status = generate_tariff_status($tariffs, $connected_tariffs_map);
$option_status_data = generate_option_status($connected_options_map, $connected_tariffs_map);

$top_cards = '
<a href="/cabinet/page_promo?target=tariff-href" data-scroll-target="#tariff-href" class="card">
    <div class="card-topline">
        <h4>Тарифы <span class="status-dot ' . $tariff_status['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-tariff.svg" alt="Тарифы">
    </div>
    <p>Стартовый набор для продвижения</p>
    <div class="card-status">
        <span>' . $tariff_status['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</a>
<a href="/cabinet/page_promo" data-scroll-target="#promo-href" class="card">
      <div class="card-topline"><h4>Промо <span class="status-dot ' . $promo_status_data['class'] . '"></span></h4>
       ' . $discount_html . '
       <img src="../assets/img/cabinet/card-promo.svg" alt=""></div>
        <p>Продвижение карточки</p>
        <div class="card-status">
            <span>' . $promo_status_data['status'] . '</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
    </a>
<a href="/cabinet/page_promo" data-scroll-target="#option-href" class="card">
    <div class="card-topline">
        <h4>Опции <span class="status-dot ' . $option_status_data['class'] . '"></span></h4>
        ' . $discount_html . '
        <img src="../assets/img/cabinet/card-options.svg" alt="Опции">
    </div>
    <p>Базовые настройки</p>
    <div class="card-status">
        <span>' . $option_status_data['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</a>
<a href="/cabinet/moy-blog" class="card">
      <div class="card-topline"><h4>Мой блог <span class="status-dot ' . $status_class . '"></span></h4><img src="../assets/img/cabinet/card-blog.svg" alt=""></div>
        ' . $blog_info_block . '
        <div class="card-status">
            <span>' . $status . '</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
    </a>';
$data['top_cards'] = $top_cards;

$top_cards_mobile = '
<a href="/cabinet/page_promo?target=tariff-href" class="card">
    <div class="card-topline">
        <h4>Тарифы <span class="status-dot ' . $tariff_status['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-tariff.svg" alt="Тарифы">
    </div>
    <p>Стартовый набор <br>для продвижения</p>
    <div class="card-status">
        <span>' . $tariff_status['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</a>
<a href="/cabinet/page_promo?target=promo-section" class="card">
      <div class="card-topline"><h4>Промо <span class="status-dot ' . $promo_status_data['class'] . '"></span></h4>
       ' . $discount_html . '
       <img src="../assets/img/cabinet/card-promo.svg" alt=""></div>
        <p>Продвижение карточки</p>
        <div class="card-status">
            <span>' . $promo_status_data['status'] . '</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
    </a>
<a href="/cabinet/page_promo?target=option-href" class="card">
    <div class="card-topline">
        <h4>Опции <span class="status-dot ' . $option_status_data['class'] . '"></span></h4>
        ' . $discount_html . '
        <img src="../assets/img/cabinet/card-options.svg" alt="Опции">
    </div>
    <p>Базовые настройки</p>
    <div class="card-status">
        <span>' . $option_status_data['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</a>
<a href="/cabinet/moy-blog" class="card">
      <div class="card-topline"><h4>Мой блог <span class="status-dot ' . $status_class . '"></span></h4><img src="../assets/img/cabinet/card-blog.svg" alt=""></div>
        ' . $blog_info_block . '
        <div class="card-status">
            <span>' . $status . '</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
</a>';
$data['top_cards_mobile'] = $top_cards_mobile;
$HTML = html_explode($data, $HTML);
