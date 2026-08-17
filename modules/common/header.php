<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/S.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Cases.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/CasesHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Gender.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/BaseInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/NamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianLanguage.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/Cases.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianCasesHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/FirstNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/MiddleNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/LastNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/GeographicalNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/NounDeclension.php';

use Morphos\Russian\GeographicalNamesInflection;

// Личный кабинет
if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] > 0) {
	$user = get_specialist($_SESSION['user']['id']);
	$name = $user['company'] ?? $user['name'] . " " . $user['lastname'];
    $data['user_links1'] = '<a href="/actions/account/logout.php" class="logout"><i></i>Выйти</a>';
    $data['user_links2'] = '<div class="header__profile-greeting">Привет, <a href="/cabinet" class="profile-link">'. $name .'</a></div>';
} else {
	unset($_SESSION['user']);
    $data['user_links1'] = <<<EOF
        <a class="login"><i></i>Войти</a>
    EOF;
    $data['user_links2'] = <<<EOF
    <button class="btn btn__orange reg">Создать аккаунт</button>
    EOF;
}

if (isset($_SESSION['metro']) && strpos($_SERVER['REQUEST_URI'], '/notariusy') !== 0) {
    unset($_SESSION['metro']);
}

$cityName = $_SESSION['city']['name'];

// Получаем в родительном падеже
$cityGenitive = GeographicalNamesInflection::getCase($cityName, 'родительный');
$cityPrepositional = GeographicalNamesInflection::getCase($cityName, 'предложный');
$_SESSION['city_genitive'] = $cityGenitive;
$_SESSION['city_prepositional'] = $cityPrepositional;

$slugs = ['yuristy-i-advokaty', 'yoga-studii', 'notariusy'];

foreach ($slugs as $slug) {
    $service = get_service_parent_catalog($slug); // вернёт ['title' => ..., 'icon' => ..., ...]
    if ($service) {
        $key = match ($slug) {
            'yuristy-i-advokaty' => 'menu1_lawyer',
            'yoga-studii'            => 'menu1_expert',
            'notariusy'          => 'menu1_notarius',
        };
        $data[$key] = htmlspecialchars($service['title']);
    }
}

// Социальные сети
$social_links = get_all_social();
$data['social_links_header'] = '<li class="flex social__wrapper">';
foreach ($social_links as $social) {
	if ($social['alt'] === 'Telegram') {
        continue;
    }
    $data['social_links_header'] .= '<a target="_blank" href="' . htmlspecialchars($social['link']) . '" class="' . htmlspecialchars($social['alt']) . '"></a>';
}
$data['social_links_header'] .= '</li>';

// нижнее меню для мобилки
// определяем активный раздел по ссылке
$home = $journal = $izbrannoe = $cabinet = '';
$page = strtok(explode('/', $_SERVER['REQUEST_URI'])[1], '?');
if ($page == "") {
    $home = ' active';
} else if ($page == "article") {
	$journal = ' active';
} else if ($page == "moy_yogoort") {
	$cabinet = ' active';
} else {
    $$page = ' active';
}
$cabinet_element = '';
if (isset($_SESSION['user'])) {
	$cabinet_element = '<a href="/cabinet" class="bottom-header__item'.$cabinet.'" id="cabinet-item"><span>Мой ЙОГУРТ</span></a>';
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
				<span>Журнал</span>
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
