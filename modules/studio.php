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
        $slugUrl = '/studio/' . $specialist['slug'];
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
    $title_reviews = 'о студии йоги';
    $title_blog = "студии йоги";

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
        $title_reviews = 'о студии йоги';
        $title_blog = "студии йоги";
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
        $title_reviews = 'о студии йоги';
        $title_blog = "студии йоги";
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
    $data['breadcrumbs'] = '<a href="/yoga-studii">Студии</a><span>/</span>';
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
            <span>Записаться на занятие</span>
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
            $inner_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['site'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts3.svg"> <span>Сайт</span></a></li>';
        }
        /* if ($other['tg']) {
            $inner_contacts .= '<li class="no-sticky"><a href="' . $other['tg'] . '" rel="nofollow"><img src="/assets/img/contacts4.svg"> Телеграм</a></li>';
        } */
        if ($other['vk']) {
            $inner_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['vk'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts5.svg"> Вконтакте</a></li>';
        }
        if ($other['dzen']) {
            $inner_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['dzen'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts6.svg"> Яндекс.Дзен</a></li>';
        }
        if ($other['max']) {
            $inner_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['max'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts-max-hover.svg"> MAX</a></li>';
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
            $opt_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['site'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts3.svg"> <span>Сайт</span></a></li>';
        }
        /* if ($other['tg']) {
            $opt_contacts .= '<li class="no-sticky"><a href="' . $other['tg'] . '" rel="nofollow"><img src="/assets/img/contacts4.svg"> Телеграм</a></li>';
        } */
        if ($other['vk']) {
            $opt_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['vk'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts5.svg"> Вконтакте</a></li>';
        }
        if ($other['dzen']) {
            $opt_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['dzen'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts6.svg"> Яндекс.Дзен</a></li>';
        }
        if ($other['max']) {
            $opt_contacts .= '<li class="no-sticky"><a target="_blank" href="' . $other['max'] . '" rel="noopener noreferrer nofollow"><img src="/assets/img/contacts-max-hover.svg"> MAX</a></li>';
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

    $contacts_block = <<<EOF
        <div class="contacts-block">
            <div id="contacts">
                {$contacts_list}
            </div>
        </div>
    EOF;

    $mobile_contacts_block = <<<EOF
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
                color: #F2606C;
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
$fav_all = array_map('intval', $_SESSION['favorite']['all'] ?? []);
$fav_class = in_array((int)$specialist_id, $fav_all, true) ? 'active' : '';

$tariff3Connected = false;
foreach ($connected_tariffs as $tariff) {
    if ($tariff['tariff_id'] == 3) {
        $tariff3Connected = true;
    }
}
$rocketStyle = $tariff3Connected ? 'display: block;' : 'display: none;';
$topSpecialistBadge = '';
if ($tariff3Connected) {
    $topSpecialistBadge = <<<HTML
             <div style="position: absolute; left: 15px; top: 30px; max-width: 118px; z-index: 45; width: 100%; height: 28px; background: #F8A3AA; border-radius: 40px; color: #fff; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 14px; font-weight: 300; pointer-events: none;">
                    ТОП-cтудия
                    <img src='/assets/img/cabinet/fire-card.svg' alt="">
                </div>
HTML;
    $topSpecialistBadgeMobile = <<<HTML
             <div style="position: absolute; left: 20px; top: 15px; max-width: 118px; z-index: 25; width: 100%; height: 28px; background: #F8A3AA; border-radius: 40px; color: #fff; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 14px; font-weight: 300; pointer-events: none;">
                    ТОП-cтудия
                    <img src='/assets/img/cabinet/fire-card.svg' alt="">
                </div>
HTML;
}

$blog = get_specialist_blog($specialist['id']);
if ($blog) {
    $articles = get_blog_articles($blog['id'], 0, 2);
    if ($articles) {
        $icon_blog .= '<i class="company-blog-btn"></i>';
    }
}

// 1. Декодируем галерею специалиста
$gallery_photos = json_decode($specialist['gallery'] ?? '[]', true) ?: [];

// 2. Считаем общее количество фото (1 аватар + количество фото в галерее)
$has_avatar = !empty($specialist['photo']);
$total_photos = ($has_avatar ? 1 : 0) + count($gallery_photos);

// 3. Условие для кнопки: показываем только если фотографий строго больше 5
$more_photos_label = ($total_photos > 5) ? '<span class="more-photos-label">Смотреть все фото</span>' : '';

// 4. Готовим пути для боковых фото (нам нужно ровно 4 штуки)
$side_bg = ['', '', '', ''];
for ($i = 0; $i < 4; $i++) {
    if (isset($gallery_photos[$i])) {
        $side_bg[$i] = '/uploads/gallery/' . htmlspecialchars($gallery_photos[$i], ENT_QUOTES);
    }
}
$main_photo_bg = $has_avatar ? '/uploads/avatars/' . htmlspecialchars($specialist['photo'], ENT_QUOTES) : '';

$all_photos = [];
if (!empty($specialist['photo'])) {
    $all_photos[] = '/uploads/avatars/' . htmlspecialchars($specialist['photo'], ENT_QUOTES);
}
foreach ($gallery_photos as $photo) {
    $all_photos[] = '/uploads/gallery/' . htmlspecialchars($photo, ENT_QUOTES);
}

// 2. Генерируем слайды
$swiper_slides = '';
foreach ($all_photos as $photo_path) {
    $swiper_slides .= <<<EOF
    <div class="swiper-slide">
        <img src="{$photo_path}" alt="Фото студии йоги">
    </div>
EOF;
}

// 1. Проверяем главную картинку
$main_img_html = (!empty($main_photo_bg) && $main_photo_bg !== '#')
    ? '<img src="' . $main_photo_bg . '" alt="Студия йоги" class="grid-image">'
    : '';

// 2. Проверяем боковые картинки в цикле (чтобы не плодить копипаст)
$side_img_html = [];
for ($i = 0; $i < 4; $i++) {
    $side_img_html[$i] = (!empty($side_bg[$i]) && $side_bg[$i] !== '#')
        ? '<img src="' . $side_bg[$i] . '" alt="Студия йоги" class="grid-image">'
        : '';
}

// 3. Выводим СЕТКУ (теперь подставляем уже проверенные HTML-элементы)
$data['specialist'] .= <<<EOF
<div class="company-page-desktop-gallery">
    {$topSpecialistBadge}
    <div class="image-block-grid">
        <div class="grid-item main-photo" data-index="0">
            {$main_img_html}
        </div>
        
        <div class="grid-side-column">
            <div class="grid-item side-photo" data-index="1">
                {$side_img_html[0]}
            </div>
            <div class="grid-item side-photo" data-index="2">
                {$side_img_html[1]}
            </div>
            <div class="grid-item side-photo" data-index="3">
                {$side_img_html[2]}
            </div>
            <div class="grid-item side-photo" data-index="4">
                {$side_img_html[3]}
                {$more_photos_label}
            </div>
        </div>
    </div>
</div>

<div class="company-page-mobile-gallery">
    <div class="swiper mobile-gallery-swiper">
        <div class="swiper-wrapper">
            {$swiper_slides}
        </div>
        <div class="swiper-pagination mobile-gallery-pagination"></div>
        
        ($topSpecialistBadgeMobile)
    </div>
</div>

<div class="popup" id="gallerySliderPopup">
    <div class="bg"></div>
    
    <div class="window gallery-window">
        <div class="swiper gallery-swiper-container">
            <div class="swiper-wrapper">
                {$swiper_slides}
            </div>
            
           <div class="close-slider-btn" title="Закрыть">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="2" y2="22"></line>
                    <line x1="2" y1="2" x2="22" y2="22"></line>
                </svg>
            </div>
            
            <div class="swiper-button-prev gallery-arrow-prev"></div>
            <div class="swiper-button-next gallery-arrow-next"></div>
            
            <div class="swiper-pagination gallery-pagination"></div>
        </div>
    </div>
</div>

<div class="popup" id="roomsSliderPopup">
    <div class="bg"></div>
    
    <div class="window gallery-window">
        <div class="swiper rooms-swiper-container">
            <div class="swiper-wrapper">
                <!-- слайды подставляются через JS при клике на конкретный зал -->
            </div>
            
           <div class="close-slider-btn" title="Закрыть">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="2" y2="22"></line>
                    <line x1="2" y1="2" x2="22" y2="22"></line>
                </svg>
            </div>
            
            <div class="swiper-button-prev rooms-popup-arrow-prev"></div>
            <div class="swiper-button-next rooms-popup-arrow-next"></div>
            
            <div class="swiper-pagination rooms-popup-pagination"></div>
        </div>
    </div>
</div>

<div class="popup" id="documentPhotoPopup">
    <div class="bg"></div>

    <div class="window document-photo-window">
        <img src="" alt="Документ специалиста" class="document-photo-img">

        <div class="close-slider-btn" title="Закрыть">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="2" y2="22"></line>
                <line x1="2" y1="2" x2="22" y2="22"></line>
            </svg>
        </div>
    </div>
</div>

<div class="popup" id="stuffPhotoPopup">
    <div class="bg"></div>

    <div class="window stuff-photo-window">
        <img src="" alt="Документ специалиста" class="stuff-photo-img">

        <div class="close-slider-btn" title="Закрыть">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="2" y2="22"></line>
                <line x1="2" y1="2" x2="22" y2="22"></line>
            </svg>
        </div>
    </div>
</div>

<div class="company-page_content">
{$contacts_block}
<div class="top-info fav">
    <div class="company-actions">
        {$icon_blog}
        <i class="company-fav {$fav_class}" 
           data-id="{$specialist_id}" 
           data-cat="{$specialist['classification_id']}">
        </i>
    </div>
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
                    <input class="inp" name="name" maxlength="30" type="text" placeholder="Ваше имя (не более 30 символов)" />
                    <div class="invalid-feedback"></div>
                </div>
              <div class="field">
                  <input type="text" class="phone-input-reg" name="contactInfo" placeholder="Номер вашего телефона для связи" />
                  <div class="invalid-feedback"></div>
                  <input type="hidden" id="specialist_mail" value="{$email}">
                  <input type="hidden" name="specialist_id" value="{$specialist_id}">
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
                    <input class="captcha-input" id="captcha_code" name="captcha_code" type="text" placeholder="Введите цифры" autocomplete="off" />
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
              <input class="captcha-input" id="captcha_code_conf" name="captcha_code_conf" type="text" placeholder="Введите цифры" autocomplete="off" />
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
            <input class="captcha-input" id="captcha_code_review" name="captcha_code_review" type="text" placeholder="Введите цифры" autocomplete="off" />
            <input type="hidden" id="captcha_captcha_id_review" name="captcha_id_review" value="' . $randId . '" />
        </div>
    </div>
    <div class="captcha-error"></div>
</div>
';
$HTML_ = explode('<!--data:captcha_review-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:captcha_review-->{$data['captcha_review']}<!--data:captcha_review-->" . $HTML_[2];


$data['h1_hidden_title'] = '<h1 class="dn">' . $name . '</h1>';

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
        (!empty($other['email']) && trim($other['email']) !== '')
    )
) {
    $data['specialist'] .= <<<EOF
        <button class="contacts-button"><img src="/assets/img/contacts8.svg"> Записаться</button>
    EOF;
}
$all_equipments = get_all_equipment();
$selected_equipment = get_specialist_equipment($specialist['id']);

$equipments = [];
$amenities = [];

// 1. Распределяем выбранные элементы по категориям
foreach ($all_equipments as $item) {
    if (array_key_exists($item['id'], $selected_equipment)) {
        $category = trim($item['category']);

        if (in_array($category, ['equipment', 'equipments'])) {
            $equipments[] = $item;
        } elseif (in_array($category, ['amenity', 'amenities'])) {
            $amenities[] = $item;
        }
    }
}

// 2. Сортируем обе группы по алфавиту
$sortByName = function ($a, $b) {
    return mb_strtolower(trim($a['name'])) <=> mb_strtolower(trim($b['name']));
};

usort($equipments, $sortByName);
usort($amenities, $sortByName);

// 3. Функция для генерации HTML иконок
$renderIcons = function ($items) {
    $html = '';
    foreach ($items as $item) {
        if (!empty($item['img'])) {
            if (strpos($item['img'], '<svg') !== false) {
                $html .= '<div class="studio-mini-icon-item studio-mini-icon-item--svg" title="' . htmlspecialchars($item['name']) . '">' . $item['img'] . '</div>';
            } else {
                $icon_file = !empty($item['img_pink']) ? $item['img_pink'] : $item['img'];
                $html .= '<img class="studio-mini-icon-item" src="/assets/img/equipment/' . htmlspecialchars($icon_file) . '" alt="' . htmlspecialchars($item['name']) . '" title="' . htmlspecialchars($item['name']) . '">';
            }
        }
    }
    return $html;
};

$all_features = array_merge($amenities, $equipments);

$mobile_html = $renderIcons($all_features);

if (!empty($mobile_html)) {
    $data['specialist'] .= <<<EOF
    <div class="studio-mini-icons-mobile">
        <div class="studio-mini-icons-row studio-mini-icons-row--mobile">
            {$mobile_html}
        </div>
    </div>
    EOF;
}

$amenities_html = $renderIcons($amenities);
$equipment_html = $renderIcons($equipments);

// Проверяем: если есть ХОТЯ БЫ удобства ИЛИ ХОТЯ БЫ оборудование
if (!empty($amenities_html) || !empty($equipment_html)) {

    // Открываем общий контейнер
    $data['specialist'] .= '<div class="studio-mini-icons-desktop">';

    // Если есть удобства — выводим их строку
    if (!empty($amenities_html)) {
        $data['specialist'] .= '<div class="studio-mini-icons-row">' . $amenities_html . '</div>';
    }

    // Если есть оборудование — выводим его строку
    if (!empty($equipment_html)) {
        $data['specialist'] .= '<div class="studio-mini-icons-row">' . $equipment_html . '</div>';
    }

    // Гарантированно закрываем общий контейнер
    $data['specialist'] .= '</div>';
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

$block_equipment = block_equipment($specialist);
$data['specialist'] .= $block_equipment['desctop'];

// Прайсы
$prices = block_price($specialist);
$data['prices'] = $prices['desctop'];
$data['mobile_contacts'] = $mobile_contacts_block;
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
    $data['services'] .= block_address($adress, $map_adress, $how_to_get, $specialist['coordinates'], $specialist, $name, $rating);
}


// Сотрудники
$data['employees'] = block_employees($specialist);

// Залы
$specialist_rooms = json_decode($specialist['rooms'] ?? '[]', true) ?: [];

$data['rooms'] = block_studio_rooms($specialist_rooms);

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
    3, 8 => 'Студии йоги рядом',
    4 => 'Рекомендуемые эксперты',
    default => 'Не передано значение'
};

// Кнопки блока Экспертов
$recommended_button_experts = match ($specialist['classification_id']) {
    1, 2, 4, 5, 6 => 'Найти эксперта',
    3, 8 => 'Посмотреть другие студии',
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
        $lawyers = get_random_specialists_by_service(implode(',', array_keys($specializations)), $lawyers_count);
    } else {
        $lawyers = get_random_recomendations_by_classification([1, 2, 5, 6, 7], $lawyers_count);
    }
} else {
    $lawyers = get_random_recomendations_by_classification([1, 2, 5, 6, 7], $lawyers_count);
}

if ($lawyers) {
    // Исключаем текущего специалиста
    $lawyers = array_filter($lawyers, fn($item) => $item['id'] != $specialist_id);
    $lawyers = array_slice($lawyers, 0, 8);

    // Подгружаем все специализации для рандома
    $lawyers = loadSpecializationList($lawyers);

    // Выводим через block с рандомной специализацией
    $lawyers = block($lawyers, false, true);
}

// Эксперты
$specialist_metro_id = get_specialist_metro_id($specialist_id);

$experts_count = in_array($specialist['classification_id'], [3, 8]) ? 9 : 8;

$experts = get_random_recomendations_by_classification([3, 8], $experts_count, 0, $specialist_metro_id);

if ($experts) {
    $experts = array_filter($experts, fn($item) => $item['id'] != $specialist_id);
    $experts = array_slice($experts, 0, 8);

    $experts = loadSpecializationList($experts);
    $experts = block($experts, false, true);
}


// Подтягиваем список специалистов
$notary_metro_id = get_specialist_metro_id($specialist_id);

$notaries_count = $specialist['classification_id'] == 4 ? 9 : 8;
$notaries = get_random_recomendations_by_classification([4], $notaries_count, 0, $notary_metro_id);

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
        $href = 'yoga-studii';
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

        // Только первому блоку даём border-top
        $inline_style = ($block_index === 0) ? 'style="border-top: 1px solid #E8ECF1;"' : '';

        $data['recomended'] .= <<<EOF
        <div class="index-catalog-block on-company-page toggle-block" {$inline_style}>
            <div class="block-name name company-page-name toggle-header">
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
                        Направления
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
 * Вывод удобств и оборудования на странице студии
 * 
 * @param array $specialist
 * @return array
 */
function block_equipment(array $specialist): array
{
    $code = [
        'desctop' => '',
        'mobile'  => ''
    ];

    // Получаем все элементы и выбранные специалистом
    $all_equipments = get_all_equipment();
    $selected_equipment = get_specialist_equipment($specialist['id']);

    $amenities = [];
    $equipments = [];

    // Группируем только выбранные элементы
    foreach ($all_equipments as $item) {
        if (array_key_exists($item['id'], $selected_equipment)) {
            if ($item['category'] === 'amenity') {
                $amenities[] = $item;
            } elseif ($item['category'] === 'equipment') {
                $equipments[] = $item;
            }
        }
    }

    // Сортируем оба массива по алфавиту (как в ЛК и админке)
    usort($amenities, function ($a, $b) {
        return mb_strtolower(trim($a['name'])) <=> mb_strtolower(trim($b['name']));
    });

    usort($equipments, function ($a, $b) {
        return mb_strtolower(trim($a['name'])) <=> mb_strtolower(trim($b['name']));
    });

    // Вспомогательная функция генерации HTML-элементов списка с иконками
    $generate_list_html = function (array $items) {
        $html = '';
        foreach ($items as $item) {
            // Если в БД хранится готовый SVG или класс иконки
            $icon_html = '';
            if (!empty($item['img'])) {
                if (strpos($item['img'], '<svg') !== false) {
                    $icon_html = $item['img'];
                } else {
                    $icon_html = '<img src="/assets/img/equipment/' . htmlspecialchars($item['img']) . '"></img>';
                }
            } else {
                // Резервный вариант (класс по ID)
                $icon_html = '<i class="eq-icon-' . $item['id'] . '"></i>';
            }

            $html .= "<li>
                <div class='studio-equipment__icon'>{$icon_html}</div>
                <span class='studio-equipment__text'>{$item['name']}</span>
            </li>";
        }
        return $html;
    };

    $amenities_html = $generate_list_html($amenities);
    $equipments_html = $generate_list_html($equipments);

    $html_output = '';

    // Блок: Удобства
    if ($amenities_html) {
        $html_output .= <<<EOF
            <div class="studio-equipment__section">
                <h2 class="name">Удобства в студии</h2>
                <ul class="studio-equipment__grid">
                    {$amenities_html}
                </ul>
            </div>
        EOF;
    }

    // Блок: Оборудование
    if ($equipments_html) {
        // Добавляем отступ сверху между блоками, если вывелись оба
        $extra_class = $amenities_html ? 'studio-equipment__section--spacing' : '';
        $html_output .= <<<EOF
            <div class="studio-equipment__section {$extra_class}">
                <h2 class="name">Оборудование для занятий</h2>
                <ul class="studio-equipment__grid">
                    {$equipments_html}
                </ul>
            </div>
        EOF;
    }

    if (!empty($html_output)) {
        $code['desctop'] = '<div class="studio-equipment">' . $html_output . '</div>';
        // Поскольку структура сетки полностью адаптивная через CSS, мобильная версия дублирует десктопную
        $code['mobile']  = $code['desctop'];
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

            $cleanPrice = preg_replace('/[^0-9]/', '', $service['price']);

            $formattedPrice = !empty($cleanPrice) ? number_format((int)$cleanPrice, 0, '.', "'") : $service['price'];

            $code['popup'] .= <<<EOF
                <div class="price-item">
                    <div class="price-name">{$service['name']}</div>
                    <div class="price-value">{$of} {$formattedPrice} ₽</div>
                </div>
        EOF;

            $class = '';
            if ($key > 5) $class = 'class="hidden-price"';

            $code['desctop'] .= <<<EOF
                <tr {$class}>
                    <td>{$service['name']}</td>
                    <td>{$of} {$formattedPrice} ₽</td>
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
            /* $month = date("m", strtotime($review['date']));
            $date = date("m, Y", strtotime($review['date']));
            $date = str_replace($month, $monthsList[$month], $date); */
            $timestamp = strtotime($review['date']);
            $month = date('m', $timestamp);
            $year = date('Y', $timestamp);
            $date = $monthsList[$month] . ', ' . $year;

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
function block_address(string $address, string $map_adress, string $how_to_get, string|null $coordinates, array $specialist, string $name, $rating): string
{
    // Если нет ни координат, ни адреса — вообще ничего не выводим
    if (!$coordinates && !$address) {
        return '';
    }

    $code = '<div class="map">'; // Открываем один раз в самом начале

    if (!is_null($coordinates) && $coordinates) {
        list($longitude, $latitude) = explode(' ', $coordinates);

        $rating_formatted = number_format($rating, 1);
        $reviews_count = isset($specialist['count_rating']) ? (int)$specialist['count_rating'] : 0;
        $star_class = $reviews_count > 0 ? 'fa fa-star' : 'fa fa-star-o';

        $ballonHtml = addslashes('<div style="font-weight: 600;">' . $specialist['classification'] . ' ' . $name . ' <span><i style="color: #FF9E68;" class=\'' . $star_class . '\'></i> <span style="color: #FF9E68;">' . $rating_formatted . '</span></span></div>');

        $code .= '
                <h2 class="name">
                    Адрес на карте
                </h2>
                <div class="mmap">
                    <div id="specialist_adress_map" style="max-height: 480px; height: 100%;">
                    </div>
                    <script src="https://api-maps.yandex.ru/2.1/?apikey=129a1c0b-f07a-49fa-96f8-abd7d687e8e9&lang=ru_RU" type="text/javascript"></script>
                    <script type="text/javascript">
                        ymaps.ready(init);
                        function init() {
                            var myMap = new ymaps.Map("specialist_adress_map", {
                                center: [' . $latitude . ', ' . $longitude . '],
                                zoom: 16
                            }, {
                                searchControlProvider: "yandex#search",
                                balloonPanelMaxMapArea: 0 
                            });
                            var myCollection = new ymaps.GeoObjectCollection(); 
                            var myPlacemark = new ymaps.Placemark([
                                ' . $latitude . ', ' . $longitude . '
                            ], {
                                balloonContent: "' . $ballonHtml . addslashes($map_adress) . '"
                            }, {
                                preset: "islands#icon",
                                iconColor: "#F2606C"
                            });
                            myCollection.add(myPlacemark);
                            myMap.geoObjects.add(myCollection);
                        }
                    </script>
                </div>';
    } else {
        // Сюда попадем, только если координат нет, но адрес заполнен
        $code .= '
                <h2 class="name">
                    Адрес
                </h2>';
    }

    if ($address) {
        if ($how_to_get) {
            $how_to_get = '<p>' . $how_to_get . '</p>';
        }

        $code .= '  
                <p class="grey">Как добраться:</p>
                <div class="adress" id="copy-adress">
                    ' . $address . '
                </div>
                <textarea id="content">' . $address . '</textarea>
                ' . $how_to_get . '
                <a class="open-yandex-nav" href="yandexnavi://map_search?text=' . urlencode($map_adress) . '"><img src="/assets/img/map-button1.svg"> Открыть в Яндекс.Навигаторе</a>
                <a class="open-yandex-map" href="https://yandex.ru/maps/?text=' . urlencode($map_adress) . '"><img src="/assets/img/map-button2.svg"> Открыть в Яндекс.Картах</a>';
    }

    $code .= '</div>'; // Гарантированно закрываем .map в самом конце

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
                <h2 class="block-name name">Команда</h2>
                <div class="staff__grid">
        EOF;

        foreach ($employees as $employee) {
            $code .= <<<EOF
        <div class="item">
            <div class="img__wrapper">
                <img src="/uploads/employee/{$employee['photo']}" alt="{$employee['name']} {$employee['lastname']}" loading="lazy">
            </div>
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
                    <h2 class="name">Блог $title $name</h2>
            EOF;


            foreach ($articles as $article) {
                $code .= '<div class="item2">
                <a href="/journal/id' . $article['blog_id'] . '/' . $article['alias'] . '" class="stretched-link"></a>
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
                    <h2 class="name">Блог $title $name</h2>
                    <p class="empty-blog">В блоге еще нет записей.</p>
                </div>
            EOF;
        }
    }

    return $code;
}

/**
 * @param array $gallery_photos
 * @return string
 */
function block_studio_rooms(array $rooms): string
{
    $code = '';

    if (empty($rooms)) {
        return $code;
    }

    $code = <<<EOF
        <div class="rooms-block">
            <div class="slider-header">
                <h2 class="block-name name">Залы для занятий</h2>
                <div class="slider-arrows slider-arrows-rooms">
                    <button class="slick-prev slick-rooms-arrow-prev" type="button">
                        <img src="/assets/img/arrow-left.svg" alt="prev">
                    </button>
                    <button class="slick-next slick-rooms-arrow-next" type="button">
                        <img src="/assets/img/arrow-right.svg" alt="next">
                    </button>
                </div>
            </div>
            <div class="rooms-list">
    EOF;

    foreach ($rooms as $room) {
        $room_photos = $room['photos'] ?? [];

        if (empty($room_photos)) {
            continue;
        }

        $room_name = htmlspecialchars($room['name'] ?? '', ENT_QUOTES);

        $slides = '';
        foreach ($room_photos as $photo) {
            $photo_url = '/uploads/rooms/' . htmlspecialchars($photo, ENT_QUOTES);
            $slides .= <<<EOF
                <div class="swiper-slide">
                    <div class="room-slide-inner">
                        <img src="{$photo_url}" alt="{$room_name}" loading="lazy">
                    </div>
                </div>
            EOF;
        }

        $code .= <<<EOF
            <div class="room-item">
                <div class="room-swiper swiper">
                    <div class="room-name">{$room_name}</div>
                    <div class="swiper-wrapper">
                        {$slides}
                    </div>
                    <div class="swiper-button-prev rooms-arrow-prev"></div>
                    <div class="swiper-button-next rooms-arrow-next"></div>
                    <div class="swiper-pagination room-pagination"></div>
                </div>
            </div>
        EOF;
    }

    $code .= <<<EOF
            </div>
        </div>
    EOF;

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
                    <h2 class="block-name name">Акции в студии</h2>
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
                    <div class="img-block">
                        <img src="/uploads/documents/{$document['file']}" alt="Документ специалиста" loading="lazy">
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
        "url" => $specialist['url'] ?? "https://urfaq.ru/studio/id{$specialist['id']}",
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
        3 => ['name' => 'Эксперты', 'slug' => 'yoga-studii'],
        8 => ['name' => 'Эксперты', 'slug' => 'yoga-studii'],
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
