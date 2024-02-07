<?php

$data = [];

$user = get_specialist($_SESSION['user']['id']);

$data['index_user_info'] = user_info_block($user);

$data['index_blog_info'] = blog_info_block($user);

$data['cabinet_photo_mob'] .= mob_input_photo($user);

$photo = $user['photo'] ? '/uploads/avatars/'.$user['photo'] : '/assets/img/cabinet/no-photo.svg';
$photo_class = $user['photo'] ? '' : 'default';
$photo_edit_class = $user['photo'] ? '' : ' no-photo';

//выводим фото для компа
$data['index_photo_edit'] = '<a href="/cabinet/redaktirovat-informatsiyu" class="photo__edit '.$photo_edit_class.'" style="background-image: url(' .$photo.')">
    <span class="pencil"></span>
</a>';
$name = $user['company'] ?? $user['name'] . " " . $user['lastname'];
//выводим фото и инфу для верха мобилки
$data['index_mob_info'] = '<div class="index-mob__info-block"><div class="photo-block profile-photo-mmenu">
<div class="photo '.$photo_class.'" style="background-image: url('.$photo.')"></div>
<div class="photo-btn"></div>
</div>
<div class="info-block">
<div class="name">' . $name . '</div>
<div class="classification">'.$user['classification'].'</div>
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
    <span class="bold orange">PRO</span><span class="bold">-ФОТО</span> со скидкой <span class="orange">57%</span>
</div>
<div class="actual__descr">
    Первый инструмент для продвижения страницы
</div>
<div class="actual__price">
    <span class="high-price">
        '.$old_price.'
        <div class="cross-line"></div>
    </span>
    <span class="low-price">' .$actual_price. '</span>
    <span>руб.</span>
</div>
<div class="actual__postscr">
    *Первые 2 недели после регистрации
</div>
<div class="actual__arrow"></div>
<div class="actual__photocamera"></div>
<a href="/cabinet/pro-photo" class="actual__link"></a>
</div>';
//выводим цену на фото
$data['index_photo_price'] = '<div class="high-price">
<span>'.$old_price.'</span>
<span class="small"> руб.</span>
<div class="cross-line"></div>
</div>
<div class="actual-price">
<span>'.$actual_price.'*</span>
<span class="small"> руб.</span>
</div>';

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
    $photo = $user['photo'] ? '/uploads/avatars/'.$user['photo'] : '/assets/img/cabinet/no-photo.svg';
    $photo_class = $user['photo'] ? '' : 'default';
    $phone = is_null($user['phone']) ? "Не указан" : format_phone($user['phone']);
    $foundation = is_null($user['foundation']) ? "Не указано" : $user['foundation'];
    
    $code = <<<EOF
    <div class="profile-photo {$photo_class}" style="background-image: url('{$photo}')"></div>
    <div class="profile-info"> 
        <a href="/cabinet/redaktirovat-informatsiyu" class="block__title">{$name}</a>
	    <div class="block__subtitle">{$user['classification']}</div>
        <div class="other-info__wrapper">
            <span class="grey">Город:</span>
            <span>{$user['city']}</span>
            <span class="grey">{$foundation_name}:</span>
            <span>{$foundation}</span>
            <span class="grey">Телефон:</span>
            <span>{$phone}</span>
            <span class="grey">Почта:</span>
            <span>{$user['email']}</span>
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
    $blog_id = get_specialist_blog($user['id'])['id'];
    $count = count(get_blog_articles($blog_id));
    $views_db = count_blog_views($blog_id)[0];
    $views = $views_db['SUM(views)'] ?? '0';
    $rating = is_null($user['rating']) ? '0' : $user['rating'];
    $code = <<<EOF
    <div class="other-info__wrapper">
        <span class="grey">Записей:</span>
        <span>{$count}</span>
        <span class="grey">Просмотров:</span>
        <span>{$views}</span>
        <span class="grey">Рейтинг:</span>
        <span>{$rating}</span>
    </div>
EOF;
    return $code;
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
        $code .= '<a class="menu-item" href="/cabinet/'.$page['alias'].'">'.$page['menu_title'].'</a>';
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
        $photo = '/uploads/avatars/'.$user['photo'];
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