<?php

// Если незалогинен, редирект на страницу "мой ЮРФАК"
// Проверка сессии и разных сценариев
$user = null;

if (isset($_GET['successfullconfirmation'])) {
	// Если нет сессии — редирект
	if (!isset($_SESSION['user']['id'])) {
		responseRedirect('/moy_yogoort');
		exit;
	}
	$user = get_specialist($_SESSION['user']['id']);
	if (!$user) {
		unset($_SESSION['user']);
		responseRedirect('/moy_yogoort');
		exit;
	}
} elseif (!isset($_SESSION['user']['id'])) {
	// Сценарий confirmation / recovery
	if (isset($_GET['confirmation']) || isset($_GET['recovery'])) {
		// Для confirmation используем старую проверку hash
		if (isset($_GET['user_id'], $_GET['hash'])) {
			$user_id = intval(clean_elem_form($_GET['user_id']));
			$hash = clean_elem_form($_GET['hash']);

			if (!check_hash($hash, $user_id)) {
				unset($_SESSION['user']);
				responseRedirect('/moy_yogoort');
				exit;
			}

			$user = get_specialist($user_id);
			if (!$user) {
				unset($_SESSION['user']);
				if (isset($_GET['recovery'])) {
					// Если это recovery — редирект на action
					responseRedirect('/action?recovery');
				} else {
					// Иначе — стандартный редирект
					responseRedirect('/moy_yogoort');
				}
				exit;
			}

			// Если это recovery — проверяем токен
			if (isset($_GET['recovery'])) {
				$token = clean_elem_form($_GET['token'] ?? '');
				if ($user['recovery_token'] !== $token || empty($token)) {
					// Токен не валиден или уже использован
					responseRedirect('/action?recovery');
					exit;
				}

				// Восстанавливаем пользователя
				recover_user($user['id']);
			}

			// Создаём сессию
			session_start();
			$_SESSION['user'] = [
				'id' => $user['id'],
				'name' => $user['name']
			];
		} else {
			// Недостаточно данных в URL
			responseRedirect('/moy_yogoort');
			exit;
		}
	} else {
		// Параметры confirmation/recovery не установлены
		responseRedirect('/moy_yogoort');
		exit;
	}
} else {
	// Пользователь залогинен
	$user = get_specialist($_SESSION['user']['id']);
	if (!$user) {
		unset($_SESSION['user']);
		responseRedirect('/moy_yogoort');
		exit;
	}
}

$data['user_info_idea'] = '<input type="hidden" name="user_email" value="' . $user['email'] . '"><input type="hidden" name="user_name" value="' . $user['name'] . ' ' . $user['lastname'] . '">';

$requestion = explode('/', $_SERVER['REQUEST_URI'])[2];
if ($requestion) {
	$alias = (strtok($requestion, '?')) ?: '';
} else {
	$alias = '';
}

$page = get_cabinet_page($alias);

// Если специалист пытается попасть в раздел "Сотрудники", то редиректим на главную
if (!$user['company'] && $page['id'] == 17) responseRedirect('/cabinet');
// Если нотариус пытается попасть в раздел "Направления", то редиректим на главную
if (($user['classification_id'] == 4) && $menu_page['id'] == 6) responseRedirect('/cabinet');

$hasPhoto = !empty($user['photo']);

// Проверяем миниатюру, потом оригинал, потом дефолт
if ($hasPhoto && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/thumb_' . $user['photo'])) {
	$user['photo'] = '/uploads/avatars/thumb_' . $user['photo'];
} elseif ($hasPhoto && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $user['photo'])) {
	$user['photo'] = '/uploads/avatars/' . $user['photo'];
} else {
	$user['photo'] = '/assets/img/cabinet/no-photo.svg';
	$hasPhoto = false; // обновляем флаг
}

$user['class'] = $hasPhoto ? 'filled' : '';
$userNameDisplay = ($user['company'] ? " {$user['company']}" : " {$user['name']}");

$studio_slug = !empty($user['slug']) ? $user['slug'] : 'id' . $user['id'];

$data['user_header'] = <<<EOF
<i><img class="{$user['class']}" src="{$user['photo']}"></i><img class="arrow-down" src="/assets/img/cabinet/arrow-down.svg">
<div class="dropdown-content">
    <div class="img-user-dropdown">
        <i><img class="{$user['class']}" src="{$user['photo']}"></i>
        <div class="header-column">
            <p class="user-name">$userNameDisplay</p>
            <a href="/redaktirovat-informatsiyu">Редактировать страницу</a>
        </div>
    </div>
    <a href="/studio/{$studio_slug}"><img src="/assets/img/cabinet/cabinet-dropdown.svg"> Перейти на страницу</a>
    <a href="../faq"><img src="/assets/img/cabinet/faq-dropdown.svg"> Частые вопросы</a>
    <a href="/actions/account/logout.php"><img src="/assets/img/cabinet/exit-dropdown.svg"> Выйти</a>
</div>
EOF;

// Меню для страниц редактирования личной информации
$menu = top_menu($page, $user);
$data['top_menu'] = $menu;

// Левое меню
$left_menu = left_menu($page['alias'], $user);
$data['left_menu'] = $left_menu['desctop'];
$data['left_menu_mobile'] = $left_menu['mobile'];

if ($page['title'] && $page['description'] && $page['keywords']) {
	if ($page['title'] !== "Обзор аккаунта") {
		$page['title'] = "Обзор аккаунта <img src='/assets/img/cabinet/obzor-arrow.svg'> <span class='grey'>{$page['title']}</span>";
		$page['url_title'] = "Обзор аккаунта";
	}
	$HTML = html_replace([
		'title' 		=> $page['title'],
		'url_title' 		=> $page['url_title'],
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
 * @return string
 */
function top_menu(array $page, array $user): string
{

	$code = '';
	if (str_starts_with($page['alias'], 'redaktirovat-')) {
		//получаем страницы меню
		$array = get_cabinet_top_menu();
		$menu_class = '';
	} else if ($page['alias'] == 'moy-blog' || $page['alias'] == 'sozdat-zapis' || $page['alias'] == 'prosmotr-zapisi') {
		//получаем страницы меню блогов
		$array = get_cabinet_blog_top_menu();
		$menu_class = 'blog-menu';
	} else return $code;
	$code = <<<EOF
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
		$code .= "<li><a href='/cabinet/{$menu_page['alias']}' class='{$class}'>{$menu_page['menu_title']}</a></li>";
		$iteration++;
	}
	$href_mobile = ($page['id'] == 14 || $page['id'] == 20) ? 'moy-blog' : '';
	$code .= <<<EOF
		</ul>
	</nav>
	EOF;
	$code .= <<<EOF
	<div style="margin: 0px;" class="heading__mobile"><a href="/cabinet/{$href_mobile}"></a>{$page['menu_mob_title']}</div>
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
		<div class="left-menu-back"></div>
		<nav class="left-menu">
			<div class="logo__wrapper">
				<a class="logo" href="/"><img class="logo-img" src="/assets/img/logo/yoga_logo.svg"><span>ЙОГУРТ</span></a>
			</div>
			<ul>
	EOF;

	$name = $user['company'] ?? $user['name'] . " " . $user['lastname'];

	if ($user['company']) {
		$old_price = '11990';
		$actual_price = '5990';
	} else {
		$old_price = '5990';
		$actual_price = '2990';
	}

	$specialist = get_specialist($_SESSION['user']['id']);
	$studio_slug = !empty($specialist['slug']) ? $specialist['slug'] : 'id' . $specialist['id'];

	//Тарифы
	$tariffs = [
		'basic' => get_tariff_data(1),
		'pro' => get_tariff_data(2),
		'expert' => get_tariff_data(3)
	];

	$connected_tariffs = get_connected_tariffs($specialist['id']);

	$connected_tariffs_map = [];
	foreach ($connected_tariffs as $tariff) {
		if (is_array($tariff)) {
			$connected_tariffs_map[$tariff['tariff_id']] = date('d.m.y', strtotime($tariff['end_date']));
		}
	}


	// Промо 

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

	function generate_promo_status($connected_promo_map)
	{
		$count = count($connected_promo_map); // Считаем количество подключенных промо

		if ($count > 0) {
			$status = 'Подключено (' . $count . ')';
			$status_class = 'blue-dot'; // Класс для синего кружка
		} else {
			$status = 'Не подключено';
			$status_class = 'red-dot'; // Класс для красного кружка
		}

		return ['status' => $status, 'class' => $status_class];
	}



	//Опции

	$connected_options = get_connected_options($specialist['id']);

	$connected_options_map = [];
	foreach ($connected_options as $option) {
		$connected_options_map[$option['option_id']] = $option['end_date'];
	}

	function generate_tariff_status($tariffs, $connected_tariffs_map)
	{
		$status = 'Не подключено';
		$status_class = 'red-dot';

		foreach ($tariffs as $tariff) {
			if (isset($connected_tariffs_map[$tariff['id']])) {
				$end_date = $connected_tariffs_map[$tariff['id']];
				$status = '"' . $tariff['name'] . '" до ' . $end_date;
				$status = strtoupper($status);
				$status_class = 'blue-dot';
				break;
			}
		}

		return ['status' => $status, 'class' => $status_class];
	}

	function generate_pro_photo_status($pro_photo_records, $registered_at)
	{
		// По умолчанию
		$status = 'Не проведена';
		$status_class = 'red-dot';
		$show_discount = false;

		// Проверяем, есть ли хотя бы одна запись с moderation = 0
		foreach ($pro_photo_records as $record) {
			if ($record['moderation'] == 0) {
				$status = 'Проведена';
				$status_class = 'blue-dot';
				return [
					'status' => $status,
					'class' => $status_class,
					'show_discount' => false
				];
			}
		}

		// Если записей нет — проверяем 2 недели
		if (!empty($registered_at)) {
			$registered_at_ts = strtotime($registered_at);
			$now = time();
			$diff = $now - $registered_at_ts;

			if ($diff < 14 * 24 * 60 * 60) {
				// прошло меньше 2 недель — показываем скидку
				$show_discount = true;
			}
		}

		return [
			'status' => $status,
			'class' => $status_class,
			'show_discount' => $show_discount
		];
	}


	function generate_option_status($connected_options_map, $connected_tariffs_map)
	{
		$count = count($connected_options_map);

		// Опции, входящие в состав тарифов
		$tariff_options = [
			1 => [1, 2, 3],
			2 => [1, 2, 3, 4, 5],
			3 => [1, 2, 3, 4, 5],
		];

		// исключение дублирующихся опций
		$included_options = [];
		foreach ($connected_tariffs_map as $tariff_id => $end_date) {
			if (isset($tariff_options[$tariff_id])) {
				$included_options = array_merge($included_options, $tariff_options[$tariff_id]);
			}
		}

		$unique_options = array_diff(array_keys($connected_options_map), $included_options);
		$count = count($unique_options) + count($included_options);

		if ($count > 0) {
			$status = 'Подключено (' . $count . ')';
			$status_class = 'blue-dot';
		} else {
			$status = 'Не подключено';
			$status_class = 'red-dot';
		}

		return ['status' => $status, 'class' => $status_class];
	}

	$tariff_status = generate_tariff_status($tariffs, $connected_tariffs_map);
	$option_status_data = generate_option_status($connected_options_map, $connected_tariffs_map);
	$promo_status_data = generate_promo_status($connected_promo_map);


	$top_cards = '
<div class="card">
    <div class="card-topline">
        <h4>Тарифы <span class="status-dot ' . $tariff_status['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-tariff.svg" alt="Тарифы">
    </div>
    <p>Стартовый набор для продвижения</p>
    <div class="card-status">
        <span></span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</div>
<div class="card">
      <div class="card-topline"><h4>Промо <span class="status-dot"></span></h4><img src="../assets/img/cabinet/card-promo.svg" alt=""></div>
        <p>Продвижение карточки</p>
        <div class="card-status">
            <span>' . $promo_status_data['status'] . '</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
    </div>
    <div class="card">
    <div class="card-topline">
        <h4>Опции <span class="status-dot ' . $option_status_data['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-options.svg" alt="Опции">
    </div>
    <p>Базовые настройки</p>
    <div class="card-status">
        <span>' . $option_status_data['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</div>
    <div class="card">
      <div class="card-topline"><h4>PRO-фото <span class="status-dot"></span></h4><img src="../assets/img/cabinet/card-photo.svg" alt=""></div>
        <p>Скидка 50% в первые две недели</p>
        <div class="card-status">
            <span>Не проведена</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
    </div>';

	$social_links = get_all_social();

	$code['mobile'] = '
	<div class="popup feedback feedback-cabinet">
		<div class="bg"></div>
		<div class="window">
			<div class="close"></div>
			<div class="reg">
				<form method="POST" id="feedback-popup">
					<div class="name">
						Контакты
					</div>
					<div class="form">
						<p class="text-only">
							По любым вопросам, а также с жалобами и предложениями о сотрудничестве вы можете обращаться через форму обратной связи на странице <a href="/cabinet/podderzhka" class="blue-link">“Поддержка”</a>.
						</p> 
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="mob-nav2">
		<div class="greeting__wrapper">
			<div class="profile-greeting">
    			Привет, <span>' . $name . '</span>
    			<a href="/actions/account/logout.php" class="logout">
        			<img src="/assets/img/logout-mobile-active.svg" alt="favorites">
    			</a>
			</div>
			<a href="/studio/' . $studio_slug . '">Перейти на страницу</a>
		</div>
		<div>
			<ul class="mob-menu__nav">
				<li class="faq"><a href="/cabinet/faq">Частые вопросы</a></li>
				<li class="support"><a href="/cabinet/podderzhka">Поддержка</a></li>
			</ul>
		</div>
		<div>
			<ul class="mob-menu__nav">
				<li class="blogs"><a href="/journal">Журнал</a></li>
			</ul>
		</div>
		<div>
			<ul class="mob-menu__nav">
				<li class="about"><a href="/o-proekte">О проекте</a></li>
				<li class="contacts"><a class="feedback-cabinet">Контакты</a></li>
			</ul>
		</div>
		<div>
			<div class="mob-menu__social">';

	foreach ($social_links as $social) {
		if ($social['alt'] === 'Telegram') {
			continue;
		}
		
		$code['mobile'] .= '<a target="_blank" href="' . htmlspecialchars($social['link']) . '" class="' . htmlspecialchars($social['alt']) . '"></a>';
	}

	$code['mobile'] .= '
			</div>
		</div>';

	$iteration = 1;

	foreach ($menu_pages as $menu_page) {
		$class = $menu_page['alias'] == $page ? 'active' : '';

		if ((str_starts_with($page, 'redaktirovat-') && $menu_page['id'] == 3) || ($menu_page['id'] == 1 && $page === '')) {
			$class = 'active';
		}

		$span = '';
		if ($menu_page['active'] == 0) {
			if ($menu_page['id'] == 9) {
				$class = 'disabled-stat';
				// $span = '<span>Собирается</span>';
			} else if ($menu_page['id'] == 10) {
				// $class = 'disabled-promo';
				// $span = '<span>Скоро</span>';
			}
		}

		if ($menu_page['id'] == 16) {
			// сохраняем HTML с уже определённым $class
			$menu_item_16 = "<li><a href='/cabinet/{$menu_page['alias']}' class='cabinet-menu-link{$iteration} {$class}'>{$menu_page['title']}</a></li>";
			continue;
		}

		$code['desctop'] .= "<li><a href='/cabinet/{$menu_page['alias']}' class='cabinet-menu-link{$iteration} {$class}'>{$menu_page['title']}</a>{$span}</li>";

		$iteration++;
	}
	$code['desctop'] .= "<li class='line'></li>";

	$blogClass = $page == 'moy-blog' ? 'active' : '';
	$postClass = $page == 'sozdat-zapis' ? 'active' : '';
	$helpClass = $page == 'podderzhka' ? 'active' : '';
	$faqClass = $page == 'faq' ? 'active' : '';

	$code['desctop'] .= "<li><a href='/cabinet/moy-blog' class='cabinet-menu-link9 {$blogClass}'>Мой блог</a></li>";
	$code['desctop'] .= "<li><a href='/cabinet/sozdat-zapis' class='cabinet-menu-link10 {$postClass}'>Написать пост</a></li>";
	$code['desctop'] .= "<li class='line'></li>";

	$code['desctop'] .= $menu_item_16;
	$code['desctop'] .= "<li><a href='/cabinet/faq' class='cabinet-menu-link8 {$faqClass}'>Частые вопросы</a></li>";
	$code['desctop'] .= "<li><a href='/cabinet/podderzhka' class='cabinet-menu-link7 {$helpClass}'>Поддержка</a></li>";

	/* $code['desctop'] .= "<p class='under-blog'>Делитесь знаниями и продвигайте свой профиль на страницах <a href='/journal' class='orange'>Журнала</a></p>"; */


	$code['desctop'] .= <<<EOF
			</ul>
	EOF;

	/* if ($page != 'cabinet_blog') {
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
	} */

	$code['desctop'] .= <<<EOF
		</nav>
	EOF;

	$code['mobile'] .= <<<EOF
		</div>
	EOF;

	return $code;
}
