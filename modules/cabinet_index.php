<?php

$data = [];

$user = get_specialist($_SESSION['user']['id']);

$data['index_user_info'] = user_info_block($user);

$blog_info = blog_info_block($user);
$blog_info_block = $blog_info['code'];
$status = $blog_info['status'];
$status_class = $blog_info['class'];

$data['cabinet_photo_mob'] .= mob_input_photo($user);

$photo = $user['photo'] ? '/uploads/avatars/' . $user['photo'] : '/assets/img/cabinet/no-photo.svg';
$photo_class = $user['photo'] ? '' : 'default';
$photo_edit_class = $user['photo'] ? '' : ' no-photo';

//выводим фото для компа
$data['index_photo_edit'] = '<a href="/cabinet/redaktirovat-informatsiyu" class="photo__edit ' . $photo_edit_class . '" style="background-image: url(' . $photo . ')">
</a>';

$data['upload_user_photo'] .= input_photo($user);

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

if ($user['company']) {
    $old_price = '11990';
    $actual_price = '5990';
} else {
    $old_price = '5990';
    $actual_price = '2990';
}

$data['index_mob_info'] .= '<div class="mob-menu__actual">
<div class="actual__name">
    <span class="bold orange">PRO</span><span class="bold">-ФОТО</span> со скидкой <span class="orange">50%</span>
</div>
<div class="actual__descr">
    Первый инструмент для продвижения страницы
</div>
<div class="actual__price">
    <span class="high-price">
        ' . $old_price . '
        <div class="cross-line"></div>
    </span>
    <span class="low-price">' . $actual_price . '</span>
    <span>руб.</span>
</div>
<div class="actual__postscr">
    *Первые 2 недели после регистрации
</div>

<a href="/cabinet/pro-photo" class="actual__link"></a>
</div>';
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
    $photo = $user['photo'] ? '/uploads/avatars/' . $user['photo'] : '/assets/img/cabinet/no-photo.svg';
    $photo_class = $user['photo'] ? '' : 'default';
    $phone = is_null($user['phone']) ? "Не указан" : format_phone($user['phone']);
    $foundation = is_null($user['foundation']) ? "Не указано" : $user['foundation'];

    $code = <<<EOF
    <div class="profile-photo {$photo_class}" style="background-image: url('{$photo}')"></div>
    <div class="profile-info"> 
        <a href="/cabinet/redaktirovat-informatsiyu" class="block__title">{$name}</a>
	    <div class="block__subtitle">{$user['classification']}</div>
        <div class="other-info__wrapper">
        <div class="info-pair">
            <span class="grey">Город:</span>
            <span>{$user['city']}</span>
        </div>
        <div class="info-pair">
            <span class="grey">{$foundation_name}:</span>
            <span>{$foundation}</span>
        </div>
        <div class="info-pair">
            <span class="grey">Телефон:</span>
            <span>{$phone}</span>
        </div>
        <div class="info-pair">
            <span class="grey">Почта:</span>
            <span>{$user['email']}</span>
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
    $rating = is_null($user['rating']) ? '0' : $user['rating'];
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
            <span class="grey">Просмотров:</span>
            <span>{$views}</span>
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
        if (!str_starts_with($page['alias'], 'redaktirovat-')) continue;
        if (!$user['company'] && $page['id'] == 17) continue;
        if (($user['classification_id'] == 4) && $page['id'] == 6) continue;
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
            <div class="photo__btn_wrapper">
                <input type="file" name="photo_file" id="photo_file">
                <label for="photo_file" class="photo__btn {$class}">
                    {$btn}
                </label>
                <a href="/cabinet/pro-photo" class="btn__white-orange">Заказать фотосъёмку</a>
            </div>
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
    <p>Стартовый набор для продвижения</p>
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
