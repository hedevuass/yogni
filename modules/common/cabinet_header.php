<?php

// Если незалогинен, редирект на страницу "мой ЮРФАК"
if (!isset($_SESSION['user'])) responseRedirect('/moy_urfaq');
$requestion = explode('/', $_SERVER['REQUEST_URI'])[2];
if ($requestion) {
	$alias = (strtok($requestion, '?')) ?: '';
} else {
	$alias = '';
}

$page = get_cabinet_page($alias);

// Подставляем имя пользователя и аватарку
$user = get_specialist($_SESSION['user']['id']);

// Если специалист пытается попасть в раздел "Сотрудники", то редиректим на главную
if (!$user['company'] && $page['id'] == 17) responseRedirect('/cabinet');
// Если нотариус пытается попасть в раздел "Направления", то редиректим на главную
if (($user['classification_id'] == 4) && $menu_page['id'] == 6) responseRedirect('/cabinet');
$user['photo'] = $user['photo'] ? '/uploads/avatars/' . $user['photo'] : '/assets/img/cabinet/gray-circle.svg';

$data['user_header'] = <<<EOF
	<div class="user-text">
	Привет, <span>{$user['name']}</span> 
	<a href="/specialist/id{$user['id']}">Перейти на страницу</a>
	</div>
	<i><img src="{$user['photo']}"></i>
EOF;

// Меню для страниц редактирования личной информации
$menu = top_menu($page, $user);
$data['top_menu'] = $menu['desctop'];

// Левое меню
$left_menu = left_menu($page['alias'], $user);
$data['left_menu'] = $left_menu['desctop'];
$data['left_menu_mobile'] = $left_menu['mobile'];

if ($page['title'] && $page['description'] && $page['keywords']) {
	$HTML = html_replace([
		'title' 		=> $page['title'],
		'description' 	=> $page['description'],
		'keywords' 		=> $page['keywords']
	], $HTML);
}

$HTML = html_explode($data, $HTML);

$data = [
	'user' => $user
];


/**
 * @param string $page
 * @param array $user
 * @return array
 */
function top_menu(array $page, array $user): array
{

	$code = [
		'desctop' 	=> '',
		'mobile' 	=> ''
	];
	if (str_starts_with($page['alias'], 'redaktirovat-')) {
		//получаем страницы меню
		$array = get_cabinet_top_menu();
		$menu_class = '';
	} else if ($page['alias'] == 'moy-blog' || $page['alias'] == 'sozdat-zapis'){
		//получаем страницы меню блогов
		$array = get_cabinet_blog_top_menu();
		$menu_class = 'blog-menu';
	} else return $code;
    $code['desctop'] = <<<EOF
<div class="content-wrap">
    <nav class="top-menu">
		<ul class="{$menu_class}">
EOF;
	$iteration = 8;
	foreach ($array as $menu_page) {
		//не выводим раздел "Сотрудники" для специалистов (НЕ компаний)
		if (!$user['company'] && $menu_page['id'] == 17) continue;
		if (($user['classification_id'] == 4) && $menu_page['id'] == 6) continue;

		$class = $menu_page['id'] == $page['id'] ? 'active' : '';
        $code['desctop'] .= "<li><a href='/cabinet/{$menu_page['alias']}' class='{$class}'>{$menu_page['menu_title']}</a></li>";
		$iteration++;
	}
	$href_mobile = $page['id'] == 14 ? 'moy-blog' : '';
    $code['desctop'] .= <<<EOF
		</ul>
	</nav>
	<div class="heading__mobile"><a href="/cabinet/{$href_mobile}"></a>{$page['menu_title']}</div>
</div>
EOF;

	return $code;
}

/**
 * @param string $page
 * @param array $user
 * @return array
 */
function left_menu(string $page, array $user): array
{
	$code = [
		'desctop' 	=> '',
		'mobile' 	=> ''
	];

	$menu_pages = get_cabinet_menu();

	$code['desctop'] = <<<EOF
		<nav class="left-menu">
			<div class="logo__wrapper">
				<a class="logo" href="/"><span class="orange">ЮР</span>ФАК</a>
				<a class="site-link" href="/"><i></i>На сайт</a>
			</div>
			<ul>
	EOF;

	$name = $user['name'];

	if ($user['company']) {
		$old_price = '11990';
		$actual_price = '5990';
	} else {
		$old_price = '5990';
		$actual_price = '2990';
	}

	$code['mobile'] = '
	<div class="mob-nav2">
		<div class="greeting__wrapper">
			<div class="profile-greeting">
    			Привет, <span>'.$name.'</span>
    			<a href="/actions/account/logout.php" class="logout">
        			<img src="/assets/img/logout-mobile-active.svg" alt="favorites">
    			</a>
			</div>
			<a href="/specialist/id'.$user['id'].'">Перейти на страницу</a>
		</div>
		<div>
			<div class="name">Актуальное</div>
			<div class="mob-menu__actual">
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
			</div>
		</div>
		<div>
			<ul class="mob-menu__nav">
				<li class="faq"><a href="/cabinet/faq">FAQ</a></li>
				<li class="support"><a href="/cabinet/podderzhka">Поддержка</a></li>
			</ul>
		</div>
		<div>
			<div class="mob-menu__social">
				<a href="#" class="youtube"></a>
				<a href="#" class="telegram"></a>
				<a href="#" class="vk"></a>
				<a href="#" class="yandex"></a>
			</div>
		</div>';

	$iteration = 1;

	foreach ($menu_pages as $menu_page) {
		$class = $menu_page['alias'] == $page ? 'active' : '';

		if ((str_starts_with($page, 'redaktirovat-') && $menu_page['id'] == 3) || ($menu_page['id'] == 1 && $page==='')) $class = 'active';

		$span = '';
		if ($menu_page['active'] == 0) {
			if ($menu_page['id'] == 9){
				$class = 'disabled-stat';
				$span = '<span>Собирается</span>';
			} else if ($menu_page['id'] == 10) {
				$class = 'disabled-promo';
				$span = '<span>Скоро</span>';
			}
		}

		$code['desctop'] .= "<li><a href='/cabinet/{$menu_page['alias']}' class='cabinet-menu-link{$iteration} {$class}'>{$menu_page['title']}</a>{$span}</li>";

		$iteration++;
	}

	$code['desctop'] .= <<<EOF
			</ul>
	EOF;

	if ($page != 'cabinet_blog') {
		$code['desctop'] .= <<<EOF
			<div class="bubble-card blog">
			<div class="name large">
				БЛОГ
			</div>
			<p class="small">Делитесь знаниями и продвигайте свой профиль</p>
			<a class="bubble-card__blog-btn"></a>
			<a class="bubble-card__bg" href="/cabinet/moy-blog"></a>
		</div>
	EOF;
	}

	if ($page != 'cabinet_faq') {
		$code['desctop'] .= <<<EOF
			<div class="bubble-card faq">
			<div class="name">
				FAQ
			</div>
			<p>Мы собрали ответы на самые частые вопросы</p>
			<a class="bubble-card__btn">Перейти</a>
			<a class="bubble-card__bg" href="/cabinet/faq"></a>
		</div>
	EOF;
	}

	if ($page != 'cabinet_support') {
		$code['desctop'] .= <<<EOF
			<div class="bubble-card support">
			<a class="bubble-card__btn">Поддержка</a>
			<a class="bubble-card__bg" href="/cabinet/podderzhka"></a>
		</div>
	EOF;
	}

	$code['desctop'] .= <<<EOF
		</nav>
	EOF;

	$code['mobile'] .= <<<EOF
		</div>
	EOF;

	return $code;
}