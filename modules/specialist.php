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

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'); // убираем query string
$parts = explode('/', $uri);
$lastPart = end($parts);

// Проверяем GET-параметры, разрешаем Метрику
$isYandexMetrics = false;
if (!empty($_GET)) {
    foreach ($_GET as $k => $v) {
        if (strpos($k, '_ym') === 0) {
            $isYandexMetrics = true;
            break;
        }
    }
}

$specialist = null;
$specialist_id = null;

if (strpos($lastPart, 'id') === 0) {
    $specialist_id = intval(str_replace('id', '', $lastPart));
    $specialist = get_specialist($specialist_id);

    if (!$specialist && !$isYandexMetrics) {
        responseRedirect('/404');
    }

    if (!empty($specialist['slug'])) {
        $slugUrl = '/specialist/' . $specialist['slug'];
        responseRedirect($slugUrl);
    }
} else {
    $slug = $lastPart;
    $specialist = get_specialist_by_slug($slug);

    if (!$specialist && !$isYandexMetrics) {
        responseRedirect('/404');
    }

    if ($specialist) {
        $specialist_id = $specialist['id'];
    }
}

// Если специалиста нет делаем редирект на главную
/* if (!$specialist) responseRedirect('/404'); */

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

    $lastname_spec = str_replace(' ', '', $specialist['lastname']);
    $name_spec = str_replace(' ', '', $specialist['name']);

    $name_reviews = morphos\Russian\inflectName($lastname_spec . ' ' . $name_spec, 'предложный');
    $name_reviews = implode(' ', array_reverse(explode(' ', $name_reviews)));

    $name_blog = morphos\Russian\inflectName($lastname_spec . ' ' . $name_spec, 'родительный');
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


if ($specialist_id) {
    $connected_tariffs = get_connected_tariffs($specialist_id);
    $connected_options = get_connected_options($specialist_id);
} else {
    $connected_tariffs = [];
    $connected_options = [];
}
$connected_option_ids = array_column($connected_options, 'option_id');

$other = json_decode($specialist['other'], true);

$inner_contacts = '';

if ($other['email']) {
    $inner_contacts .= '
        <li class="company-write">
            <img src="/assets/img/contacts2-2.svg">
            <span>Записаться на консультацию</span>
        </li>';
}

if (!empty($connected_tariffs)) {
    if ($specialist['phone'] || $other['email'] || $other['site'] || $other['tg'] || $other['vk']) {
        $inner_contacts .= '<li class="event other"><i></i>Развернуть контакты</li>
            <li class="event more"><i></i>Развернуть контакты</li>';

        if ($specialist['phone']) {
            $inner_contacts .= '<li class="event no-sticky contacts-tel">
                <img src="/assets/img/contacts1.svg"> 
                <span class="non-clicked">Показать телефон</span>
                <span class="clicked"><a href="tel:' . htmlspecialchars($specialist['phone']) . '">' . format_phone($specialist['phone']) . '</a></span>
            </li>';
        }

        if ($other['email']) {
            $inner_contacts .= '<li class="event no-sticky contacts-email">
                <img src="/assets/img/contacts2.svg">
                <span class="non-clicked">Показать почту</span>
                <span class="clicked"><a href="mailto:' . htmlspecialchars($other['email']) . '">' . htmlspecialchars($other['email']) . '</a></span>
            </li>';
        }

        if ($other['site']) {
            $inner_contacts .= '<li class="no-sticky"><a href="' . $other['site'] . '" rel="nofollow"><img src="/assets/img/contacts3.svg"> <span>Сайт</span></a></li>';
        }
        if ($other['tg']) {
            $inner_contacts .= '<li class="no-sticky"><a href="' . $other['tg'] . '" rel="nofollow"><img src="/assets/img/contacts4.svg"> Телеграм</a></li>';
        }
        if ($other['vk']) {
            $inner_contacts .= '<li class="no-sticky"><a href="' . $other['vk'] . '" rel="nofollow"><img src="/assets/img/contacts5.svg"> Вконтакте</a></li>';
        }
        if ($other['dzen']) {
            $inner_contacts .= '<li class="no-sticky"><a href="' . $other['dzen'] . '" rel="nofollow"><img src="/assets/img/contacts6.svg"> Яндекс.Дзен</a></li>';
        }
    }
} elseif (!empty($connected_option_ids)) {
    $opt_contacts = '';

    if (in_array(1, $connected_option_ids) && $specialist['phone']) {
        $opt_contacts .= '<li class="event no-sticky contacts-tel">
            <img src="/assets/img/contacts1.svg">
            <span class="non-clicked">Показать телефон</span>
            <span class="clicked"><a href="tel:' . htmlspecialchars($specialist['phone']) . '">' . format_phone($specialist['phone']) . '</a></span>
        </li>';
    }

    if (in_array(2, $connected_option_ids) && $other['email']) {
        $opt_contacts .= '<li class="event no-sticky contacts-email">
            <img src="/assets/img/contacts2.svg">
            <span class="non-clicked">Показать почту</span>
            <span class="clicked"><a href="mailto:' . htmlspecialchars($other['email']) . '">' . htmlspecialchars($other['email']) . '</a></span>
        </li>';
    }

    if (in_array(3, $connected_option_ids) && ($other['site'] || $other['tg'] || $other['vk'] || $other['dzen'])) {
        if ($other['site']) {
            $opt_contacts .= '<li class="no-sticky"><a href="' . $other['site'] . '" rel="nofollow"><img src="/assets/img/contacts3.svg"> <span>Сайт</span></a></li>';
        }
        if ($other['tg']) {
            $opt_contacts .= '<li class="no-sticky"><a href="' . $other['tg'] . '" rel="nofollow"><img src="/assets/img/contacts4.svg"> Телеграм</a></li>';
        }
        if ($other['vk']) {
            $opt_contacts .= '<li class="no-sticky"><a href="' . $other['vk'] . '" rel="nofollow"><img src="/assets/img/contacts5.svg"> Вконтакте</a></li>';
        }
        if ($other['dzen']) {
            $opt_contacts .= '<li class="no-sticky"><a href="' . $other['dzen'] . '" rel="nofollow"><img src="/assets/img/contacts6.svg"> Яндекс.Дзен</a></li>';
        }
    }

    if ($opt_contacts) {
        $inner_contacts .= '
            <li class="event other"><i></i>Развернуть контакты</li>
            <li class="event more"><i></i>Развернуть контакты</li>'
            . $opt_contacts;
    }
}

if (!empty($inner_contacts)) {
    $contacts_list .= '<ul>' . $inner_contacts . '</ul>';

    $data['specialist'] = <<<EOF
        <div class="contacts-block">
            <div id="contacts">
                {$contacts_list}
            </div>
        </div>
    EOF;

    $data['mobile_contacts'] = <<<EOF
        <div class="name mobile-contacts">Контакты</div> 
        <div class="contacts-block mobile-contacts">
            {$contacts_list}
        </div>
    EOF;
}
if ($specialist['active'] == 0) {
    // Блок, если специалист не активен
    $data['specialist_top_line'] = <<<EOF
    <div class="confirmation-sticky" style="
        max-width: 1472px;
        margin: 0 auto;
        z-index: 1000;
        height: 64px;
        background-color: #585858;
        display: flex;
        align-items: center;
        color: white;
        font-size: 14px;
    ">
        <div style="padding: 0 32px;">
            Это ваша страница? Чтобы получить доступ к личному кабинету,&nbsp;
            <a href="#" class="confirmation__button" style="
                color: #FF8139;
                text-decoration: underline;
            ">подтвердите почту</a>
        </div>
    </div>
EOF;
}

// Аватарка
$specialist['photo'] = !empty($specialist['photo'])
    ? $specialist['photo']
    : (!empty($specialist['company_id']) ? '022.webp' : '02.webp');

//Избранное
$fav_class = '';
foreach ($_SESSION['favorite'] as $fav_cat) {
    if (in_array($specialist_id, $fav_cat)) {
        $fav_class = 'active';
    }
}

$tariff3Connected = false;
foreach ($connected_tariffs as $tariff) {
    if ($tariff['tariff_id'] == 3) {
        $tariff3Connected = true;
    }
}
$rocketStyle = $tariff3Connected ? 'display: block;' : 'display: none;';

$blog = get_specialist_blog($specialist['id']);
if ($blog) {
    $articles = get_blog_articles($blog['id'], 0, 2);
    if ($articles) {
        $icon_blog .= '<i class="company-blog-btn"></i>';
    }
}
$data['specialist'] .= <<<EOF
    <div class="top-info fav">
        <div class="company-actions">
			{$icon_blog}
			<i class="company-fav {$fav_class}">
                <input type="hidden" id="favorite_cat" value="{$specialist['classification_id']}">
                <input type="hidden" id="favorite_id" value="{$specialist_id}">
            </i>
		</div>
        <div class="image lazy-bg" data-bg="/uploads/avatars/{$specialist['photo']}"><div class="rocket" style="$rocketStyle"></div></div>
        <div class="company-name">
            <span class="specialist-name">$name</span>
            <span class="classification-name">{$specialist['classification']}</span>
		</div>
EOF;

$email = htmlspecialchars($other['email']);
$randId = 'id' . bin2hex(random_bytes(6));
$data['specialist'] .= <<<EOF
<div class="popup write">
  <div class="bg"></div>
  <div class="window middle">
      <div class="close"></div>
      <div class="reg">
          <div class="name">Форма записи</div>
          <div class="form">
                <div class="field">
                    <input class="inp" name="name" type="text" placeholder="Ваше имя" />
                    <div class="invalid-feedback"></div>
                </div>
              <div class="field">
                  <input type="text" class="phone-input-reg" name="contactInfo" placeholder="Номер вашего телефона для связи" />
                  <div class="invalid-feedback"></div>
                  <input type="hidden" id="specialist_mail" value="{$email}">
                  <input type="hidden" name="specialist_id" value="{$specialist_id}">
              </div>
              <div class="field">
                  <textarea class="popup-textarea" name="message" placeholder="Сообщение"></textarea>
                  <div class="invalid-feedback"></div>
              </div>
              <div class="field captcha-field">
                <div class="captcha-row">
                    <div class="captcha-left">
                    <img id="captcha" src="/libs/securimage/securimage_show.php?id={$randId}" alt="CAPTCHA" aria-hidden="false" />
                    <button type="button" id="captcha_refresh" class="captcha-refresh" title="Обновить каптчу" aria-label="Обновить каптчу">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M21 12a9 9 0 10-3.2 6.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    </div>

                    <div class="captcha-right">
                    <label class="captcha-label" for="captcha_code">Подтвердите, что вы не робот</label>
                    <input class="captcha-input" id="captcha_code" name="captcha_code" type="text" placeholder="Введите код" autocomplete="off" />
                    <input type="hidden" id="captcha_captcha_id" name="captcha_id" value="{$randId}" />
                    </div>
                </div>
                <div class="captcha-error"></div>
                </div>

              <div class="button">
                  <p>
                      Нажимая “Отправить”, я принимаю изложенное в документах,
                      представленных в разделе сайта “О проекте”.
                  </p>
                  <button id="send_message_to_specialist" class="popup-btn">Отправить</button>
              </div>
          </div>
      </div>
  </div>
</div>
EOF;

$data['confirmation_popup'] = '
<div class="popup confirmation__popup">
  <div class="bg"></div>
  <div class="window middle">
    <div class="close"></div>
    <div class="reg">
      <div class="name">Подтверждение прав</div>
      <div class="form">
        <div class="field">
          <input type="text" name="email" placeholder="Email*" class="inp" />
          <input type="hidden" name="user_id" id="user_id_input" placeholder="User_id*" value="' . $specialist_id . '" class="inp" />
          <div class="invalid-feedback"></div>
        </div>

        <!-- Капча -->
        <div class="field captcha-field">
          <div class="captcha-row">
            <div class="captcha-left">
              <img id="captcha_conf" src="/libs/securimage/securimage_show.php?id=' . $randId . '" alt="CAPTCHA" aria-hidden="false" />
              <button type="button" id="captcha_refresh_conf" class="captcha-refresh" title="Обновить каптчу" aria-label="Обновить каптчу">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M21 12a9 9 0 10-3.2 6.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>

            <div class="captcha-right">
              <label class="captcha-label" for="captcha_code_conf">Подтвердите, что вы не робот</label>
              <input class="captcha-input" id="captcha_code_conf" name="captcha_code_conf" type="text" placeholder="Введите код" autocomplete="off" />
              <input type="hidden" id="captcha_captcha_id_conf" name="captcha_id_conf" value="' . $randId . '" />
            </div>
          </div>
          <div class="captcha-error"></div>
        </div>
        <!-- Конец капчи -->

        <div class="button">
          <p>
            Нажимая “Подтвердить права”, я принимаю изложенное в документах,
            представленных в разделе сайта “О проекте”.
          </p>
          <button class="popup-btn send-confirmation">Подтвердить права</button>
        </div>
        <div class="bottom-links flex">
          <div>
            <a class="login">Войти</a>
            <a class="reg">Зарегистрироваться</a>
          </div>
          <a class="feedback">Контакты</a>
        </div>
      </div>
    </div>
  </div>
</div>';

$data['captcha_review'] = '
<div class="field captcha-field">
    <div class="captcha-row">
        <div class="captcha-left">
            <img id="captcha_review" src="/libs/securimage/securimage_show.php?id=' . $randId . '" alt="CAPTCHA" aria-hidden="false" />
            <button type="button" id="captcha_refresh_review" class="captcha-refresh" title="Обновить каптчу" aria-label="Обновить каптчу">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M21 12a9 9 0 10-3.2 6.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="captcha-right">
            <label class="captcha-label" for="captcha_code_review">Подтвердите, что вы не робот</label>
            <input class="captcha-input" id="captcha_code_review" name="captcha_code_review" type="text" placeholder="Введите код" autocomplete="off" />
            <input type="hidden" id="captcha_captcha_id_review" name="captcha_id_review" value="' . $randId . '" />
        </div>
    </div>
    <div class="captcha-error"></div>
</div>
';
$HTML_ = explode('<!--data:captcha_review-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:captcha_review-->{$data['captcha_review']}<!--data:captcha_review-->" . $HTML_[2];


$data['h1_hidden_title'] = '<h1 class="dn">' . $name . '</h1>';

// Функция склонения
function pluralForm(int $number, string $one, string $few, string $many): string
{
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    if ($mod10 == 1 && $mod100 != 11) return $one;
    if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) return $few;
    return $many;
}

// Рейтинг
$reviews_count = isset($specialist['count_rating']) ? (int)$specialist['count_rating'] : 0;
$rating = isset($specialist['rating']) ? intval(round($specialist['rating'], 0)) : 0;

$stars = '';
for ($i = 0; $i < $rating; $i++) {
    $stars .= '<i class="fa fa-star"></i>';
}
for ($i = $rating; $i < 5; $i++) {
    $stars .= '<i class="fa fa-star-o"></i>';
}

$reviews_text = pluralForm($reviews_count, 'отзыв', 'отзыва', 'отзывов');

if ($reviews_count > 0) {
    $reviews_display = "<a class=\"show-reviews\">{$reviews_count} {$reviews_text}</a>";
} else {
    // Некликабельная, но цвет не меняется
    $reviews_display = "<a class=\"show-reviews\" tabindex=\"-1\" style=\"pointer-events: none; cursor: default; text-decoration: none; color: inherit;\">{$reviews_count} {$reviews_text}</a>";
}

$data['specialist'] .= <<<HTML
<div class="rating">
    <span>{$stars}</span>
    {$reviews_display}
</div>
HTML;



$metroPart = '';
if (!empty($specialist['metro'])) {
    $metroPart = ", м. {$specialist['metro']}";
}

// Ссылка-триггер для плавного скролла к элементу с классом "map"
$mapLink = ' <a href="#" '
    . 'style="color: #4F77B4; text-decoration: none;" '
    . 'onclick="
        (function(){
            var el = document.querySelector(\'.map\');
            if (!el) return false;
            if (window.innerWidth <= 768) {
                var top = el.getBoundingClientRect().top + window.pageYOffset - 50;
                window.scrollTo({ top: top, behavior: \'smooth\' });
            } else {
                el.scrollIntoView({ behavior: \'smooth\' });
            }
        })();
        return false;
    ">'
    . '(<u>посмотреть адрес на карте</u>)'
    . '</a>';


$data['specialist'] .= <<<EOF
    <div class="city">
        <span>Город:</span> {$specialist['city']}{$metroPart}{$mapLink}
    </div>
EOF;

// Образование
$data['specialist'] .= education($specialist);

// Опыт работы
$data['specialist'] .= experience($specialist);

//Кнопка "Показать контакты"
if (
    (
        (!empty($other['email']) && trim($other['email']) !== '') ||
        !empty($inner_contacts)
    )
) {
    $data['specialist'] .= <<<EOF
        <button class="contacts-button"><img src="/assets/img/contacts8.svg"> Записаться</button>
    EOF;
}
// Описание
if (!is_null($specialist['description']) && $specialist['description']) {
    $description = html_entity_decode(string: htmlspecialchars_decode($specialist['description']));
    $data['specialist'] .= <<<EOF
        <div class="specialist-description"><noindex>{$description}</noindex></div>
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
if (!empty($specialist['schedule'])) {
    $data['services'] .= block_schedule($specialist['schedule']);
}

// адреса и карта
$adress = $map_adress = [];
if (!empty($specialist['city'])) {
    $address_city = 'г. ' . $specialist['city'];
    $adress[]     = $address_city;
    $map_adress[] = $address_city;
}
if (!empty($specialist['metro'])) {
    // метро в общем списке, но не на карте
    $adress[] = 'м. ' . $specialist['metro'];
}
if (!empty($specialist['street'])) {
    $adress[]     = $specialist['street'];
    $map_adress[] = $specialist['street'];
}
if (!empty($specialist['house'])) {
    $adress[]     = $specialist['house'];
    $map_adress[] = $specialist['house'];
}

$how_to_get = '';
if (!empty($specialist['how_to_get'])) {
    $how_to_get = $specialist['how_to_get'];
}

$adress     = implode(', ', $adress);
$map_adress = implode(', ', $map_adress);

// Вывод адресов и карты
if ($map_adress !== '') {
    $data['services'] .= block_address($adress, $map_adress, $how_to_get, $specialist['coordinates']);
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

if (isset($_SESSION['user'])) {
    $data['user_id'] = "<input type='hidden' name='user_id' value='{$_SESSION['user']['id']}'>";
}

$data['input_specialist_id'] = "<input type='hidden' name='specialist' value='{$specialist['id']}'>";


$excluded_blocks = [];
$has_tariff_2_or_3 = array_intersect(array_column($connected_tariffs, 'tariff_id'), [2, 3]);

// Проверяем, есть ли тарифы 2 или 3
$has_tariff_2_or_3 = array_intersect(array_column($connected_tariffs, 'tariff_id'), [2, 3]);

// Проверяем подключенные опции
$has_option_4 = in_array(4, array_column($connected_options, 'option_id'));

// Проверяем либо тарифы, либо опции
$has_tariff_2_or_3_or_option_4 = $has_tariff_2_or_3 || $has_option_4;

if ($has_tariff_2_or_3_or_option_4) {
    switch ($specialist['classification_id']) {
        case 1:
        case 2:
        case 5:
        case 6:
        case 7:
            $excluded_blocks[] = 'lawyers';
            break;
        case 3:
        case 8:
            $excluded_blocks[] = 'experts';
            break;
        case 4:
            $excluded_blocks[] = 'notaries';
            break;
    }
}

// Порядок блоков
$block_order = match ($specialist['classification_id']) {
    1, 2, 5, 6, 7 => ['lawyers', 'experts', 'notaries'],
    3, 8 => ['experts', 'lawyers', 'notaries'],
    4 => ['notaries', 'lawyers', 'experts'],
    default => []
};

// Фильтруем порядок блоков с учётом исключений
$block_order = array_filter($block_order, fn($block) => !in_array($block, $excluded_blocks));

/**
 * Блоки специалистов внизу страницы
 */

// Заголовки блока Юристов и Адвокатов
$recommended_title_lawyers = match ($specialist['classification_id']) {
    1, 2, 5, 6, 7 => 'Юристы и адвокаты с похожей специализацией',
    3, 8 => 'Юристы и адвокаты по теме',
    4 => 'Рекомендуемые юристы и адвокаты',
    default => 'Не передано значение'
};

// Кнопки блока Юристов и Адвокатов
$recommended_button_lawyers = match ($specialist['classification_id']) {
    1, 2, 5, 6, 7 => 'Посмотреть других специалистов',
    3, 4, 8 => 'Найти юриста или адвоката',
    default => 'Не передано значение'
};

// Заголовки блока экспертов
$recommended_title_experts = match ($specialist['classification_id']) {
    1, 2, 5, 6, 7 => 'Эксперты по теме',
    3, 8 => 'Эксперты с похожей специализацией',
    4 => 'Рекомендуемые эксперты',
    default => 'Не передано значение'
};

// Кнопки блока Экспертов
$recommended_button_experts = match ($specialist['classification_id']) {
    1, 2, 4, 5, 6 => 'Найти эксперта',
    3, 8 => 'Посмотреть других специалистов',
    default => 'Не передано значение'
};

// Заголовки блока Нотариусов
$recommended_title_notaries = match ($specialist['classification_id']) {
    1, 2, 3, 5, 6, 7, 8 => 'Нотариусы рядом',
    4 => 'Другие нотариусы рядом',
    default => 'Не передано значение'
};

// Кнопки блока Нотариусов
$recommended_button_notaries = match ($specialist['classification_id']) {
    1, 2, 3, 5, 6, 7, 8 => 'Найти нотариуса',
    4 => 'Посмотреть других специалистов',
    default => 'Не передано значение'
};

// Подтягиваем список юристов и адвокатов
$lawyers_count = in_array($specialist['classification_id'], [1, 2, 5, 6, 7]) ? 9 : 8;

if ($specialist['classification_id'] != 4 && $specialist['classification_id'] != 8 && $specialist['classification_id'] != 3) {
    if (!empty($specializations)) {
        $lawyers = get_specialists_by_service(implode(',', array_keys($specializations)), $lawyers_count);
    } else {
        $lawyers = '';
    }
} else {
    $lawyers = get_specialists_by_classification([1, 2, 5, 6, 7], $lawyers_count);
}
if ($lawyers) {
    $lawyers = array_filter($lawyers, fn($item) => $item['id'] != $specialist_id);
    $lawyers = array_slice($lawyers, 0, 8);
    $lawyers = block($lawyers);
}

// Подтягиваем список экспертов
$experts_count = in_array($specialist['classification_id'], [3, 8]) ? 9 : 8;

$experts = get_specialists_by_classification([3, 8], $experts_count);
$experts = array_filter($experts, fn($item) => $item['id'] != $specialist_id);
$experts = array_slice($experts, 0, 8);
$experts = block($experts);

// Подтягиваем список специалистов
$notary_metro_id = get_specialist_metro_id($specialist_id);

$notaries_count = $specialist['classification_id'] == 4 ? 9 : 8;
$notaries = get_specialists_by_classification([4], $notaries_count, 0, $notary_metro_id);

$notaries = array_filter($notaries, fn($item) => $item['id'] != $specialist_id);
$notaries = array_slice($notaries, 0, 8);
$notaries = block($notaries);


// Инициализируем массив $data['recomended'] для избежания ошибки
if (!isset($data['recomended'])) {
    $data['recomended'] = '';
}

$block_index = 0;
// Генерация блоков с учётом исключений
foreach ($block_order as $block) {
    if (in_array($block, $excluded_blocks)) {
        continue;
    }

    $title = 'recommended_title_' . $block;
    $button = 'recommended_button_' . $block;

    if ($block == 'experts') {
        $href = 'experty';
    } elseif ($block == 'notaries') {
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
        $title = trim($$title);
        $words = explode(' ', $title);
        $lastWord = array_pop($words);
        $titleStart = implode(' ', $words);

        // ✅ Только первому блоку даём border-top
        $inline_style = ($block_index === 0) ? 'style="border-top: 1px solid #E8ECF1;"' : '';

        $data['recomended'] .= <<<EOF
        <div class="index-catalog-block on-company-page toggle-block" {$inline_style}>
            <div class="block-name company-page-name toggle-header">
                <span class="toggle-title" onclick="toggleContent(this)">
                    {$titleStart}
                    <span class="title-end-nowrap">
                        {$lastWord}
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </span>
                </span>
            </div>
            <div class="grid_flex toggle-content" style="display: none;">
                {$$block}
            </div>
            <div class="bottom-link toggle-content" style="display: none;">
                <a href="/{$href}" {$desc_class}>{$$button}</a>
                {$mobile_button}
            </div>
        </div>
        EOF;

        $block_index++;
    }
}


$page_meta = get_specialist_meta($specialist_id);

// Устанавливаем title и description
/* $HTML = html_replace([
    'title' => !empty($page_meta['title']) ? $page_meta['title'] : $name,
    'description' => !empty($page_meta['description']) ? $page_meta['description'] : ''
], $HTML); */

$HTML = html_explode($data, $HTML);

/**
 * @param array $specialist
 * @return string
 */
function experience(array $specialist): string
{
    $content = '';

    if ($specialist['foundation']) {
        $foundation = (int)$specialist['foundation'];

        if ($foundation < 1000) {
            $experience = $foundation;

            if ($experience <= 0) {
                $insert = 'менее года';
            } else {
                $insert = num_decline($experience, 'год, года, лет');
            }

            if (!$specialist['company']) {
                $content = "<span>Опыт работы:</span> {$insert}";
            }
        } else {
            if ($specialist['company']) {
                $content = "<span>Год основания:</span> {$foundation}";
            } else {
                $experience = date('Y') - $foundation;

                if ($experience <= 0) {
                    $insert = 'менее года';
                } else {
                    $insert = num_decline($experience, 'год, года, лет');
                }

                $content = "<span>Опыт работы:</span> {$insert}";
            }
        }
    }

    if ($content !== '') {
        return '<div class="year">' . $content . '</div>';
    }

    return '';
}

function education(array $specialist): string
{
    $content = '';

    if ($specialist['university'] && !$specialist['company']) {
        $education = json_decode($specialist['university']);
        if (!empty($education)) {
            $content = "<span>Образование:</span> " . implode(", ", $education);
        }
    }

    if ($content !== '') {
        return '<div class="education">' . $content . '</div>';
    }

    return '';
}
/**
 * @param array $specialist
 * @param array $specializations
 * @return array
 */
function block_ways(array $specialist, array $specializations): array
{
    $code = [
        'desctop' => '',
        'mobile'  => ''
    ];

    if ($specialist['classification_id'] != 4 && $specializations) {
        $class_link = get_classification_link($specialist['classification_id']);

        // Фильтруем только родительские специализации
        $parent_specializations = array_filter($specializations, function ($item) {
            return empty($item['p_id']);
        });

        // Сортируем по title (алфавитно)
        usort($parent_specializations, function ($a, $b) {
            // Нормализуем строки
            $titleA = mb_strtolower(trim($a['title']));
            $titleB = mb_strtolower(trim($b['title']));
        
            // Убираем точки и двойные пробелы
            $titleA = preg_replace('/[.\s]+/u', ' ', $titleA);
            $titleB = preg_replace('/[.\s]+/u', ' ', $titleB);
        
            // Проверяем "прочие"
            $isOtherA = ($a['id'] == 97) || preg_match('/^проч/i', $titleA);
            $isOtherB = ($b['id'] == 97) || preg_match('/^проч/i', $titleB);
        
            // Если одна из записей "Прочие" — она идёт последней
            if ($isOtherA && !$isOtherB) return 1;
            if (!$isOtherA && $isOtherB) return -1;
        
            // Иначе обычная сортировка по алфавиту
            return $titleA <=> $titleB;
        });

        $list = '';

        foreach ($parent_specializations as $specialization) {
            $href = "/" . $class_link . "/" . $specialization['slug'];
            $list .= "<a href='{$href}'>{$specialization['title']}</a>";
            $code['mobile'] .= '<li><a href="' . $href . '">' . $specialization['title'] . '</a></li>';
        }

        if ($list) {
            $code['desctop'] .= <<<EOF
                <div class="ways">
                    <h2 class="name">
                        Направления работы
                    </h2>
                    <div class="flex">
                        $list
                    </div>
                    <button>Посмотреть все направления</button>
                </div>
            EOF;
        }
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
                <h2 class="name">Стоимость услуг</h2>
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
        $reviews_count = isset($specialist['count_rating']) ? (int)$specialist['count_rating'] : 0;
        $reviews_text = pluralForm($reviews_count, 'отзыв', 'отзыва', 'отзывов');

        // Кликабельность только если есть отзывы
        $quantity = $reviews_count > 0
            ? '<a class="show-reviews">' . $reviews_count . ' ' . $reviews_text . '</a>'
            : '<a class="show-reviews" style="pointer-events: none; cursor: default; text-decoration: none; color: inherit;">' . $reviews_count . ' ' . $reviews_text . '</a>';

        $code['desctop'] .= <<<EOF
            <h2 class="name">
                Отзывы $title $name
            </h2>
            <span class="rating">
                    <span>
                        {$rating_html}
                    </span>
                    {$quantity}
                </span>
        EOF;

        $code['popup'] .= <<<EOF
            <div class="name">
                <div class="review-popup-name">Отзывы $title $name</div>
                <span class="rating">
                    <span>
                        {$rating_html}
                    </span>
                    <span class="text">{$reviews_count} {$reviews_text}</span>
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
                    <p class="review-text">{$review['review']}</p>
                </div>
            EOF;

            if ($key > 2) continue;

            $code['desctop'] .= <<<EOF
                <div class="item item-desctop">
                    <div class="date">
                        <span>{$date}</span>
                        {$review['author']}
                        <span class="rating"> 
                            {$rating}
                        </span>
                    </div>
                    <p class="review-text cut-text">{$review['review']}</p>
                </div>
            EOF;
        }


        $code['popup'] .= <<<EOF
            </div>
        EOF;

        $show_reviews_btn = '';
        if (count($reviews) >= 1) {
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
            <h2 class="name">
                Отзывы $title $name
            </h2>
            <p>Отзывов пока нет.</p>
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
    $output = '';
    $schedule = json_decode($schedule, true);

    if (is_array($schedule)) {
        $items = '';

        foreach ($schedule as $key => $value) {
            $start = $value['start'] ?? false;
            $end = $value['end'] ?? false;
            $b_start = $value['b_start'] ?? false;
            $b_end = $value['b_end'] ?? false;
            $vac = $value['vac'] ?? null;

            $day = match ((int)$key) {
                1 => 'Пн',
                2 => 'Вт',
                3 => 'Ср',
                4 => 'Чт',
                5 => 'Пт',
                6 => 'Сб',
                7 => 'Вс',
                default => null,
            };

            if (!$day) continue;

            // Выводим только если:
            // 1) нет vac — считаем выходным
            // или
            // 2) есть vac, но start и end заполнены
            if (!isset($vac) || ($vac !== null && $start && $end)) {
                $items .= "<div class=\"item\">";
                $items .= "<span class=\"day\">{$day}</span>";

                if (!isset($vac)) {
                    $items .= "<span class=\"time day-off\">Выходной</span>";
                } else {
                    $items .= "<span class=\"time\">{$start} – {$end}</span>";

                    if ($b_start && $b_end) {
                        $items .= "<span class=\"break\">Перерыв с {$b_start} до {$b_end}</span>";
                    }
                }

                $items .= "</div>";
            }
        }

        if ($items !== '') {
            $output = <<<EOF
                <div class="days">
                    <h2 class="name">График работы</h2>
                    {$items}
                </div>
            EOF;
        }
    }

    return $output;
}

/**
 * @param string $address
 * @return string
 */
function block_address(string $address, string $map_adress, string $how_to_get, string|null $coordinates): string
{
    $code = '';

    if ($how_to_get) {
        $how_to_get = '<p>' . $how_to_get . '</p>';
    }
    if (!is_null($coordinates) && $coordinates) {
        list($longitude, $latitude) = explode(' ', $coordinates);
        // Создаем HTML-код для отображения карты с полученными координатами
        $code = '
            <div class="map">
                <h2 class="name">
                    Адрес на карте
                </h2>
                <div class="mmap">
                    <div id="specialist_adress_map" style="max-height: 480px; height: 100%;">
                    </div>
                    <script src="https://api-maps.yandex.ru/2.1/?apikey=b0c770d0-eb80-4f0e-bb33-f3d1bb289c95&lang=ru_RU" type="text/javascript"></script>
                    <script type="text/javascript">
                        ymaps.ready(init);
                        function init() {
                            var myMap = new ymaps.Map("specialist_adress_map", {
                                center: [' . $latitude . ', ' . $longitude . '],
                                zoom: 16
                            }, {
                                searchControlProvider: "yandex#search"
                            });
                            var myCollection = new ymaps.GeoObjectCollection(); 
                            var myPlacemark = new ymaps.Placemark([
                                ' . $latitude . ', ' . $longitude . '
                            ], {
                                balloonContent: "' . $map_adress . '"
                            }, {
                                preset: "islands#icon",
                                iconColor: "#FF8139"
                            });
                            myCollection.add(myPlacemark);
                            myMap.geoObjects.add(myCollection);
                        }
                    </script>
                </div>';
    } else if ($address) {
        $code = '
            <div class="map">
                <h2 class="name">
                    Адрес
                </h2>';
    }
    if ($address) {
        $code .= '  
                <p class="grey">Как добраться:</p>
                <div class="adress" id="copy-adress">
                    ' . $address . '
                </div>
                <textarea id="content">' . $address . '</textarea>
                ' . $how_to_get . '
                <a class="open-yandex-nav" href="yandexnavi://map_search?text=' . $map_adress . '"><img src="/assets/img/map-button1.svg"> Открыть в Яндекс.Навигаторе</a>
                <a class="open-yandex-map" href="https://yandex.ru/maps/?text=' . $map_adress . '"><img src="/assets/img/map-button2.svg"> Открыть в Яндекс.Картах</a>
            </div>';
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
                <h2 class="block-name">Команда</h2>
                <div class="staff__grid">
        EOF;

        foreach ($employees as $employee) {
            $code .= <<<EOF
                <div class="item">
                    <div class="img__wrapper lazy-bg" data-bg="/uploads/employee/{$employee['photo']}"></div>
                    <div>
                        <span>{$employee['name']} {$employee['lastname']}</span>
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
                <div class="articles">
                    <h2 class="name"><a href="/journal/id{$blog['id']}">Блог $title $name</a></h2>
            EOF;


            foreach ($articles as $article) {
                $code .= '<div class="item2">
                <div class="flex post-data">
                    <a href="/journal/' . $article['service_slug'] . '" class="cat_' . $article['post_type'] . '"><span>' . $article['service_title'] . '</span></a>
                    <div class="date">' . date('d.m.Y', strtotime($article['date'])) . '</div>
                </div>
                <a href="/journal/id' . $article['blog_id'] . '/' . $article['alias'] . '" class="name">' . strip_tags(html_entity_decode($article['title'])) . '</a>';
                /* if ($article['photo'] != '' && !is_null($article['photo'])) {
                    $code .= '<a href="/journal/id' . $article['blog_id'] . '/' . $article['alias'] . '" class="image">
                    <img src="/uploads/articles/' . $article['photo'] . '">
                </a>';
                } */
                $code .= '<a class="content cut-text-blogs" href="/journal/id' . $article['blog_id'] . '/' . $article['alias'] . '">' . strip_tags(html_entity_decode($article['content'])) . '</a>';
                $author_name = $specialist['company'] ? $specialist['company'] : $specialist['name'] . ' ' . $specialist['lastname'];
                $code .= '<a href="/journal/id' . $article['blog_id'] . '" class="author">' . $author_name . '</a>
                </div>';
            }

            $code .= <<<EOF
                        <a href="/journal/id{$blog['id']}" class="bottom-button">Посмотреть все записи</a>
                    </div>
            EOF;
        } else {
            $code .= <<<EOF
                <div class="articles">
                    <h2 class="name"><a href="/journal/id{$blog['id']}">Блог $title $name</a></h2>
                    <p class="empty-blog">В блоге еще нет записей.</p>
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
                <div class="slider-header">
                    <h2 class="block-name">Документы, достижения</h2>
                    <div class="slider-arrows">
                        <button class="slick-prev" type="button">
                            <img src="/assets/img/arrow-left.svg" alt="prev">
                        </button>
                        <button class="slick-next" type="button">
                            <img src="/assets/img/arrow-right.svg" alt="next">
                        </button>
                    </div>
                </div>
                <div class="list">
        EOF;

        foreach ($documents as $document) {
            $code .= <<<EOF
                <div class="item">
                    <div class="img-block lazy-bg" data-bg="/uploads/documents/{$document['file']}"></div>
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
 * Генерация JSON-LD для специалиста или компании
 *
 * @param array $specialist
 * @return array
 */
function generateSpecialistJsonLd(array $specialist, ?array $other = null): array
{
    // Получаем все специализации специалиста
    $specializations = get_specialist_specialization($specialist['id']);

    // Берем только названия (title) и сортируем
    $knowsAbout = [];
    if ($specializations) {
        // Оставляем только родительские специализации
        $parent_specializations = array_filter($specializations, function ($item) {
            return empty($item['p_id']);
        });

        usort($parent_specializations, function ($a, $b) {
            return mb_strtolower($a['title']) <=> mb_strtolower($b['title']);
        });

        $knowsAbout = array_map(fn($spec) => $spec['title'], $parent_specializations);
    }

    // Формируем адрес для JSON-LD
    $streetAddress = [];
    if (!empty($specialist['city'])) {
        $streetAddress[] = 'г. ' . $specialist['city'];
    }
    if (!empty($specialist['metro'])) {
        $streetAddress[] = 'м. ' . $specialist['metro'];
    }
    if (!empty($specialist['street'])) {
        $streetAddress[] = $specialist['street'];
    }
    if (!empty($specialist['house'])) {
        $streetAddress[] = $specialist['house'];
    }
    $streetAddressStr = implode(', ', $streetAddress);

    $sameAs = [];
    if (!empty($other['tg'])) {
        $sameAs[] = $other['tg'];
    }
    if (!empty($other['vk'])) {
        $sameAs[] = $other['vk'];
    }
    if (!empty($other['dzen'])) {
        $sameAs[] = $other['dzen'];
    }

    $ratingValue = isset($specialist['rating'])
        ? number_format((float)$specialist['rating'], 1, '.', '')
        : 0.0;

    // Основной объект
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "LegalService",
        "url" => $specialist['url'] ?? "https://urfaq.ru/specialist/id{$specialist['id']}",
        "image" => $specialist['photo'] ? "https://urfaq.ru/uploads/avatars/" . $specialist['photo'] : null,
        "name" => $specialist['company'] ?? $specialist['name'] . ' ' . $specialist['lastname'],
        "additionalType" => $specialist['classification_name'] ?? $specialist['classification'] ?? "Юрист",
        "description" => strip_tags(htmlspecialchars_decode($specialist['description']) ?? ''),
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => $streetAddressStr,
            "addressLocality" => $specialist['city'] ?? '',
            "addressCountry" => "RU",
            "description" => $specialist['how_to_get'] ?? ''
        ],
        "hasMap" => !empty($specialist['coordinates'])
            ? 'https://yandex.ru/maps/?whatshere[point]=' . str_replace(' ', ',', $specialist['coordinates'])
            : ($specialist['map_url'] ?? ''),
        "areaServed" => [
            "@type" => "State",
            "name" => $specialist['city'] ?? ''
        ],
        "aggregateRating" => isset($specialist['rating'], $specialist['count_rating']) ? [
            "@type" => "AggregateRating",
            "ratingValue" => $ratingValue,
            "reviewCount" => $specialist['count_rating']
        ] : null,
        "knowsAbout" => $knowsAbout,
        "contactPoint" => [
            "@type" => "ContactPoint",
            "contactType" => "Записаться",
            "telephone" => $specialist['phone'] ?? '',
            "email" => $specialist['email'] ?? '',
            "url" => $other['site'] ?? '',
            "availableLanguage" => ["Russian"],
            "areaServed" => "RU"
        ],
        "sameAs" => $sameAs,
        /* "blogPost" => !empty($specialist['blog']) ? [
            "@type" => "Article",
            "url" => $specialist['blog']['url'] ?? '',
            "datePublished" => $specialist['blog']['date'] ?? '',
            "articleSection" => $specialist['blog']['section'] ?? '',
            "headline" => $specialist['blog']['title'] ?? '',
            "description" => $specialist['blog']['description'] ?? '',
            "author" => $specialist['company'] ?? $specialist['name'] . ' ' . $specialist['lastname']
        ] : null, */
        "hasCredential" => []
    ];

    // Заполняем услуги / предложения
    $services = get_specialist_service($specialist['id'] ?? 0);
    if (!empty($services) && is_array($services)) {
        $offers = [];
        foreach ($services as $s) {
            $offer = ["@type" => "Offer"];
            if (!empty($s['name'])) $offer['name'] = $s['name'];
            if (isset($s['price']) && $s['price'] !== '') {
                $offer['price'] = (float)$s['price'];
                $offer['priceCurrency'] = "RUB";
            } elseif (!empty($s['price_text'])) {
                $offer['description'] = $s['price_text'];
            }
            // serviceType — корректное поле для Offer
            if (!empty($specialist['classification_name'])) {
                $offer['serviceType'] = $specialist['classification_name'];
            } elseif (!empty($s['service_type'])) {
                $offer['serviceType'] = $s['service_type'];
            }
            $offers[] = $offer;
        }
        $jsonLd['makesOffer'] = (count($offers) === 1) ? $offers[0] : $offers;
    }

    if (!empty($specialist['foundation'])) {
        $f = (int)$specialist['foundation'];
        if ($f >= 1000 && $f <= (int)date('Y')) {
            $jsonLd['foundingDate'] = (string)$f;
        }
    }

    if (!empty($specialist['schedule'])) {
        $schedule = json_decode($specialist['schedule'], true);
        if (is_array($schedule)) {
            $dayMap = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
            $ohs = [];

            foreach ($schedule as $key => $value) {
                $d = (int)$key;
                if (!isset($dayMap[$d])) continue;

                $start = $value['start'] ?? null;
                $end = $value['end'] ?? null;
                $vac = $value['vac'] ?? null;

                // Выводим только если рабочий день
                if (isset($vac) && $start && $end) {
                    $ohs[] = [
                        "@type" => "OpeningHoursSpecification",
                        "dayOfWeek" => "https://schema.org/" . $dayMap[$d],
                        "opens" => $start,
                        "closes" => $end
                    ];

                    $shortDay = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'][$d - 1];
                }
            }

            if (!empty($ohs)) $jsonLd['openingHoursSpecification'] = $ohs;
        }
    }

    // Заполняем документы / сертификаты
    if (!empty($specialist['document'])) {
        $documents = json_decode($specialist['document'], true);
        foreach ($documents as $doc) {
            $jsonLd['hasCredential'][] = [
                "@type" => "EducationalOccupationalCredential",
                "name" => $doc['name'] ?? '',
                "image" => isset($doc['file']) ? "https://urfaq.ru/uploads/documents/" . $doc['file'] : null,
            ];
        }
    }

    if (!empty($specialist['company']) && ($employees = get_company_employees($specialist['company_id'] ?? 0))) {
        $jsonLd['employee'] = [];
        foreach ($employees as $employee) {
            $jsonLd['employee'][] = array_filter([
                "@type" => "Person",
                "name" => trim(($employee['name'] ?? '') . ' ' . ($employee['lastname'] ?? '')),
                "jobTitle" => $employee['post'] ?? null,
                "image" => !empty($employee['photo']) ? "https://urfaq.ru/uploads/employee/" . $employee['photo'] : null
            ], function ($v) {
                return !is_null($v) && $v !== '';
            });
        }
    }

    /* $blog = get_specialist_blog($specialist['id']);
    if ($blog) {
        $articles = get_blog_articles($blog['id'], 0, 1); // берем последнюю статью
        if ($articles && count($articles) > 0) {
            $article = $articles[0];
            $author_name = $specialist['company']
                ? $specialist['company']
                : $specialist['name'] . ' ' . $specialist['lastname'];

            $jsonLd['blogPost'] = [
                "@type" => "Article",
                "url" => "/journal/id{$article['blog_id']}/{$article['alias']}",
                "datePublished" => date('Y-m-d', strtotime($article['date'])),
                "articleSection" => $article['service_title'] ?? '',
                "headline" => strip_tags(html_entity_decode($article['title'])),
                "description" => strip_tags(html_entity_decode($article['content'])),
                "author" => $author_name
            ];
        }
    } */

    return $jsonLd;
}

/**
 * Генерация JSON-LD для хлебных крошек (BreadcrumbList) специалиста
 *
 * @param array $specialist Данные специалиста
 * @return string
 */
function generateSpecialistBreadcrumbJsonLd(array $specialist): string
{
    if (empty($specialist)) return '';

    $classificationId = isset($specialist['classification_id']) ? (int)$specialist['classification_id'] : null;

    // Определяем категорию и slug
    $categoryMap = [
        1 => ['name' => 'Юристы и адвокаты', 'slug' => 'yuristy-i-advokaty'],
        2 => ['name' => 'Юристы и адвокаты', 'slug' => 'yuristy-i-advokaty'],
        5 => ['name' => 'Юристы и адвокаты', 'slug' => 'yuristy-i-advokaty'],
        6 => ['name' => 'Юристы и адвокаты', 'slug' => 'yuristy-i-advokaty'],
        7 => ['name' => 'Юристы и адвокаты', 'slug' => 'yuristy-i-advokaty'],
        3 => ['name' => 'Эксперты', 'slug' => 'experty'],
        8 => ['name' => 'Эксперты', 'slug' => 'experty'],
        4 => ['name' => 'Нотариусы', 'slug' => 'notariusy'],
    ];

    $category = $categoryMap[$classificationId] ?? ['name' => 'Специалисты', 'slug' => 'specialisty'];

    $breadcrumbs = [
        ['name' => 'Главная', 'url' => '/'],
        ['name' => $category['name'], 'url' => '/' . $category['slug']]
    ];

    $itemListElements = [];
    foreach ($breadcrumbs as $i => $crumb) {
        $itemListElements[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => strip_tags(htmlspecialchars_decode($crumb['name'])),
            'item' => full_url($crumb['url'])
        ];
    }

    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $itemListElements
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}

/**
 * Генерация JSON-LD для всех отзывов специалиста как один объект Review
 *
 * @param array $specialist
 * @return string
 */
function generateSpecialistReviewsJsonLd(array $specialist): string
{
    $reviews = get_specialist_reviews($specialist['id'] ?? 0);
    if (empty($reviews)) return '';

    $nestedReviews = [];

    foreach ($reviews as $review) {
        $authorName = $review['author'] ?? 'Аноним';
        $reviewDate = date('Y-m-d', strtotime($review['date'] ?? 'now'));

        $nestedReviews[] = [
            "@type" => "Review",
            "author" => [
                "@type" => "Person",
                "name" => $authorName
            ],
            "datePublished" => $reviewDate,
            "reviewBody" => strip_tags($review['review'] ?? ''),
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => isset($review['rating']) ? (int)$review['rating'] : 0,
            ]
        ];
    }

    $ratingValue = isset($specialist['rating'])
        ? number_format((float)$specialist['rating'], 1, '.', '')
        : 0.0;
    // Один главный объект Review, внутри массив reviews
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "Review",
        "name" => $specialist['company'] ?? trim(($specialist['lastname'] ?? '') . ' ' . ($specialist['name'] ?? '')),
        "reviewRating" => [
            "@type" => "AggregateRating",
            "ratingValue" => $ratingValue,
            "reviewCount" => $specialist['count_rating'] ?? 0,
        ],
        "reviews" => $nestedReviews
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}

function generateSpecialistImageJsonLd(array $specialist): string
{
    if (empty($specialist['photo'])) return '';

    $name = $specialist['company'] ?? trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));

    $imageLd = [
        "@context" => "https://schema.org",
        "@type" => "ImageObject",
        "contentUrl" => "https://urfaq.ru/uploads/avatars/" . $specialist['photo'],
        "name" => $name
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($imageLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}

$blog = get_specialist_blog($specialist['id']);
$posts = $blog ? get_blog_articles($blog['id'], 0, 2) : [];

/**
 * Генерация JSON-LD для публикаций автора (CreativeWorkSeries) с workExample
 *
 * @param array $posts Статьи на странице (максимум 2)
 * @param array $specialist Информация об авторе
 * @return string
 */
function generateAuthorPublicationsJsonLd(array $posts, array $specialist): string
{
    $publicationCount = count($posts);
    $authorName = $specialist['company'] ?? trim($specialist['name'] . ' ' . $specialist['lastname']);

    $workExamples = array_map(function ($post) use ($specialist) {
        $authorType = !empty($specialist['company']) ? "Organization" : "Person";
        $authorName = !empty($specialist['company'])
            ? $specialist['company']
            : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));

        return [
            "@type" => "Article",
            "url" => full_url($post['url'] ?? ''),
            "datePublished" => date('Y-m-d', strtotime($post['date'] ?? 'now')),
            "articleSection" => $post['service_title'] ?? '',
            "headline" => strip_tags(htmlspecialchars_decode($post['title']) ?? ''),
            "description" => strip_tags(htmlspecialchars_decode($post['content']) ?? ''),
            "author" => [
                "@type" => $authorType,
                "name" => $authorName
            ]
        ];
    }, $posts);

    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "CreativeWorkSeries",
        "name" => "Публикации на странице автора",
        "description" => "Опубликованные статьи на странице автора ({$publicationCount})",
        "workExample" => $workExamples
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}

// Пример использования
$specialist_jsonld = generateSpecialistJsonLd($specialist, $other);
$specialist_breadcrumb_jsonld = generateSpecialistBreadcrumbJsonLd($specialist);
$specialist_review_jsonld = generateSpecialistReviewsJsonLd($specialist);
echo $specialist_breadcrumb_jsonld;
echo $specialist_review_jsonld;
echo generateSpecialistImageJsonLd($specialist);
echo generateAuthorPublicationsJsonLd($posts, $specialist);

// Для вставки в страницу
echo '<script type="application/ld+json">' . json_encode($specialist_jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
