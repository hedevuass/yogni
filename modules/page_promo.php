<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Вывод отладочной информации
  file_put_contents('php://stderr', print_r($_POST, true));

  $specialist_id = $_POST['specialist_id'];
  $duration = $_POST['duration'];
  $duration_int = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
  $end_date = date('Y-m-d', strtotime("+ $duration"));

  if (isset($_POST['tariff_id'])) {
      $tariff_id = $_POST['tariff_id'];
      add_tariff_to_specialist($specialist_id, $tariff_id, $end_date, $duration_int);
      echo json_encode(['status' => 'success', 'message' => 'Тариф успешно подключен']);
  } elseif (isset($_POST['option_id'])) {
      $option_id = $_POST['option_id'];
      add_options_to_specialist($specialist_id, $option_id, $end_date);
      echo json_encode(['status' => 'success', 'message' => 'Опция успешно подключена']);
  } elseif (isset($_POST['banner_id'])) {
      $banner_id = $_POST['banner_id'];
      $start_date = $_POST['start_date'];
      $end_date = $_POST['end_date'];
      $description = $_POST['description'];
      add_banners_to_specialist($specialist_id, $banner_id, $start_date, $end_date, $description);
      echo json_encode(['status' => 'success', 'message' => 'Баннер успешно размещен']);
  } elseif (isset($_POST['journal_id'])) {
      $journal_id = $_POST['journal_id'];
      $start_date = $_POST['start_date'];
      $end_date = $_POST['end_date'];
      $description = $_POST['description'];
      add_journal_to_specialist($specialist_id, $journal_id, $start_date, $end_date, $description);
      echo json_encode(['status' => 'success', 'message' => 'Баннер успешно размещен']);
  } elseif (isset($_POST['promos'])) {
      foreach ($_POST['promos'] as $promo) {
          $promo_id = $promo['promo_id'];
          $service_ids = $promo['service_ids'];
          $start_date = $promo['start_date'];
          $end_date = $promo['end_date'];
          $price = $promo['price'];
          $specialist_id = $promo['specialist_id'];

          foreach ($service_ids as $service_id) {
              add_promo_placement($specialist_id, $promo_id, $service_id, $start_date, $end_date, $price);
          }
      }
      echo json_encode(['status' => 'success', 'message' => 'Промо размещение успешно добавлено']);
  } else {
      echo json_encode(['status' => 'error', 'message' => 'Неверный запрос']);
  }
  exit();
}


$data = [];
$specialist = get_specialist($_SESSION['user']['id']);
$specialist_classification = $specialist['classification_id'];

//Тарифы
$tariffs = [
    'basic' => get_tariff_data(1),
    'pro' => get_tariff_data(2),
    'expert' => get_tariff_data(3)
];

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

if (isset($tariff['tariff_id']) && isset($connected_tariffs_id[$tariff['tariff_id']])) {
  $connected_id = $connected_tariffs_id[$tariff['tariff_id']];
  $end_date = $connected_tariffs_map[$tariff['tariff_id']];
  $duration = $connected_tariffs_duration[$tariff['tariff_id']];

  // Конвертируем end_date в объект DateTime
  $end_date_obj = DateTime::createFromFormat('d.m.y', $end_date);
  
  // Вычисляем start_date
  $start_date_obj = clone $end_date_obj;
  $start_date_obj->modify("-$duration months");
  
  $start_date = $start_date_obj->format('d.m.y');
  $connected_name = get_tariff_name($connected_id);

  // Формируем HTML для попапа
  $connected_popup = '
  <p class="connected_tariff_popup">
      <div class="popup-connected-tariff-p">У вас подключен тариф: <span>"' . htmlspecialchars($connected_name) . '"</span></div>
      <div class="popup-connected-tariff-p">Период действия: <span>с ' . htmlspecialchars($start_date) . '</span> <span id="popup-connected-tariff-duration"> по ' . htmlspecialchars($end_date) . '</span></div>
  </p>
  <div class="connected-tariff-descr">*При изменении тарифного плана до окончания срока действия подписки стоимость услуг не пересчитывается, новый тариф подключается с даты оплаты.</div>';
} else {
  // Если нет подключенных тарифов
  $connected_popup = '';
}

function generate_tariff_html($tariff, $class, $specialist_id, $connected_tariffs_map)
{
    $options = json_decode($tariff['options'], true);
    $options_html = '';
    foreach ($options as $option) {
        $options_html .= '<li><img src="../assets/img/cabinet/' . $option['image'] . '" alt="' . $option['text'] . '">' . $option['text'] . '</li>';
    }

    $button_html = '';
    if (isset($connected_tariffs_map[$tariff['id']])) {
        $end_date = $connected_tariffs_map[$tariff['id']];
        $button_html = '<button class="confirm-btn card-button confirm-btn-' . $class . ' inactive-button">Подключено до ' . $end_date . '</button>';
    } else {
        $button_html = '
        <button class="confirm-btn card-button confirm-btn-' . $class . ' opacity-button" 
                data-tariff-id="' . $tariff['id'] . '" 
                data-tariff-name="' . $tariff['name'] . '" 
                data-tariff-price="' . $tariff['price'] . '" 
                data-tariff-old-price="' . $tariff['price_old'] . '"
                data-specialist-id="' . $specialist_id . '"
                data-options=\'' . json_encode($options) . '\'>Подключить</button>';
    }

    return '
    <div class="tariff-card tariff-card-' . $class . '">
        <h3>' . $tariff['name'] . '</h3>
        <p><span class="old-price price-old">' . $tariff['price_old'] . '</span> <span class="new-price">' . $tariff['price'] . '</span> ₽ / мес.</p>
        <ul>' . $options_html . '</ul>
        ' . $button_html . '
    </div>
    ';
}

function generate_mobile_tariff_html($tariff, $class, $specialist_id, $connected_tariffs_map)
{
    $options = json_decode($tariff['options'], true);
    $options_html = '';
    foreach ($options as $option) {
        $options_html .= '<li><img src="../assets/img/cabinet/' . $option['image'] . '" alt="' . $option['text'] . '">' . $option['text'] . '</li>';
    }

    $button_html = '';
    if (isset($connected_tariffs_map[$tariff['id']])) {
        $end_date = $connected_tariffs_map[$tariff['id']];
        $button_html = '<button class="confirm-btn card-button confirm-btn-' . $class . ' inactive-button">Подключено до ' . $end_date . '</button>';
    } else {
        $button_html = '
        <button class="confirm-btn card-button confirm-btn-' . $class . ' opacity-button" 
                data-tariff-id="' . $tariff['id'] . '" 
                data-tariff-name="' . $tariff['name'] . '" 
                data-tariff-price="' . $tariff['price'] . '"
                data-tariff-old-price="' . $tariff['price_old'] . '"
                data-specialist-id="' . $specialist_id . '"
                data-options=\'' . json_encode($options) . '\'>Подключить</button>';
    }

    return '
    <div class="swiper-slide">
        <div class="tariff-card tariff-card-' . $class . '">
            <h3>' . $tariff['name'] . '</h3>
            <p><span class="old-price price-old">' . $tariff['price_old'] . '</span> <span class="new-price">' . $tariff['price'] . '</span> ₽ / мес.</p>
            <ul>' . $options_html . '</ul>
            ' . $button_html . '
        </div>
    </div>
    ';
}

$tariff_html = '';
$tariff_html .= generate_tariff_html($tariffs['basic'], 'basic', $specialist['id'], $connected_tariffs_map);
$tariff_html .= generate_tariff_html($tariffs['pro'], 'pro', $specialist['id'], $connected_tariffs_map);
$tariff_html .= generate_tariff_html($tariffs['expert'], 'expert', $specialist['id'], $connected_tariffs_map);

$mobile_tariff_html = '';
$mobile_tariff_html .= generate_mobile_tariff_html($tariffs['basic'], 'basic', $specialist['id'], $connected_tariffs_map);
$mobile_tariff_html .= generate_mobile_tariff_html($tariffs['pro'], 'pro', $specialist['id'], $connected_tariffs_map);
$mobile_tariff_html .= generate_mobile_tariff_html($tariffs['expert'], 'expert', $specialist['id'], $connected_tariffs_map);


//Опции
$options = get_all_options();

$connected_options = get_connected_options($specialist['id']);

$connected_options_map = [];
foreach ($connected_options as $option) {
    $connected_options_map[$option['option_id']] = $option['end_date'];
}

$options_html = '';
function generate_option_html($option, $connected_options_map, $specialist_id, $connected_tariffs_map)
{
    $is_connected = isset($connected_options_map[$option['id']]);
    $end_date = $is_connected ? date('d.m.y', strtotime($connected_options_map[$option['id']])) : '';

    $connected_tariffs_ids = array_keys($connected_tariffs_map);
    $is_basic_tariff_connected = in_array('1', $connected_tariffs_ids); // Тариф 1 (basic)
    $is_other_tariffs_connected = in_array('2', $connected_tariffs_ids) || in_array('3', $connected_tariffs_ids); // Тариф 2 или 3

    $included_in_tariff = ($is_other_tariffs_connected && in_array($option['id'], [1, 2, 3, 4, 5])) ||
                          ($is_basic_tariff_connected && in_array($option['id'], [1, 2, 3]));

    $option_html = '
    <div class="table-row">
        <div class="table-cell">' . htmlspecialchars($option['name']) . '</div>
        <div class="table-cell hidden-comp">
          <label class="switch" data-option-id="' . $option['id'] . '" data-specialist-id="' . $specialist_id . '">
            <input type="checkbox" readonly ' . ($is_connected || $included_in_tariff ? 'checked' : '') . ' />
            <span class="slider round"></span>
          </label>
        </div>
        <div class="table-cell table-cell-descr hidden-comp">
        ';

    if ($included_in_tariff) {
        // Если опция включена в тариф basic
        $option_html .= 'Включено в тариф';
    } elseif ($is_connected) {
        // Если опция подключена отдельно
        $option_html .= 'Подключено до ' . htmlspecialchars($end_date);
    } else {
        // Если опция не подключена
        $option_html .= '<button class="connect-button connect-button-option" data-option-id="' . $option['id'] . '" data-specialist-id="' . $specialist_id . '">Подключить за ' . htmlspecialchars($option['price']) . ' ₽</button>';
    }

    $option_html .= '</div>
    <div class="mobile-only merge-column">
        <div class="table-cell">
          <label class="switch" data-option-id="' . $option['id'] . '" data-specialist-id="' . $specialist_id . '">
            <input type="checkbox" readonly ' . ($is_connected || $included_in_tariff ? 'checked' : '') . ' />
            <span class="slider round"></span>
          </label>
        </div>
        <div class="table-cell table-cell-descr">';

    if ($included_in_tariff) {
        $option_html .= 'Включено в тариф';
    } elseif ($is_connected) {
        $option_html .= 'Подключено до ' . htmlspecialchars($end_date);
    } else {
        $option_html .= '<button class="connect-button connect-button-option" data-option-id="' . $option['id'] . '" data-specialist-id="' . $specialist_id . '">Подключить за ' . htmlspecialchars($option['price']) . ' ₽</button>';
    }
    $option_html .= '</div>
    </div>
    </div>';

    return $option_html;
}



foreach ($options as $option) {
    $options_html .= generate_option_html($option, $connected_options_map, $specialist['id'], $connected_tariffs_map);
}

// Получаем данные о баннерах
$banners = get_all_banners();

$connected_banners = get_connected_banners($specialist['id']);

$connected_banners_map = [];
foreach ($connected_banners as $banner) {
    $connected_banners_map[$banner['banner_id']] = [
        'start_date' => $banner['start_date'],
        'end_date' => $banner['end_date']
    ];
}

$banner_items_html = '';

// Функция для генерации HTML для подключенных баннеров
function generate_connected_banner_html($banner, $dates)
{
    return '
    <div class="connected-detail">
        <img src="../assets/img/cabinet/connected-banner.svg" alt="Icon" class="connected-icon" />
        <div class="connected-info">
            <p>' . htmlspecialchars($banner['name']) . '</p>
            <p>с ' . date('d.m.y', strtotime($dates['start_date'])) . ' по ' . date('d.m.y', strtotime($dates['end_date'])) . '</p>
        </div>
    </div>
    ';
}

foreach ($banners as $banner) {
    if (isset($connected_banners_map[$banner['id']])) {
        $banner_items_html .= generate_connected_banner_html($banner, $connected_banners_map[$banner['id']]);
    }
}

if (!empty($banner_items_html)) {
    $banner_html = '
    <div class="connected-banners">
    <div class="connected-item">
        <h3>Размещения:</h3>
        ' . $banner_items_html . '
    </div>
    </div>';
} else {
    $banner_html = '';
}

$banner_options_html = '
<div class="select-toggle input">Выберите баннер для размещения</div>
<div class="select-dropdown">
  <label class="option-label">Баннеры для размещения</label>';

foreach ($banners as $banner) {
    // Юрист (classification_id = 1)
    if ($specialist_classification == 1 && ($banner['id'] == 1 || $banner['id'] == 2)) {
        $banner_options_html .= '
        <label class="select-option select-option-banner">
          <input type="radio" name="banner_id" value="' . htmlspecialchars($banner['id']) . '">
          ' . htmlspecialchars($banner['name']) . '
        </label>';
    }
    // Эксперт (classification_id = 3)
    elseif ($specialist_classification == 3 && ($banner['id'] == 1 || $banner['id'] == 3)) {
        $banner_options_html .= '
        <label class="select-option select-option-banner">
          <input type="radio" name="banner_id" value="' . htmlspecialchars($banner['id']) . '">
          ' . htmlspecialchars($banner['name']) . '
        </label>';
    }
    // Нотариус (classification_id = 4)
    elseif ($specialist_classification == 4 && ($banner['id'] == 1 || $banner['id'] == 4)) {
        $banner_options_html .= '
        <label class="select-option select-option-banner">
          <input type="radio" name="banner_id" value="' . htmlspecialchars($banner['id']) . '">
          ' . htmlspecialchars($banner['name']) . '
        </label>';
    } 
    else {
        $banner_options_html .= '
        <label class="select-option select-option-banner">
          <input type="radio" name="banner_id" value="' . htmlspecialchars($banner['id']) . '">
          ' . htmlspecialchars($banner['name']) . '
        </label>';
    }
}

$banner_options_html .= '
</div>';


$popup_banner_html = '
<div class="popup banner-popup" id="banner-popup">
  <div class="bg"></div>
  <div class="window middle">
    <div class="close"></div>
    <div class="reg">
      <div class="popup-body">
        <form method="POST" id="banner-popup-form">
          <div class="name">
            Размещение баннера
          </div>
          <div class="popup-content">
                ' . $banner_options_html . '
            <input type="text" id="date-select" name="date" placeholder="Даты (кратно 1 неделе)" readonly>
    
            <label for="ad-text">Введите рекламный текст:</label>
            <p class="paragraph">В рекламных строках рекомендуется указать ваши преимущества и выгодные предложения.</p>
            <p class="paragraph">Ваше имя или название компании будут отображены на баннере по умолчанию.</p>

            <input type="text" name="ad_line1" placeholder="Строка баннера № 1 - не более 30 символов">
            <input type="text" name="ad_line2" placeholder="Строка баннера № 2 - не более 30 символов">
            <input type="text" name="ad_line3" placeholder="Строка баннера № 3 - не более 30 символов">
            <input type="hidden" id="popup-specialist-id" value="'. htmlspecialchars($specialist['id']) .'">
            <input type="hidden" id="popup-banner-price" value="7590">
    
          </div>
          <div class="button">
          <button class="reklama-continue-btn">Далее</button>
          </div>
          <div class="popup-footer hidden">
            <p class="red">Выберите место и период размещения</p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>';

$popup_banner_html2 = '
<div class="popup banner-popup" id="banner-popup-2">
  <div class="bg"></div>
  <div class="window middle">
    <div class="close"></div>
    <div class="reg">
      <div class="popup-body">
        <form method="POST" id="banner-popup-form-2">
          <div class="name">
            Размещение баннера
          </div>
          <div class="popup-content">
          <p class="text-only-tariff">
            <span id="banner-name"></span> <span class="right-side" id="banner-price"></span><br>
            <hr>
            Период <span class="right-side"></span>
            <span class="right-side" id="banner-period"></span><br>
            <p class="right-side">с <span id="start-date"></span> по <span id="end-date"></span></p>
            <span class="right-side" id="popup-tariff-duration"></span><br>
            <div class="for-pay">К оплате: <span id="total-price"></span></div>
            <p class="reklam-text">*Рекламный текст проверит модератор, после чего на электронную почту вам будет направлена ссылка для оплаты размещения.</p>
            <div class="button">
            <button type="submit" class="submit-btn">Отправить на модерацию</button>
            </div>
            </p>
            <input type="hidden" name="banner_id" id="popup-banner-id-2">
            <input type="hidden" value="'. $specialist['id'] .'" name="specialist_id" id="popup-specialist-id-2">
            <input type="hidden" name="start_date" id="popup-start-date">
            <input type="hidden" name="end_date" id="popup-end-date">
            <input type="hidden" name="description" id="popup-description">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>';

// Получаем данные о журнале
$journals = get_all_journals();

$connected_journals = get_connected_journals($specialist['id']);

$connected_journals_map = [];
foreach ($connected_journals as $journal) {
    $connected_journals_map[$journal['journal_id']] = [
        'start_date' => $journal['start_date'],
        'end_date' => $journal['end_date'],
    ];
}

$journal_items_html = '';

// Функция для генерации HTML для подключенных записей в журнале
function generate_connected_journal_html($journal, $dates) {
    return '
    <div class="connected-detail">
        <img src="../assets/img/cabinet/connected-journal.svg" alt="Icon" class="connected-icon" />
        <div class="connected-info">
            <p>' . htmlspecialchars($journal['name']) . '</p>
            <p>с ' . date('d.m.y', strtotime($dates['start_date'])) . ' по ' . date('d.m.y', strtotime($dates['end_date'])) . '</p>
        </div>
    </div>
    ';
}

foreach ($journals as $journal) {
    if (isset($connected_journals_map[$journal['id']])) {
        $journal_items_html .= generate_connected_journal_html($journal, $connected_journals_map[$journal['id']]);
    }
}

if (!empty($journal_items_html)) {
    $journal_html = '
    <div class="connected-item">
        <h3>Размещения:</h3>
        ' . $journal_items_html . '
    </div>';
}

$journal_options_html = '
<div class="select-toggle input">Выберите место для размещения</div>
<div class="select-dropdown">';

foreach ($journals as $journal) {
    $journal_options_html .= '
    <label class="select-option select-option-journal">
      <input type="radio" name="journal_id" value="' . htmlspecialchars($journal['id']) . '">
      ' . htmlspecialchars($journal['name']) . '
    </label>';
}

$journal_options_html .= '
</div>';

$popup_journal_html = '
<div class="popup journal-popup" id="journal-popup">
  <div class="bg"></div>
  <div class="window middle">
    <div class="close"></div>
    <div class="reg">
      <div class="popup-body">
        <form method="POST" id="journal-popup-form">
          <div class="name">
            Размещение баннера
          </div>
          <div class="popup-content">
            ' . $journal_options_html . '
            <input type="text" id="date-select-journal" name="date" placeholder="Даты (кратно 1 неделе)" readonly>
    
            <label for="ad-text">Введите рекламный текст:</label>
            <p class="paragraph">В рекламных строках рекомендуется указать ваши преимущества и выгодные предложения.</p>
            <p class="paragraph">Ваше имя или название компании будут отображены на баннере по умолчанию.</p>

            <input type="text" name="ad_line1" placeholder="Заголовок - не более 30 символов">
            <input type="text" name="ad_line2" placeholder="Описание - не более 30 символов">
            <input type="hidden" id="popup-specialist-id" value="'. htmlspecialchars($specialist['id']) .'">
            <input type="hidden" id="popup-journal-price" value="7590">
    
          </div>
          <div class="button">
          <button class="reklama-continue-btn">Далее</button>
          </div>
          <div class="popup-footer hidden">
            <p class="red">Выберите место и период размещения</p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>';

$popup_journal_html2 = '
<div class="popup journal-popup" id="journal-popup-2">
  <div class="bg"></div>
  <div class="window middle">
    <div class="close"></div>
    <div class="reg">
      <div class="popup-body">
        <form method="POST" id="journal-popup-form-2">
          <div class="name">
            Размещение баннера
          </div>
          <div class="popup-content">
          <p class="text-only-tariff">
            <span id="journal-name"></span> <span class="right-side" id="journal-price"></span><br>
            <hr>
            Период <span class="right-side"></span>
            <span class="right-side" id="journal-period"></span><br>
            <p class="right-side">с <span id="start-date"></span> по <span id="end-date"></span></p>
            <span class="right-side" id="popup-tariff-duration"></span><br>
            <div class="for-pay">К оплате: <span id="total-price"></span></div>
            <p class="reklam-text">*Рекламный текст проверит модератор, после чего на электронную почту вам будет направлена ссылка для оплаты размещения.</p>
            <div class="button">
            <button type="submit" class="submit-btn">Отправить на модерацию</button>
            </div>
            </p>
            <input type="hidden" name="journal_id" id="popup-journal-id-2">
            <input type="hidden" value="'. $specialist['id'] .'" name="specialist_id" id="popup-specialist-id-2">
            <input type="hidden" name="start_date" id="popup-start-date-journal">
            <input type="hidden" name="end_date" id="popup-end-date-journal">
            <input type="hidden" name="description" id="popup-description-journal">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>';

$services = get_services_by_classification($specialist_classification);
$promo_options = get_all_promo();

$popup_promo_html = '
<div class="popup promo-popup" id="promo-popup">
  <div class="bg"></div>
  <div class="window middle">
    <div class="close"></div>
    <div class="reg">
      <div class="popup-body">
        <form method="POST" id="promo-popup-form">
          <div class="name">Промо-размещение</div>
          <div class="popup-content">
            <div class="custom-select" id="promo-select">
              <div class="select-toggle input">Выберите место для размещения</div>
              <div class="select-dropdown">
              <label class="option-label">Разделы/подразделы каталога</label>';

foreach ($services as $service) {
    $popup_promo_html .= '
                <label class="select-option">
                  <input type="checkbox" name="service_ids[]" value="' . $service['id'] . '">
                  ' . htmlspecialchars($service['title']) . '
                </label>';
}

$popup_promo_html .= '
              </div>
            </div>
            <input type="text" class="input" id="promo-date-select" name="date" placeholder="Даты (кратно 1 неделе)" readonly>
            <input type="hidden" value="'. $specialist['id'] .'" name="specialist_id" id="popup-specialist-id-promo">';
foreach ($promo_options as $promo) {
    $class = $index === 1 ? "promo-icon special-padding" : "promo-icon"; // Добавляем класс только ко второму элементу
    $popup_promo_html .= '
                  <label class="select-option select-option-promo">
                      <input type="radio" name="promo_id" value="' . $promo['id'] . '">
                      <img src="' . htmlspecialchars($promo['url']) . '" alt="Icon" class="' . $class . '">
                      <div>
                          <div class="promo-title bold">' . htmlspecialchars($promo['title']) . '</div>
                          <div class="promo-description">' . htmlspecialchars($promo['description']) . '</div>
                          <div class="promo-price hidden">' . htmlspecialchars($promo['price']) . '</div>
                      </div>
                  </label>';
    $index++;
}

$popup_promo_html .= '
            <div class="button">
              <button type="submit" class="promo-continue-btn">Далее</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>';

// Получаем данные о промо
$connected_promo = get_connected_promo($specialist['id']);

// Карта подключенных промо
$connected_promo_map = [];
foreach ($connected_promo as $promo) {
    if (is_array($promo)) {
        $promo_details = get_promo_details($promo['promo_id']);
        $connected_promo_map[] = [
            'title' => $promo_details['title'],
            'url' => $promo_details['url'],
            'start_date' => $promo['start_date'],
            'end_date' => $promo['end_date']
        ];
    }
}

$promo_items_html = '';

// Функция для генерации HTML для подключенных промо
function generate_connected_promo_html($promo)
{
    return '
    <div class="connected-detail">
        <img src="' . htmlspecialchars($promo['url']) . '" alt="Icon" class="connected-icon" />
        <div class="connected-info">
            <p>' . htmlspecialchars($promo['title']) . '</p>
            <p>с ' . date('d.m.y', strtotime($promo['start_date'])) . ' по ' . date('d.m.y', strtotime($promo['end_date'])) . '</p>
        </div>
    </div>
    ';
}

// Генерация HTML для всех подключенных промо
foreach ($connected_promo_map as $promo) {
    $promo_items_html .= generate_connected_promo_html($promo);
}

if (!empty($promo_items_html)) {
    $promo_html = '
    <div class="connected-banners">
    <div class="connected-item">
        <h3>Промо размещения:</h3>
        ' . $promo_items_html . '
    </div>
      </div>';
} else {
    $promo_html = '';
}


// Промо
$connected_promo = get_connected_promo($specialist['id']);

$connected_promo_map = [];
foreach ($connected_promo as $promo) {
    if (is_array($promo)) {
        $promo_details = get_promo_details($promo['promo_id']);
       
        $connected_promo_map[] = [
            'title' => $promo_details['title']
        ];
    }
}


$tariff_status = generate_tariff_status($tariffs, $connected_tariffs_map);
$option_status_data = generate_option_status($connected_options_map);
$promo_status_data = generate_promo_status($connected_promo_map);

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

$top_cards = '
<a href="#tariff-href" class="card">
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
<a href="#promo-href" class="card">
      <div class="card-topline"><h4>Промо <span class="status-dot ' . $promo_status_data['class'] . '"></span></h4><img src="../assets/img/cabinet/card-promo.svg" alt=""></div>
        <p>Продвижение карточки</p>
        <div class="card-status">
            <span>' . $promo_status_data['status'] . '</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
    </a>
<a href="#option-href" class="card">
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
    <a href="/cabinet/pro-photo" class="card">
      <div class="card-topline"><h4>PRO-фото <span class="status-dot"></span></h4><img src="../assets/img/cabinet/card-photo.svg" alt=""></div><div class="circle-pro-photo">-50%</div>
        <p>Скидка 50% до 17.04.24</p>
        <div class="card-status">
            <span>Не проведена</span>
            <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
        </div>
    </a>';

    

$data['tariff_cards'] = $tariff_html;
$data['connected_tariff'] = $connected_popup;
$data['top_cards'] = $top_cards;
$data['mobile_tariff_cards'] = $mobile_tariff_html;
$data['options'] = $options_html;
$data['banners'] = $banner_html;
$data['promo_connected'] = $promo_html;
$data['journal'] = $journal_html;
$data['banner-popup'] = $popup_banner_html;
$data['banner-popup2'] = $popup_banner_html2;
$data['journal-popup'] = $popup_journal_html;
$data['journal-popup2'] = $popup_journal_html2;
$data['promo-popup'] = $popup_promo_html;
$data['second-promo-popup'] = $second_popup_promo_html;
$HTML = html_explode($data, $HTML);
