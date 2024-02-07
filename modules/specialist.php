<?php
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/S.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/CasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Gender.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/BaseInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/NamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianLanguage.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianCasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/FirstNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/MiddleNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/LastNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/functions.php';

$specialist_id = end(explode('/', $_SERVER['REQUEST_URI']));
if (mb_strpos($specialist_id, 'id') === false) {
    responseRedirect('/');
} else {
    $specialist_id = intval(str_replace('id', '', $specialist_id));
}

// Получаем специалиста
$specialist = get_specialist($specialist_id);

// Если специалиста нет делаем редирект на главную
if (!$specialist) responseRedirect('/');

// Если компания
if ($specialist['company']) {
    $name_blog = $name_reviews = $name = '"' . $specialist['company'] . '"';
    $title_reviews = 'о компании';
    $title_blog = "компании";

    if ($specialist['classification_id'] == 5) {
        $title_reviews = 'о юридической компании';
        $title_blog = "юридической компании";
    } else if ($specialist['classification_id'] == 6) {
        $title_reviews = 'о коллегии адвокатов';
        $title_blog = "коллегии адвокатов";
    } else if ($specialist['classification_id'] == 7) {
        $title_reviews = 'об адвокатском бюро';
        $title_blog = "адвокатского бюро";
    } else if ($specialist['classification_id'] == 8) {
        $title_reviews = 'об экспертном учреждении';
        $title_blog = "экспертного учреждения";
    }
}

// Если частное лицо
if (!$specialist['company']) {
    $name = $specialist['name'] . ' ' . $specialist['lastname'];
    $title_reviews = 'о специалисте';
    $title_blog = "специалиста";
    if ($specialist['classification_id'] == 1) {
        $title_reviews = 'о юристе';
        $title_blog = "юриста";
    } else if ($specialist['classification_id'] == 2) {
        $title_reviews = 'об адвокате';
        $title_blog = "адвоката";
    } else if ($specialist['classification_id'] == 3) {
        $title_reviews = 'об эксперте';
        $title_blog = "эксперта";
    } else if ($specialist['classification_id'] == 4) {
        $title_reviews = 'о нотариусе';
        $title_blog = "нотариуса";
    }

    $name_reviews = morphos\Russian\inflectName($specialist['lastname'] . ' ' . $specialist['name'], 'дательный');
    $name_reviews = implode(' ', array_reverse(explode(' ', $name_reviews)));

    $name_blog = morphos\Russian\inflectName($specialist['lastname'] . ' ' . $specialist['name'], 'родительный');
    $name_blog = implode(' ', array_reverse(explode(' ', $name_blog)));
}

// классификация специалиста и хлебные крошки
if ($specialist['classification_id'] == 1 || $specialist['classification_id'] == 2 || $specialist['classification_id'] == 5 || $specialist['classification_id'] == 6 || $specialist['classification_id'] == 7) {
    $data['breadcrumbs'] = '<a href="/yuristy-i-advokaty">Юристы и адвокаты</a><span>/</span>';
} else if ($specialist['classification_id'] == 3 || $specialist['classification_id'] == 8) {
    $data['breadcrumbs'] = '<a href="/experty">Эксперты</a><span>/</span>';
} else if ($specialist['classification_id'] == 4) {
    $data['breadcrumbs'] = '<a href="/notariusy">Нотариусы</a><span>/</span>';
}
$data['breadcrumbs'] .= '<span class="curr-page">' . $specialist['classification'] . ' ' . $name . '</span>';

// Блок с контактами
if ($specialist['email'] && $specialist['phone'] && $specialist['active'] != 0) {
    $contacts_list = '<ul>
    <li class="company-write">
        <img src="/assets/img/contacts2.svg">
        Написать
    </li>
    <li class="event contacts-tel">
        <img src="/assets/img/contacts1.svg"> 
        <span class="non-clicked">Показать телефон</span>
        <span class="clicked"><a href="tel:' . $specialist['phone'] . '">' . format_phone($specialist['phone']) . '</a></span>
    </li>';
    $other = json_decode($specialist['other'], true);
    if ($other['email']) {
        $contacts_list .= '<li class="event contacts-email">
            <img src="/assets/img/contacts2.svg">
            <span class="non-clicked">Показать почту</span>
            <span class="clicked"><a href="mailto:' . $other['email'] . '">' . $other['email'] . '</a></span>
        </li>';
    }
    if ($other['site'] || $other['tg'] || $other['vk'] || $other['dzen']) {
        $contacts_list .= '<li class="event other"><i></i>Другие контакты</li>
            <li class="event more"><i></i>Показать ещё</li>';
        if ($other['site']) {
            $contacts_list .= '<li class="no-sticky"><a href="https://' . $other['site'] . '"><img src="/assets/img/contacts3.svg"> Сайт</a></li>';
        }
        if ($other['tg']) {
            $contacts_list .= '<li class="no-sticky"><a href="https://t.me/' . $other['tg'] . '"><img src="/assets/img/contacts4.svg"> Телеграм</a></li>';
        }
        if ($other['vk']) {
            $contacts_list .= '<li class="no-sticky"><a href="https://' . $other['vk'] . '"><img src="/assets/img/contacts5.svg"> Вконтакте</a></li>';
        }
        if ($other['dzen']) {
            $contacts_list .= '<li class="no-sticky"><a href="https://' . $other['dzen'] . '"><img src="/assets/img/contacts6.svg"> Яндекс. Дзен</a></li>';
        }
    }
    $contacts_list .= '</ul>';
    $data['specialist'] = <<<EOF
        <div class="contacts-block">
            <div id="contacts">
            {$contacts_list}
            </div>
        </div>
    EOF;
    $data['mobile_contacts'] = <<<EOF
        <div class="contacts-block mobile-contacts">
            {$contacts_list}
        </div>
    EOF;
} else if ($specialist['active'] == 0) {
    $data['specialist'] = <<<EOF
        <div class="contacts-block confirmation">
            <div id="confirmation">
                <div class="confirmation__title">
                    Это ваша страница?
                    <img src="/assets/img/ghost.svg">
                </div>
                <p class="confirmation__bold">К этому аккаунту пока не привязан Email.</p>
                <p>Чтобы получить доступ к личному кабинету, подтвердите страницу.</p>
                <a class="confirmation__button">Подтвердить</a>
            </div>
        </div>
    EOF;
}


// Аватарка
$specialist['photo'] = $specialist['photo'] ? $specialist['photo'] : 'company.png';

//Избранное
$fav_class = '';
foreach ($_SESSION['favorite'] as $fav_cat) {
    if (in_array($specialist_id, $fav_cat)) {
        $fav_class = 'active';
    }
}

$data['specialist'] .= <<<EOF
    <div class="top-info fav">
        <div class="company-actions">
			<i class="company-blog-btn"></i>
			<i class="company-fav {$fav_class}">
                <input type="hidden" id="favorite_cat" value="{$specialist['classification_id']}">
                <input type="hidden" id="favorite_id" value="{$specialist_id}">
            </i>
		</div>
        <div class="image" style="background: url('/uploads/avatars/{$specialist['photo']}') center/cover;"><div class="rocket"></div></div>
        <div class="company-name">
            <span>$name</span>
            {$specialist['classification']}
		</div>
EOF;

// Рейтинг
if ($specialist['count_rating']) {
    $data['specialist'] .= '<div class="rating"><span>';
    $rating = intval(round($specialist['rating'], 0));
    for ($i = 0; $i < $rating; $i++) {
        $data['specialist'] .= '<i class="fa fa-star"></i>';
    }
    for ($i = 5; $i > $rating; $i--) {
        $data['specialist'] .= '<i class="fa fa-star-o"></i>';
    }
    $data['specialist'] .= '</span><a class="show-reviews">' . $specialist['count_rating'] . ' отзывов</a></div>';
}

$data['specialist'] .= <<<EOF
    <div class="city">
        <span>Город:</span> {$specialist['city']}
    </div>
EOF;

// Образование
$data['specialist'] .= education($specialist);

// Опыт работы
$data['specialist'] .= experience($specialist);

//Кнопка "Показать контакты"
if ($specialist['active'] != 0) {
    $data['specialist'] .= <<<EOF
        <button class="contacts-button">Показать контакты <img src="/assets/img/contacts8.svg"></button>
    EOF;
}
// Описание
if (!is_null($specialist['description']) && $specialist['description']) {
    $description = nl2br($specialist['description']);
    $data['specialist'] .= <<<EOF
        $description
    EOF;
}
$data['specialist'] .= <<<EOF
    </div>
EOF;

// Направления работы
$specializations = get_specialist_specialization($specialist['id']);
$ways = block_ways($specialist, $specializations);
$data['specialist'] .= $ways['desctop'];
$data['specialization_popup'] = $ways['mobile'];


// Прайсы
$prices = block_price($specialist);
$data['prices'] = $prices['desctop'];
$data['services_popup'] = $prices['popup'];

// Отзывы
$reviews = block_reviews($specialist, $title_reviews, $name_reviews);
$data['services'] .= $reviews['desctop'];
$data['reviews_popup'] .= $reviews['popup'];

// График работы
$data['services'] .= block_schedule($specialist['schedule']);

//адреса и карта
$adress = $map_adress = [];
if (!is_null($specialist['metro']) && $specialist['metro']) {
    $adress[] = $specialist['metro'];
}
if (!is_null($specialist['city']) && $specialist['city']) {
    $adress[] = $specialist['city'];
    $map_adress[] = $specialist['city'];
}
if (!is_null($specialist['street']) && $specialist['street']) {
    $adress[] = $specialist['street'];
    $map_adress[] = $specialist['street'];
}
if (!is_null($specialist['house']) && $specialist['house']) {
    $adress[] = $specialist['house'];
    $map_adress[] = $specialist['house'];
}
$how_to_get = '';
if (!is_null($specialist['how_to_get']) && $specialist['how_to_get']) {
    $how_to_get = $specialist['how_to_get'];
}
$adress = implode(', ', $adress);
$map_adress = implode(', ', $map_adress);
// Вывод адресов и карты
if ($adress) {
    $data['services'] .= block_address($adress, $map_adress, $how_to_get, $specialist['map_point']);
}

// Сотрудники
$data['employees'] = block_employees($specialist);

// Блог
$data['blog'] = block_blog($specialist, $title_blog, $name_blog);

// Документы
if (!is_null($specialist['document']) && $specialist['document']) {
    $data['documents'] = block_documents($specialist['document']);
} else {
    $data['documents'] = '';
}

$data['input_specialist_id'] = "<input type='hidden' name='specialist' value='{$specialist['id']}'>";


/**
 * Блоки специалистов внизу страницы
 */

// Заголовки блока Юристов и Адвокатов
$recommended_title_lawyers = match ($specialist['classification_id']) {
    1, 2, 5, 6 => 'Юристы и адвокаты с похожей специализацией',
    3, 7 => 'Юристы и адвокаты по теме',
    4, 8 => 'Рекомендуемые юристы и адвокаты'
};

// Кнопки блока Юристов и Адвокатов
$recommended_button_lawyers = match ($specialist['classification_id']) {
    1, 2, 5, 6 => 'Посмотреть других специалистов',
    3, 4, 7, 8 => 'Найти юриста или адвоката',
};

// Заголовки блока экспертов
$recommended_title_experts = match ($specialist['classification_id']) {
    1, 2, 5, 6 => 'Эксперты по теме',
    3, 7 => 'Эксперты с похожей специализацией',
    4, 8 => 'Рекомендуемые эксперты'
};

// Кнопки блока Экспертов
$recommended_button_experts = match ($specialist['classification_id']) {
    1, 2, 4, 5, 6, 8 => 'Найти эксперта',
    3, 7 => 'Посмотреть других специалистов',
};

// Заголовки блока Нотариусов
$recommended_title_notaries = match ($specialist['classification_id']) {
    1, 2, 3, 5, 6, 7 => 'Нотариусы рядом',
    4, 8 => 'Другие нотариусы рядом'
};

// Кнопки блока Нотариусов
$recommended_button_notaries = match ($specialist['classification_id']) {
    1, 2, 3, 5, 6, 7 => 'Найти нотариуса',
    4, 8 => 'Посмотреть других специалистов'
};

// Подтягиваем список юристов и адвокатов
if ($specialist['classification_id'] != 4 && $specialist['classification_id'] != 8) {
    if (!empty($specializations) && $specializations) {
        $lawyers = get_specialists_by_service(implode(',', array_keys($specializations)), 8);
    } else {
        $lawyers = '';
    }
} else {
    $lawyers = get_specialists_by_classification([1, 2, 5, 6], 8);
}
if ($lawyers) {
    $lawyers = block($lawyers);
}

// Подтягиваем список экспертов
$experts = get_specialists_by_classification([3, 7], 8);
$experts = block($experts);

// Подтягиваем список специалистов
$notaries = get_specialists_by_classification([4, 8], 8);
$notaries = block($notaries);

// Порядок блоков
$block_order = match ($specialist['classification_id']) {
    1, 2, 5, 6 => ['lawyers', 'experts', 'notaries'],
    3, 7 => ['experts', 'lawyers', 'notaries'],
    4, 8 => ['notaries', 'lawyers', 'experts']
};


foreach ($block_order as $block) {
    $title = 'recommended_title_' . $block;
    $button = 'recommended_button_' . $block;


    if ($block == 'experts') {
        $href = 'experty';
    } else if ($block == 'notaries') {
        $href = 'notariusy';
    } else {
        $href = 'yuristy-i-advokaty';
    }

    $desc_class = '';
    $mobile_button = '';
    if ($$button == 'Посмотреть других специалистов') {
        $desc_class = 'class="desctop_only"';
        $mobile_button = '<a href="/' . $href . '" class="mobile_only">Больше специалистов</a>';
    }
    if ($$block) {
        $data['recomended'] .= <<<EOF
        <div class="index-catalog-block on-company-page">
            <div class="block-name company-page-name">
                {$$title}
            </div>
            <div class="grid_flex">
                {$$block}
            </div>
            <div class="bottom-link">
                <a href="/{$href}" {$desc_class}>{$$button}</a>
                {$mobile_button}
            </div>
        </div>
        EOF;
    }
}

// Устанавливаем title
$HTML = html_replace([
    'title' => $name
], $HTML);
$HTML = html_explode($data, $HTML);


/**
 * @param array $specialist
 * @return string
 */
function experience(array $specialist): string
{
    $code = '<div class="year">';

    if ($specialist['foundation']) {
        $specialist['foundation'] = date('Y', strtotime($specialist['foundation']));

        if ($specialist['company']) $code .= "<span>Год основания:</span> {$specialist['foundation']}";

        if (!$specialist['company']) {
            $experience = date('Y') - $specialist['foundation'];

            if (!$experience) $insert = 'менее года';
            if ($experience) $insert = num_decline($experience, 'год, года, лет');

            $code .= "<span>Опыт работы:</span> {$insert}";
        }
    }
    $code .= '</div>';

    return $code;
}

function education(array $specialist): string
{
    $code = '<div class="education">';

    if ($specialist['university'] && !$specialist['company']) {
        $education = json_decode($specialist['university']);
        $code .= "<span>Образование:</span> " . implode(", ", $education);
    }
    $code .= '</div>';

    return $code;
}

/**
 * @param array $specialist
 * @param array $specializations
 * @return array
 */
function block_ways(array $specialist, array $specializations): array
{
    $code = [
        'desctop'   => '',
        'mobile'    => ''
    ];

    if ($specialist['classification_id'] != 4 && $specialist['classification_id'] != 8 && $specializations) {
        $list = '';
        $class_link = get_classification_link($specialist['classification_id']);

        foreach ($specializations as $key => $specialization) {
            if ($specialization['p_id'] != 0) {
                continue;
            }
            //$href = "/".$class_link."/" .($specialization['p_id'] === 0 ? '' : $specialization['p_slug'] . '/') . $specialization['slug'];
            $href = "/" . $class_link . "/" . $specialization['slug'];
            $list .= "<a href='{$href}'>{$specialization['title']}</a>";

            $code['mobile'] .= '<li class="' . $specialization['slug'] . '"><a href="' . $href . '">' . $specialization['title'] . '</a></li>';
        }

        $code['desctop'] .= <<<EOF
            <div class="ways">
                <div class="name">
                    Направления работы
                </div>
                <div class="flex">
                    $list
                </div>
                <button>Посмотреть все направления</button>
            </div>
        EOF;
    }

    return $code;
}

/**
 * @param array $specialist
 * @return array
 */
function block_price(array $specialist): array
{
    $code = [
        'desctop'   => '',
        'popup'     => ''
    ];

    $services = get_specialist_service($specialist['id']);

    if ($services) {
        $code['desctop'] .= <<<EOF
            <div class="prices">
                <div class="name">Стоимость услуг</div>
                <table class="prices__table">
        EOF;

        foreach ($services as $key => $service) {
            $of = $service['ot'] ? 'от' : '';

            $code['popup'] .= <<<EOF
                <div class="price-item">
                    <div class="price-name">{$service['name']}</div>
                    <div class="price-value">{$of} {$service['price']} ₽</div>
                </div>
            EOF;

            $class = '';
            if ($key > 5) $class = 'class="hidden-price"';

            $code['desctop'] .= <<<EOF
                <tr {$class}>
                    <td>{$service['name']}</td>
                    <td>{$of} {$service['price']} ₽</td>
                </tr>
            EOF;
        }

        $code['desctop'] .= "</table>";

        if (count($services) > 6) $code['desctop'] .= "<button>Посмотреть все цены</button>";
        $code['desctop'] .= "</div>";
    }

    return $code;
}

/**
 * @param array $specialist
 * @param string $title
 * @param string $name
 * @return array
 */
function block_reviews(array $specialist, string $title, string $name): array
{
    $code = [
        'desctop'   => '',
        'popup'     => ''
    ];

    $code['desctop'] = $code['popup'] = <<<EOF
        <div class="reviews">
    EOF;

    if ($specialist['count_rating']) {
        $rating = intval(round($specialist['rating'], 0));
        $rating_html = '';
        for ($i = 0; $i < $rating; $i++) {
            $rating_html .= '<i class="fa fa-star"></i>';
        }
        for ($i = $rating; $i < 5; $i++) {
            $rating_html .= '<i class="fa fa-star-o"></i>';
        }
        $quantity = '<a class="show-reviews">' . $specialist['count_rating'] . ' отзывов</a>';
        $code['desctop'] .= <<<EOF
                <div class="name">
                    Отзывы $title $name
                    <span class="rating">
                        <span>
                            {$rating_html}
                        </span>
                        {$quantity}
                    </span>
                </div>
        EOF;

        $code['popup'] .= <<<EOF
                <div class="name">
                    Отзывы $title $name
                    <span class="rating">
                        <span>
                            {$rating_html}
                        </span>
                        <span class="text">{$specialist['count_rating']} отзывов</span>
                    </span>
                </div>
                <div class='reviews-list'>
        EOF;

        $reviews = get_specialist_reviews($specialist['id']);

        $monthsList = monthArr();


        foreach ($reviews as $key => $review) {
            $month = date("m", strtotime($review['date']));
            $date = date("m, Y", strtotime($review['date']));
            $date = str_replace($month, $monthsList[$month], $date);
            $rating = '';
            for ($i = 0; $i < $review['rating']; $i++) {
                $rating .= '<i class="fa fa-star"></i>';
            }
            for ($i = $review['rating']; $i < 5; $i++) {
                $rating .= '<i class="fa fa-star-o"></i>';
            }
            $code['popup'] .= <<<EOF
                <div class="item">
                    <div class="date">
                        <span>{$date}</span>
                        {$review['author']}
                        <span class="rating"> 
                            {$rating}
                        </span>
                    </div>
                    <p>{$review['review']}</p>
                </div>
            EOF;

            if ($key > 2) continue;

            $code['desctop'] .= <<<EOF
                <div class="item">
                    <div class="date">
                        <span>{$date}</span>
                        {$review['author']}
                        <span class="rating"> 
                            {$rating}
                        </span>
                    </div>
                    <p>{$review['review']}</p>
                </div>
            EOF;
        }


        $code['popup'] .= <<<EOF
            </div>
        EOF;

        $show_reviews_btn = '';
        if (count($reviews) > 2) {
            $show_reviews_btn = '<button class="show-reviews">Посмотреть все отзывы</button>';
        }

        $code['desctop'] .= <<<EOF
            <div class="buttons">
                {$show_reviews_btn}
                <button class="write-review">Написать отзыв</button>
            </div>
        EOF;
    } else {
        $code['desctop'] .= <<<EOF
            <div class="name">
                Отзывы $title $name
            </div>
            <p>Отзывов пока нет</p>
            <div class="buttons">
                <button class="write-review">Написать отзыв</button>
            </div>
        EOF;
    }
    $code['desctop'] .= <<<EOF
        </div>
    EOF;
    $code['popup'] .= <<<EOF
        </div>
    EOF;

    return $code;
}

/**
 * @param string $schedule
 * @return string
 */
function block_schedule(string $schedule): string
{
    $code = '';

    $schedule = json_decode($schedule, true);

    if ($schedule) {
        $code = <<<EOF
            <div class="days">
                <div class="name">График работы</div>
        EOF;
        foreach ($schedule as $key => $value) {
            $day = match ($key) {
                1 => 'Пн',
                2 => 'Вт',
                3 => 'Ср',
                4 => 'Чт',
                5 => 'Пт',
                6 => 'Сб',
                7 => 'Вс',
            };

            $code .= <<<EOF
                <div class="item">
                    <span class="day">$day</span>
            EOF;

            if (!$value['start'] || !$value['end']) $code .= "<span class='time'>Выходной</span>";
            else {
                $code .= "<span class='time'>{$value['start']} – {$value['end']}</span>";
            }

            if (isset($value['b_start'])) $code .= "Перерыв с {$value['b_start']} до {$value['b_end']}</span>";
            $code .= "</div>";
        }

        $code .= "</div>";
    }

    return $code;
}

/**
 * @param string $address
 * @return string
 */
function block_address(string $address, string $map_adress, string $how_to_get, string $point): string
{
    $code = '';

    if ($address) {
        if ($how_to_get) {
            $how_to_get = '<p>' . $how_to_get . '</p>';
        }

        $code = '
            <div class="map">
                <div class="name">
                    Адрес на карте
                </div>
                <div class="mmap">
                    <div id="specialist_adress_map">
                    </div>
                    <script src="https://api-maps.yandex.ru/2.1/?lang=ru-RU" type="text/javascript"></script>
                    <script type="text/javascript">
                        ymaps.ready(init);
                        function init() {
	                        var myMap = new ymaps.Map("specialist_adress_map", {
		                        center: ['.$point.'],
		                        zoom: 16
	                        }, {
		                        searchControlProvider: "yandex#search"
	                        });
	                        var myCollection = new ymaps.GeoObjectCollection(); 
	                        // Добавим метку красного цвета.
	                        var myPlacemark = new ymaps.Placemark([
		                        '.$point.'
	                        ], {
		                        balloonContent: "'.$map_adress.'"
	                        }, {
		                        preset: "islands#icon",
		                        iconColor: "#FF8139"
	                        });
	                        myCollection.add(myPlacemark);
	                        myMap.geoObjects.add(myCollection);
                        }
                    </script>
                </div>
                <p class="grey">Как добраться:</p>
                <div class="adress" id="copy-adress">
                    '.$address.'
                </div>
                <textarea id="content">'.$address.'</textarea>
                '.$how_to_get.'
                <a href="yandexnavi://map_search?text='.$map_adress.'"><img src="/assets/img/map-button1.svg"> Открыть в Яндекс.Навигаторе</a>
                <a href="https://yandex.ru/maps/?text='.$map_adress.'"><img src="/assets/img/map-button2.svg"> Открыть в Яндекс.Картах</a>
            </div>
        ';
    }

    return $code;
}

/**
 * @param array $specialist
 * @return string
 */
function block_employees(array $specialist): string
{
    $code = '';

    if ($specialist['company'] && $employees = get_company_employees($specialist['company_id'])) {

        $code = <<<EOF
            <div class="staff-block">
                <div class="block-name">Сотрудники организации</div>
                <div class="staff__grid">
                    <div class="item">
                        <div class="img__wrapper" style="background-image: url(/uploads/avatars/{$specialist['photo']})"></div>
                        <div>
                            <span>{$specialist['name']} {$specialist['lastname']}</span>
                            Руководитель компании
                        </div>
                    </div>
        EOF;

        foreach ($employees as $employee) {
            $code .= <<<EOF
                <div class="item">
                    <div class="img__wrapper" style="background-image: url(/uploads/employee/{$employee['photo']})"></div>
                    <div>
                        <span>{$employee['name']}</span>
                        {$employee['post']}
                    </div>
                </div>
            EOF;
        }

        $code .= <<<EOF
                </div>
            </div>
        EOF;
    }

    return $code;
}

/**
 * @param array $specialist
 * @param string $title
 * @param string $name
 * @return string
 */
function block_blog(array $specialist, string $title, string $name): string
{
    $code = '';

    $blog = get_specialist_blog($specialist['id']);

    if ($blog) {
        $articles = get_blog_articles($blog['id'], 0, 2);
        if ($articles) {
            $code = <<<EOF
                <div class="content2">
                    <div class="articles">
                        <div class="name"><a href="/journal/id{$specialist['id']}">Блог $title $name</a></div>
            EOF;

            foreach ($articles as $article) {
                $date = date("d.m.Y", strtotime($article['date']));
                $themes = '';
                $themes_arr = get_article_themes($article['id']);
                if ($themes_arr) {
                    $themes = '<div class="tags">';
                    foreach ($themes_arr as $theme) {
                        $class_link = get_classification_link($theme['classification']);
                        $themes .= '<a href="/' . $class_link . '/' . ($theme['p_id'] === 0 ? '' : $theme['p_slug'] . '/') . $theme['slug'] . '">#' . $theme['title'] . '</a>';
                    }
                    $themes .= '</div>';
                }
                $code .= <<<EOF
                    <div class="article_item">
                        <div class="article_body">
                            <div class="date">{$date}{$themes}</div>
                            <a href="/article/{$article['id']}" class="link">{$article['title']}</a>
                            <div class="article_text">{$article['content']}</div>
                            <div class="article-bottom-link">
                                <a href="/article/{$article['id']}">ЧИТАТЬ ДАЛЕЕ</a>
                            </div>
                        </div>
                    </div>
                EOF;
            }

            $code .= <<<EOF
                        <a href="/journal/id{$specialist['id']}" class="bottom-button">Посмотреть все записи</a>
                    </div>
                </div>
            EOF;
        } else {
            $code .= <<<EOF
                <div class="content2">
                    <div class="articles">
                        <div class="name"><a href="/journal/id{$specialist['id']}">Блог $title $name</a></div>
                        <p class="empty-blog">В данном блоге еще нет записей.</p>
                    </div>
                </div>
            EOF;
        }
    }

    return $code;
}

/**
 * @param string $documents
 * @return string
 */
function block_documents(string $documents): string
{
    $code = '';

    $documents = json_decode($documents, true);

    if ($documents) {
        $code = <<<EOF
            <div class="docs-block">
                <div class="block-name">Документы, достижения</div>
                <div class="list">
        EOF;

        foreach ($documents as $document) {
            $code .= <<<EOF
                <div class="item">
                    <img src="/uploads/documents/{$document['file']}">
                    <p>{$document['name']}</p>
                </div>
            EOF;
        }

        $code .= <<<EOF
                </div>
            </div>
        EOF;
    }

    return $code;
}
