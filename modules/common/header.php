<?php

// Личный кабинет
if (isset($_SESSION['user'])) {
    $data['user_links1'] = '<a href="/actions/account/logout.php" class="logout"><i></i>Выйти</a>';
    $data['user_links2'] = '<div class="header__profile-greeting">Привет, <a href="/cabinet" class="profile-link">'.$_SESSION['user']['name'].'</a></div>';
} else {
    $data['user_links1'] = <<<EOF
        <a class="login"><i></i>Войти</a>
    EOF;
    $data['user_links2'] = <<<EOF
    <button class="btn btn__orange reg">Создать аккаунт</button>
    EOF;
}

// нижнее меню для мобилки
// определяем активный раздел по ссылке
$home = $journal = $izbrannoe = $cabinet = '';
$page = strtok(explode('/', $_SERVER['REQUEST_URI'])[1], '?');
if ($page == "") {
    $home = ' active';
} else if ($page == "article") {
	$journal = ' active';
} else if ($page == "moy_urfaq") {
	$cabinet = ' active';
} else {
    $$page = ' active';
}
$cabinet_element = '';
if (isset($_SESSION['user'])) {
	$cabinet_element = '<a href="/cabinet" class="bottom-header__item'.$cabinet.'" id="cabinet-item"><span>Мой ЮРФАК</span></a>';
}
// вывод мобильного меню
$data['mobile_bottom_header'] = '
	<header class="mobile-bottom-header">
		<div class="flex">
			<a href="/" class="bottom-header__item'.$home.'" id="home-item">
				<span>Главная</span>
			</a>
			<div class="bottom-header__item" id="cat-item">
				<span>Все разделы</span>
			</div>
			<a href="/journal" class="bottom-header__item'.$journal.'" id="blog-item">
				<span>Блоги</span>
			</a>
			<a href="/izbrannoe" class="bottom-header__item'.$izbrannoe.'" id="favorite-item">
				<span>Избранное</span>
			</a>
			'.$cabinet_element.'
		</div>
	</header>';

// получаем все категории для кругляшей блока поиска
$categories = get_blog_filter_themes();

$data['search_categories'] = '';
foreach ($categories as $category) {
	if ($category['classification'] == 3) {
		continue;
	}
	if (mb_stripos($category['title'], 'прочие') !== false) {
		//раздел "прочие" в конец
		$last_category = $category;
	} else {
		$data['search_categories'] .= '<a href="/yuristy-i-advokaty/'.$category['slug'].'" class="category">'.$category['title'].'</a>';
	}
}
//раздел "прочие"
if (isset($last_category) && $last_category != '') {
	$data['search_categories'] .= '<a href="/yuristy-i-advokaty/'.$last_category['slug'].'" class="category">'.$last_category['title'].'</a>';
}

$HTML = html_explode($data, $HTML);