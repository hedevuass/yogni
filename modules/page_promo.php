<?php

$specialist = get_specialist($_SESSION['user']['id']);
$specialist_classification = $specialist['classification_id'];
$specialist_id = $specialist['id'];
// Получаем ID специалиста из сессии
$payment_type = null;

if ($specialist_id) {
  $payment = get_last_payment($specialist_id);
  if ($payment) {
    $payment_type = $payment['tableType'];
  }
}

$forbidden_words = getForbiddenWords();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Вывод отладочной информации
  file_put_contents('php://stderr', print_r($_POST, true));

  $specialist_id = $_POST['specialist_id'];
  $duration = $_POST['duration'];
  $duration_int = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
  $end_date = date('Y-m-d', strtotime("+ $duration"));

  if (isset($_POST['tariff_id'])) {
    $tariff_id = $_POST['tariff_id'];
    $orderId = $_POST['orderId'] ?? null;
    add_tariff_to_specialist($specialist_id, $tariff_id, $end_date, $duration_int, $orderId);
    echo json_encode(['status' => 'success', 'message' => 'Тариф успешно подключен']);
  } elseif (isset($_POST['option_id'])) {
    $option_id = $_POST['option_id'];
    $orderId = $_POST['orderId'] ?? null;
    add_options_to_specialist($specialist_id, $option_id, $end_date, $orderId);
    echo json_encode(['status' => 'success', 'message' => 'Опция успешно подключена']);
  } elseif (isset($_POST['banner_id'])) {
    $banner_id = $_POST['banner_id'];
    $banner_name = $_POST['banner_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];

    add_banners_to_specialist($specialist_id, $banner_id, $start_date, $end_date, $description);
    echo json_encode(['status' => 'success', 'message' => 'Баннер успешно размещен']);

    $notify_data = [
      'type' => 'banner',
      'date' => date('d.m.Y'),
      'name'  => !empty($specialist['company']) ? $specialist['company'] : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? '')),
      'text' => $description,
      'period' => date('d.m.Y', strtotime($start_date)) . ' - ' . date('d.m.Y', strtotime($end_date)),
      'title' => $banner_name
    ];

    $opts = [
      'http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($notify_data),
        'timeout' => 5
      ]
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($_SERVER['HTTP_ORIGIN'] . '/actions/mail/note_notification.php', false, $context);

    echo json_encode([
      'status' => 'success',
      'message' => 'Баннер успешно размещен, уведомление отправлено'
    ]);
  } elseif (isset($_POST['journal_id'])) {
    $journal_id = $_POST['journal_id'];
    $journal_name = $_POST['journal_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];

    add_journal_to_specialist($specialist_id, $journal_id, $start_date, $end_date, $description);
    echo json_encode(['status' => 'success', 'message' => 'Баннер успешно размещен']);

    $notify_data = [
      'type' => 'banner',
      'date' => date('d.m.Y'),
      'name'  => !empty($specialist['company']) ? $specialist['company'] : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? '')),
      'text' => $description,
      'period' => date('d.m.Y', strtotime($start_date)) . ' - ' . date('d.m.Y', strtotime($end_date)),
      'title' => $journal_name
    ];

    $opts = [
      'http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($notify_data),
        'timeout' => 5
      ]
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($_SERVER['HTTP_ORIGIN'] . '/actions/mail/note_notification.php', false, $context);

    echo json_encode([
      'status' => 'success',
      'message' => 'Баннер успешно размещен, уведомление отправлено'
    ]);
  } elseif (isset($_POST['promos'])) {
    foreach ($_POST['promos'] as $promo) {
      $promo_id = $promo['promo_id'];
      $service_ids = $promo['service_ids'];
      $service_slug = $promo['service_slug'];
      $service_name = $promo['service_name'];
      $start_date = $promo['start_date'];
      $end_date = $promo['end_date'];
      $orderId = $promo['orderId'];
      $price = $promo['price'];
      $specialist_id = $promo['specialist_id'];

      $sort = $promo_id;

      foreach ($service_ids as $service_id) {
        // Определяем block_id в зависимости от service_slug
        if ($service_id  == 'block_specialist_positions') {
          $block_id = 2;
        } elseif ($service_id  == 'block_top_specialists') {
          $block_id = 1;
        } else {
          // Если slug не специальный — используем классификацию
          switch ($specialist_classification) {
            case 1:
            case 2:
            case 5:
            case 6:
            case 7:
              $block_id = 1;
              break;
            case 3:
            case 8:
              $block_id = 2;
              break;
            case 4:
              $block_id = 3;
              break;
            default:
              $block_id = null;
              break;
          }
        }

        // Передаем block_id в add_promo_list
        add_promo_list($specialist_id, $block_id, $service_id, $service_name, $sort, $start_date, $end_date, $orderId, $price);
      }
    }

    foreach ($_POST['promos'] as $promo) {
      $promo_id = $promo['promo_id'];
      $service_ids = $promo['service_ids'];
      $start_date = $promo['start_date'];
      $end_date = $promo['end_date'];
      $price = $promo['price'];
      $specialist_id = $promo['specialist_id'];
      $service_name = $promo['service_name'];

      foreach ($service_ids as $service_id) {
        add_promo_placement($specialist_id, $promo_id, $service_id, $start_date, $end_date, $price, $service_name);
      }
    }
    echo json_encode(['status' => 'success', 'message' => 'Промо размещение успешно добавлено']);
  } elseif (isset($_POST['sort'])) {
    $sort = $_POST['sort'];
    $occupied_entries = [];

    if (!empty($_POST['service_slug'])) {
      $service_slug = $_POST['service_slug'];

      // Обработка для специальных service_slug, когда нужно использовать block_id
      if ($service_slug == 'block_specialist_positions') {
        $block_id = 2;

        $conflicting_entries = get_occupied_entries_by_block_and_service($block_id, $service_slug, $sort, $specialist['id']);

        // Подсчёт только записей с sort = 2 (для проверки >= 7)
        $conflicting_entries_sort2 = array_filter($conflicting_entries, function ($e) {
          return isset($e['sort']) && (int)$e['sort'] === 2;
        });

        foreach ($conflicting_entries as $entry) {
          // Если это тот же специалист — блокируем всегда
          if ($entry['specialist_id'] == $specialist['id']) {
            $occupied_entries[] = [
              'date_start'   => strtotime($entry['date_start']),
              'date_expires' => strtotime($entry['date_expires']),
            ];
          } else {
            // Другие специалисты
            if ($sort == 2 && count($conflicting_entries_sort2) >= 7) {
              $occupied_entries[] = [
                'date_start'   => strtotime($entry['date_start']),
                'date_expires' => strtotime($entry['date_expires']),
              ];
            } elseif ($sort != 2) {
              $occupied_entries[] = [
                'date_start'   => strtotime($entry['date_start']),
                'date_expires' => strtotime($entry['date_expires']),
              ];
            }
          }
        }
      } else {

        $conflicting_entries = get_conflicting_entries($specialist['id'], $service_slug);

        // Далее обрабатываем только для обычных service_slug
        foreach ($conflicting_entries as $entry) {
          $entry['date_start'] = strtotime($entry['date_start']);
          $entry['date_expires'] = strtotime($entry['date_expires']);
          $occupied_entries[] = $entry;
        }

        // Логика по sort = 2 применяется только для обычных записей
        if ($sort == 2) {
          // считаем только записи с sort = 2 (без создания новой функции)
          $entries_sort2 = get_occupied_entries($service_slug, 2);
          $entry_count = is_array($entries_sort2) ? count($entries_sort2) : 0;

          if ($entry_count >= 7) {
            $additional_entries = get_occupied_entries($service_slug, 2);
            foreach ($additional_entries as $entry) {
              $entry['date_start'] = strtotime($entry['date_start']);
              $entry['date_expires'] = strtotime($entry['date_expires']);
              $occupied_entries[] = $entry;
            }
          }
        } else {
          $additional_entries = get_occupied_entries($service_slug, $sort);
          foreach ($additional_entries as $entry) {
            $entry['date_start'] = strtotime($entry['date_start']);
            $entry['date_expires'] = strtotime($entry['date_expires']);
            $occupied_entries[] = $entry;
          }
        }
      }
    } elseif (!empty($_POST['block_id'])) {
      $block_id = $_POST['block_id'];

      $conflicting_entries = get_conflicting_entries_by_block($specialist['id'], $block_id);

      foreach ($conflicting_entries as $entry) {
        $entry['date_start'] = strtotime($entry['date_start']);
        $entry['date_expires'] = strtotime($entry['date_expires']);
        $occupied_entries[] = $entry;
      }

      if ($sort == 2) {
        // считаем только записи с sort = 2 для данного блока
        $entries_sort2 = get_occupied_entries_by_block($block_id, 2);
        $entry_count = is_array($entries_sort2) ? count($entries_sort2) : 0;

        if ($entry_count >= 7) {
          $additional_entries = get_occupied_entries_by_block($block_id, 2);
          foreach ($additional_entries as $entry) {
            $entry['date_start'] = strtotime($entry['date_start']);
            $entry['date_expires'] = strtotime($entry['date_expires']);
            $occupied_entries[] = $entry;
          }
        }
      } else {
        $additional_entries = get_occupied_entries_by_block($block_id, $sort);
        foreach ($additional_entries as $entry) {
          $entry['date_start'] = strtotime($entry['date_start']);
          $entry['date_expires'] = strtotime($entry['date_expires']);
          $occupied_entries[] = $entry;
        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode($occupied_entries);
    exit();
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный запрос']);
  }

  exit();
}

$connectedJournals = get_all_connected_journals();

// Формируем массив занятых диапазонов
$disabledJournalRanges = [];
foreach ($connectedJournals as $journal) {
  $disabledJournalRanges[] = [
    'from' => date('d.m.Y', strtotime($journal['start_date'])),
    'to' => date('d.m.Y', strtotime($journal['end_date'])),
  ];
}

$connectedBanners = get_all_connected_banners();

// Формируем массив занятых диапазонов для баннеров
$disabledBannerRanges = [];
foreach ($connectedBanners as $banner) {
  $disabledBannerRanges[] = [
    'from' => date('d.m.Y', strtotime($banner['start_date'])),
    'to' => date('d.m.Y', strtotime($banner['end_date'])),
  ];
}

// Передаем массив в JavaScript
$script = '<script>
const disabledJournalRanges = ' . json_encode($disabledJournalRanges) . ';
const disabledBannerRanges = ' . json_encode($disabledBannerRanges) . ';

(function(){
    const urlParams = new URLSearchParams(window.location.search);
    const status    = urlParams.get("status");
    const paymentType = ' . json_encode($payment_type, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) . ';

    if (status == "success" && paymentType) {
      let boldText = "", middleText = "";

      switch (paymentType) {
        case "tariff":
          boldText   = "Отлично!<br>Вы подключили тариф.";
          middleText = \'Информация появится в разделе "Продвижение" в течение 60 минут.\';
          break;
        case "option":
          boldText   = "Отлично!<br>Вы подключили опции.";
          middleText = \'Информация появится в разделе "Продвижение" в течение 60 минут.\';
          break;
        case "promo":
          boldText   = "Отлично!<br>Вы подключили промо-блоки.";
          middleText = \'Информация появится в разделе "Продвижение" в течение 60 минут.\';
          break;
        default:
          boldText   = "Оплата прошла успешно!";
          middleText = "";
      }

       setTimeout(() => {
        $("#successful-popup-tariff .bold").html(boldText);
        $("#successful-popup-tariff .middle-text").text(middleText);
        $("#successful-popup-tariff").fadeIn();
        urlParams.delete("status");
      const cleanedUrl = window.location.pathname +
        (urlParams.toString() ? "?" + urlParams.toString() : "");
      window.history.replaceState({}, document.title, cleanedUrl);
      }, 500); // задержка 500 мс
    }
})();
</script>';

$faqs = faq_promo();
$faq_html = '';

foreach ($faqs as $faq) {
  $faq_html .= "<div class='faq-item'>";
  $faq_html .= "<button class='faq-question'><div>{$faq['title']}</div><img src='../assets/img/cabinet/arrow-faq-down.svg' class='default-arrow' alt=''><img src='../assets/img/cabinet/arrow-faq-down-hover.svg' class='hover-arrow' alt=''></button>";
  $faq_html .= "<div class='faq-answer'>";
  $faq_html .= "<p>{$faq['description']}</p>";
  $faq_html .= "</div></div>";
}

$data = [];

function generate_request_html(array $request): string
{
    if (!$request) {
        return '';
    }
 
    // Неразрывный пробел после коротких предлогов, чтобы подпись на мобильных
    // переносилась как «познакомиться / с сервисом»
    $note = preg_replace('/(?<!\p{L})(с|в|к|о|у|и|а|на|по|до|за|из|от|со)\s+/iu', '$1&nbsp;', $request['comment_text']);
 
    return '
    <div class="request-head">
      <div class="request-head__text">
        <h2>' . $request['title'] . '</h2>
        <p>' . $request['description'] . '</p>
      </div>
      <div class="request-price">' . $request['price'] . '</div>
    </div>
 
    <div class="request-start">
      <div class="request-start__label">' . $request['promo_description'] . '</div>
      <p class="request-start__note">' . $note . '</p>
      <img class="request-start__badge" src="../assets/img/cabinet/request_badge.svg" alt="Request badge">
    </div>
    ';
}

$request_html = generate_request_html(get_request_data());

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
    
    if ($option['image'] === 'tariff-paint.svg') continue;
    
    $text = $option['text'];

    if ($text == "Всё, что в БАЗОВОМ") {
      $text = '<span>Всё, что в <span style="color: #F2606C; font-weight: 500;">\'БАЗОВОМ\'</span></span>';
    } elseif ($text == "Всё, что в ПРО") {
      $text = '<span>Всё, что в <span style="color: #F2606C; font-weight: 500;">\'ПРО\'</span></span>';
    }

    $li_style = ($option['image'] === 'tariff-up.svg') ? ' style="margin-left: -1px;"' : '';

    $options_html .= '<li' . $li_style . '>
        <img src="../assets/img/cabinet/' . $option['image'] . '" alt="' . strip_tags($text) . '">
        ' . $text . '
    </li>';
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
        <p><span class="old-price price-old">' . $tariff['price_old'] . '</span> <span class="new-price">' . $tariff['price'] . '</span> <span style="font-weight: 300; color: #6A6A6A;">₽ / мес.</span></p>
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

    if ($option['image'] === 'tariff-paint.svg') continue;

    $text = $option['text'];

    if ($text == "Всё, что в БАЗОВОМ") {
      $text = '<span>Всё, что в <span style="color: #F2606C; font-weight: 500;">\'БАЗОВОМ\'</span></span>';
    } elseif ($text == "Всё, что в ПРО") {
      $text = '<span>Всё, что в <span style="color: #F2606C; font-weight: 500;">\'ПРО\'</span></span>';
    }

    $options_html .= '<li><img src="../assets/img/cabinet/' . $option['image'] . '" alt="' . strip_tags($text) . '">' . $text . '</li>';
  }

  $button_html = '';
  if (isset($connected_tariffs_map[$tariff['id']])) {
    $end_date = $connected_tariffs_map[$tariff['id']];
    $button_html = '<button style="margin-top: auto;" class="confirm-btn card-button confirm-btn-' . $class . ' inactive-button">Подключено до ' . $end_date . '</button>';
  } else {
    $button_html = '
        <button style="margin-top: auto;" class="confirm-btn card-button confirm-btn-' . $class . ' opacity-button" 
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
    <div class="table-row table-row-option">
        <div class="table-cell">' . htmlspecialchars($option['name']) . '</div>
        <div class="table-cell hidden-comp center-status">
          <label class="switch" data-option-id="' . $option['id'] . '" data-specialist-id="' . $specialist_id . '">
            <input type="checkbox" readonly ' . ($is_connected || $included_in_tariff ? 'checked' : '') . ' />
            <span class="slider round"></span>
          </label>
        </div>
        <div class="table-cell table-cell-descr hidden-comp">
        ';

  if ($included_in_tariff) {
    // Если опция включена в тариф basic
    $option_html .= '<div class="option-220">Включено в тариф</div>';
  } elseif ($is_connected) {
    // Если опция подключена отдельно
    $option_html .= '<div class="option-220">Подключено до ' . htmlspecialchars($end_date) .'</div>';
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
$banner_items_html = '';

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

foreach ($connected_banners as $connected_banner) {
  foreach ($banners as $banner) {
    if ($banner['id'] == $connected_banner['banner_id']) {
      // Генерируем HTML для каждого совпадения
      $banner_items_html .= generate_connected_banner_html($banner, [
        'start_date' => $connected_banner['start_date'],
        'end_date' => $connected_banner['end_date']
      ]);
    }
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
<div class="select-toggle input selected-text-banner">Выберите баннер для размещения</div>
<div class="select-dropdown">';

foreach ($banners as $banner) {
  $hidden_input = '<input type="hidden" class="popup-banner-price" id="popup-banner-price-' . htmlspecialchars($banner['id']) . '" value="' . htmlspecialchars($banner['price']) . '">';

  // Юрист (classification_id = 1, 2, 5, 6, 7)
  if (
    (in_array($specialist_classification, ['1', '2', '5', '6', '7']) ||
      in_array($specialist_classification, [1, 2, 5, 6, 7])) &&
    (in_array($banner['id'], ['1', '2']) ||
      in_array($banner['id'], [1, 2]))
  ) {
    $banner_options_html .= '
          <label class="select-option select-option-banner">
            <input type="radio" name="banner_id" value="' . htmlspecialchars($banner['id']) . '" data-name="' . htmlspecialchars($banner['name']) . '">
            ' . htmlspecialchars($banner['name']) . '
          </label>' . $hidden_input;
  }
  // Эксперт (classification_id = 3)
  elseif (
    ($specialist_classification == '3' || $specialist_classification == 3) &&
    ($banner['id'] == '1' || $banner['id'] == '3' || $banner['id'] == 1 || $banner['id'] == 3)
  ) {
    $banner_options_html .= '
          <label class="select-option select-option-banner">
            <input type="radio" name="banner_id" value="' . htmlspecialchars($banner['id']) . '" data-name="' . htmlspecialchars($banner['name']) . '">
            ' . htmlspecialchars($banner['name']) . '
          </label>' . $hidden_input;
  }
  // Нотариус (classification_id = 4)
  elseif (
    ($specialist_classification == '4' || $specialist_classification == 4) &&
    ($banner['id'] == '1' || $banner['id'] == '4' || $banner['id'] == 1 || $banner['id'] == 4)
  ) {
    $banner_options_html .= '
          <label class="select-option select-option-banner">
            <input type="radio" name="banner_id" value="' . htmlspecialchars($banner['id']) . '" data-name="' . htmlspecialchars($banner['name']) . '">
            ' . htmlspecialchars($banner['name']) . '
          </label>' . $hidden_input;
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
        <div class="name">Размещение баннера</div>
        <div class="popup-content">
          ' . $banner_options_html . '
          <div class="error error-banner_id"></div>

          <input type="text" id="date-select" name="date" placeholder="Даты (кратно 1 неделе)" readonly>
          <div class="error error-date"></div>

          <label for="ad-text">Введите рекламный текст:</label>
          <div class="paragraph-block">
            <p class="paragraph">В рекламных строках рекомендуется указать ваши преимущества и выгодные предложения.</p>
            <p class="paragraph">Ваше имя или название компании будут отображены на баннере по умолчанию.</p>
            </div>

          <input type="text" name="ad_line1" placeholder="Строка баннера № 1 - не более 30 символов">
          <div class="error error-ad_line1"></div>

          <input type="text" name="ad_line2" placeholder="Строка баннера № 2 - не более 30 символов">
          <div class="error error-ad_line2"></div>

          <input type="text" name="ad_line3" placeholder="Строка баннера № 3 - не более 30 символов">
          <div class="error error-ad_line3"></div>

          <input type="hidden" id="popup-specialist-id" value="' . htmlspecialchars($specialist['id']) . '">
          <input type="hidden" id="popup-forbidden-words" value="' . htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8") . '">
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
          <div class="mb-100">
            <span id="banner-name"></span> <span class="right-side" id="banner-price"></span><br>
            <hr>
            <span class="popup-connected-tariff-bold">Период</span> <span class="right-side"></span>
            <span class="right-side" id="banner-period"></span><br>
            <p class="right-side light start_end_date">с <span id="start-date"></span> по <span id="end-date"></span></p>
            <span class="right-side" id="popup-tariff-duration"></span><br>
            </div>
            <div class="for-pay"><span class="popup-connected-tariff-bold">К оплате: </span><span id="total-price"></span></div>
            <p class="reklam-text">*Рекламный текст проверит модератор, после чего на электронную почту вам будет направлена ссылка для оплаты размещения.</p>
            <div class="button">
            <button type="submit" class="submit-btn">Отправить на модерацию</button>
            <p>Нажимая “Отправить”, вы соглашаетесь с <a href="../o-proekte">Условиями</a> предоставления услуг</p>
            </div>
            </p>
            <input type="hidden" name="banner_id" id="popup-banner-id-2">
            <input type="hidden" name="banner_name" id="popup-banner-name-2">
            <input type="hidden" value="' . $specialist['id'] . '" name="specialist_id" id="popup-specialist-id-2">
            <input type="hidden" name="start_date" id="popup-start-date">
            <input type="hidden" name="end_date" id="popup-end-date">
            <input type="hidden" name="description" id="popup-description">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>';

$forbidden_popup = '
<div class="popup banner-popup" id="forbidden-popup">
	<div class="bg"></div>
	<div class="window middle">
		<div class="close"></div>
		<div class="reg">
			<div class="popup-body">
				<form method="POST" id="forbidden-popup-form">
					<div class="name">
						Запрет публикации
					</div>
          <div class="popup-content forbidden-popup-content">
						<div class="forbidden-popup-text">
							Контент, который вы хотите опубликовать, содержит запрещенные слова и элементы:
							<span id="forbidden-words-list"></span>.
							<br><br>С правилами сайта вы можете ознакомиться
							на странице <a href="' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/o-proekte' . '">«О проекте»</a>.
          </div>
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
function generate_connected_journal_html($journal, $dates)
{
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

foreach ($connected_journals as $connected_journal) {
  foreach ($journals as $journal) {
    if ($connected_journal['journal_id'] == $journal['id']) {
      $journal_items_html .= generate_connected_journal_html($journal, $connected_journal);
    }
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
  <div class="select-toggle input selected-text-journal">Выберите место для размещения</div>
  <div class="select-dropdown">';
foreach ($journals as $journal) {
  $journal_options_html .= '
    <label class="select-option select-option-journal">
      <input 
        type="radio" 
        name="journal_id" 
        value="' . htmlspecialchars($journal['id']) . '" 
        data-name="' . htmlspecialchars($journal['name']) . '"
      >
      ' . htmlspecialchars($journal['name']) . '
    </label>
    <input 
      type="hidden" 
      class="popup-journal-price" 
      id="popup-journal-price-' . htmlspecialchars($journal['id']) . '" 
      value="' . htmlspecialchars($journal['price']) . '"
    >';
}
$journal_options_html .= '
  </div>
  <!-- Контейнер для ошибки выбора места размещения -->
  <div class="error error-journal_id"></div>
';


$popup_journal_html = '
<div class="popup journal-popup" id="journal-popup">
  <div class="bg"></div>
  <div class="window middle">
    <div class="close"></div>
    <div class="reg">
      <div class="popup-body">
        <form method="POST" id="journal-popup-form">
          <div class="name">
            Продвижение в журнале
          </div>
          <div class="popup-content popup-content-journal">
            ' . $journal_options_html . '
            <input type="text" id="date-select-journal" name="date" placeholder="Даты (кратно 1 неделе)" readonly>
            <div class="error error-date"></div>
    
            <label for="ad-text">Введите рекламный текст:</label>
            <div class="paragraph-block">
            <p class="paragraph">В рекламных строках рекомендуется указать ваши преимущества и выгодные предложения.</p>
            <p class="paragraph">Ваше имя или название компании будут отображены на баннере по умолчанию.</p>
            </div>

            <input type="text" name="ad_line1_j" placeholder="Заголовок - не более 50 символов">
            <div class="error error-ad_line1_j"></div>

            <input type="text" name="ad_line2_j" placeholder="Описание - не более 80 символов">
            <div class="error error-ad_line2_j"></div>
            <input type="hidden" id="popup-specialist-id" value="' . htmlspecialchars($specialist['id']) . '">
            <input type="hidden" id="popup-forbidden-words-journal" value="' . htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8") . '">
    
          </div>
          <div class="button">
          <button class="journal-continue-btn">Далее</button>
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
            Продвижение в журнале
          </div>
          <div class="popup-content">
          <p class="text-only-tariff">
          <div class="mb-100">
            <span id="journal-name"></span> <span class="right-side" id="journal-price"></span><br>
            <hr>
            <span class="popup-connected-tariff-bold">Период</span> <span class="right-side"></span>
            <span class="right-side" id="journal-period"></span><br>
            <p class="right-side light start_end_date">с <span id="start-date-journal"></span> по <span id="end-date-journal"></span></p>
            <span class="right-side" id="popup-tariff-duration"></span><br>
            </div>
            <div class="for-pay"><span class="popup-connected-tariff-bold">К оплате: </span><span id="total-price-journal"></span></div>
            <p class="reklam-text">*Рекламный текст проверит модератор, после чего на электронную почту вам будет направлена ссылка для оплаты размещения.</p>
            <div class="button">
            <button type="submit" class="submit-btn">Отправить на модерацию</button>
            </div>
            </p>
            </div>
            <input type="hidden" name="journal_id" id="popup-journal-id-2">
            <input type="hidden" name="journal_name" id="popup-journal-name-2">
            <input type="hidden" value="' . $specialist['id'] . '" name="specialist_id" id="popup-specialist-id-2">
            <input type="hidden" name="start_date" id="popup-start-date-journal">
            <input type="hidden" name="end_date" id="popup-end-date-journal">
            <input type="hidden" name="description" id="popup-description-journal">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>';

$services = get_services_for_menu_with_prochie_last();
$promo_options = get_all_promo();
$promo_blocks = get_all_promo_blocks();

// Преобразуем массив в ассоциативный по ID для удобного доступа
$promo_data = [];
foreach ($promo_blocks as $block) {
  $promo_data[$block['id']] = $block;
}

$id_to_slug = [
  3 => 'yuristy-i-advokaty',
  4 => 'yoga-studii',
  5 => 'notariusy',
];

foreach ($id_to_slug as $id => $slug) {
  if (isset($promo_data[$id])) {
    $service = get_service_parent_catalog($slug);
    if ($service && isset($service['title'])) {
      $cat_title = mb_strtolower($service['title']);
      $title = 'Страница "Все ' . $cat_title . '"';
      $promo_data[$id]['title'] = $title;
      $promo_data[$id]['title_escaped'] = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    }
  }
}

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
              <div id="select-toggle-promo" class="select-toggle input selected-text-promo">Выберите место для размещения</div>
              <div class="select-dropdown">
             
              <label class="option-label">Страницы</label>';
// Главная страница с двумя выборами
$popup_promo_html .= <<<EOF
<div class="select-toggle-parrent select-option service-icon m1_home">Главная страница</div>
<ul class="select-dropdown-childrens">
    <li>
        <label class="select-option">
            <input type="radio" name="service_ids[]" value="block_top_specialists" data-name="{$promo_data[1]['title']}" data-price-block1="{$promo_data[1]['price_block1']}" data-price-block2="{$promo_data[1]['price_block2']}">
            <span>{$promo_data[1]['title']}</span>
        </label>
    </li>
    <li>
        <label class="select-option">
            <input type="radio" name="service_ids[]" value="block_specialist_positions" data-name="{$promo_data[2]['title']}" data-price-block1="{$promo_data[2]['price_block1']}" data-price-block2="{$promo_data[2]['price_block2']}">
            <span>{$promo_data[2]['title']}</span>
        </label>
    </li>
</ul>
EOF;

if (in_array($specialist_classification, ['1', '2', '5', '6', '7']) || in_array($specialist_classification, [1, 2, 5, 6, 7])) {
  $escaped_title = htmlspecialchars($promo_data[3]['title'], ENT_QUOTES, 'UTF-8');
  $popup_promo_html .= <<<EOF
        <label class="select-option service-icon m1_vse_uristy">
            <input type="radio" name="service_ids[]" value="" data-block="1" data-name="{$promo_data[3]['title_escaped']}" data-price-block1="{$promo_data[3]['price_block1']}" data-price-block2="{$promo_data[3]['price_block2']}">
            <span>{$promo_data[3]['title']}</span>
        </label>
        <label style="padding-top: 24px;" class="option-label">Разделы/подразделы каталога</label>
    EOF;
}

if ($specialist_classification == "3" || $specialist_classification == 3 || $specialist_classification == 8) {
  $popup_promo_html .= <<<EOF
        <label class="select-option service-icon m1_vse_uristy">
            <input type="radio" name="service_ids[]" value="" data-block="2" data-name="{$promo_data[4]['title_escaped']}" data-price-block1="{$promo_data[4]['price_block1']}" data-price-block2="{$promo_data[4]['price_block2']}">
            <span>{$promo_data[4]['title']}</span>
        </label>
        <label style="padding-top: 24px;" class="option-label">Разделы/подразделы каталога</label>
    EOF;
}

if ($specialist_classification == "4" || $specialist_classification == 4) {
  $popup_promo_html .= <<<EOF
        <label class="select-option service-icon m1_vse_uristy">
            <input type="radio" name="service_ids[]" value="" data-block="3" data-name="{$promo_data[5]['title_escaped']}" data-price-block1="{$promo_data[5]['price_block1']}" data-price-block2="{$promo_data[5]['price_block2']}">
            <span>{$promo_data[5]['title']}</span>
        </label>
    EOF;
}

foreach ($services as $key => $service) {
  // Если категория юристы или адвокаты (ключи 1, 2, 5, 6, 7)
  if (
    in_array($specialist_classification, ['1', '2', '5', '6', '7']) ||
    in_array($specialist_classification, [1, 2, 5, 6, 7])
  ) {
    if ($key == 1 || $key == 2 || $key == 5 || $key == 6 || $key == 7) {
      foreach ($service[0] as $index => $parrent) {
        $has_children = isset($service[$parrent['id']]);

        // Если у родительской категории есть дочерние элементы, делаем её выпадающим списком
        if ($has_children) {
          $popup_promo_html .= <<<EOF
<div class="select-toggle-parrent select-option {$parrent['slug']} service-icon"
EOF;

          $popup_promo_html .= !empty($parrent['icon'])
            ? ' style="background-image: url(../assets/img/' . $parrent['icon'] . ');"'
            : '';

          $popup_promo_html .= <<<EOF
>{$parrent['title']}</div>
<ul class="select-dropdown-childrens">
  <li>
    <label class="select-option">
      <input type="radio" name="service_ids[]" value="{$parrent['slug']}" data-name='Раздел "{$parrent['title']}"' data-price-block1="{$parrent['price_block1']}" data-price-block2="{$parrent['price_block2']}">
      <span>Страница раздела "{$parrent['title']}"</span>
    </label>
  </li>
EOF;

          $last_sub = '';
          foreach ($service[$parrent['id']] as $child) {
            if (mb_stripos($child['title'], 'прочие') !== false) {
              $last_sub = $child;
            } else {
              $child_title = htmlspecialchars($child['title']);
              $popup_promo_html .= <<<EOF
  <li>
    <label class="select-option">
      <input type="radio" name="service_ids[]" value="{$child['slug']}" data-name='Подраздел "{$child_title}"' data-price-block1="{$child['price_block1']}" data-price-block2="{$child['price_block2']}">
      <span>{$child['title']}</span>
    </label>
  </li>
EOF;
            }
          }

          if ($last_sub) {
            $child_title = htmlspecialchars($last_sub['title']);
            $popup_promo_html .= <<<EOF
  <li>
    <label class="select-option">
      <input type="radio" name="service_ids[]" value="{$last_sub['slug']}" data-name='Подраздел "{$child_title}"' data-price-block1="{$last_sub['price_block1']}" data-price-block2="{$last_sub['price_block2']}">
      <span>{$last_sub['title']}</span>
    </label>
  </li>
EOF;
          }

          $popup_promo_html .= "</ul>";
        } else {
          $child_title = htmlspecialchars($parrent['title']);
          $popup_promo_html .= <<<EOF
<label class="select-option service-icon {$parrent['slug']}"
EOF;

          $popup_promo_html .= !empty($parrent['icon'])
            ? ' style="background-image: url(../assets/img/' . $parrent['icon'] . ');"'
            : '';

          $popup_promo_html .= <<<EOF
>
  <input type="radio" name="service_ids[]" value="{$parrent['slug']}" data-name='Раздел "{$child_title}"' data-price-block1="{$parrent['price_block1']}" data-price-block2="{$parrent['price_block2']}">
  {$parrent['title']}
</label>
EOF;
        }
      }
    }
  }
  if ($specialist_classification == '3' || $specialist_classification == 3 || $specialist_classification == 8) {
    // Если категория экспертизы (ключ 3)
    if ($key == 3) {
      $last_cat = '';
      foreach ($service[0] as $child) {
        if (mb_stripos($child['title'], 'прочие') !== false) {
          $last_cat = $child;
        } else {
          $child_title = htmlspecialchars($child['title']);
          $popup_promo_html .= <<<EOF
          <label class="select-option service-icon {$child['slug']}"
          EOF;

          $popup_promo_html .= !empty($child['icon'])
            ? ' style="background-image: url(../assets/img/' . $child['icon'] . ');"'
            : '';

          $popup_promo_html .= <<<EOF
          >
            <input type="radio" name="service_ids[]" value="{$child['slug']}" data-name='Раздел "{$child_title}"' data-price-block1="{$child['price_block1']}" data-price-block2="{$child['price_block2']}">
            {$child['title']}
          </label>
          EOF;
        }
      }

      // Добавляем последнюю категорию, если она существует
      if ($last_cat) {
        $child_title = htmlspecialchars($last_cat['title']);
        $popup_promo_html .= <<<EOF
        <label class="select-option service-icon {$last_cat['slug']}"
        EOF;

        $popup_promo_html .= !empty($last_cat['icon'])
          ? ' style="background-image: url(../assets/img/' . $last_cat['icon'] . ');"'
          : '';

        $popup_promo_html .= <<<EOF
        >
          <input type="radio" name="service_ids[]" value="{$last_cat['slug']}" data-name='Раздел "{$child_title}"' data-price-block1="{$last_cat['price_block1']}" data-price-block2="{$last_cat['price_block2']}">
          {$last_cat['title']}
        </label>
        EOF;
      }
    }
  }
}

$popup_promo_html .= '
</div>
</div>
<input type="hidden" value="' . $specialist['id'] . '" name="specialist_id" id="popup-specialist-id-promo">';

$index = 1;
foreach ($promo_options as $promo) {
  $id = $index === 1 ? "select-option-promo" : "";
  $class = $index === 2 ? "promo-icon" : "promo-icon"; // Добавляем класс только ко второму элементу
  $popup_promo_html .= '
<label id="' . $id . '" class="select-option select-option-promo">
  <input type="radio" name="promo_id" value="' . $promo['id'] . '">
  <img src="' . htmlspecialchars($promo['url']) . '" alt="Icon" class="' . $class . '">
  <div>
    <div class="promo-title">' . htmlspecialchars($promo['title']) . '</div>
    <div class="promo-description">' . htmlspecialchars($promo['description']) . '</div>
  </div>
</label>';
  $index++;
}


$popup_promo_html .= '
            <input type="text" class="input" id="promo-date-select" name="date" placeholder="Даты (кратно 1 неделе)" readonly>
            <div class="button">
              <button type="submit" class="promo-continue-btn">Далее</button>
            </div>
            <div class="popup-footer hidden">
            <p class="red"></p>
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
      'title' => $promo['service_name'],
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
        <img src="' . htmlspecialchars(str_replace('.svg', '-no-background.svg', $promo['url'])) . '" alt="Icon" class="connected-icon" />
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
        <h3>Подключенные промо-размещения:</h3>
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
$option_status_data = generate_option_status($connected_options_map, $connected_tariffs_map);
$promo_status_data = generate_promo_status($connected_promo_map);
$pro_photo_records = get_pro_photo_records($specialist['id']);
// Генерируем статус фотосъемки
$pro_photo_status = generate_pro_photo_status($pro_photo_records, $specialist['activated_at']);

$discount = 0;
if (isset($connected_tariffs_map[1])) {
  $discount = 10;
} elseif (isset($connected_tariffs_map[2])) {
  $discount = 15;
} elseif (isset($connected_tariffs_map[3])) {
  $discount = 20;
}

$discount_html = '';
if ($discount > 0) {
  $discount_html = '
  <div class="circle-pro-photo">-' . $discount . '%</div>
  <input type="hidden" id="discount-value" value="' . $discount . '">';
}

$top_cards = '
<a href="#tariff-href" class="card">
    <div class="card-topline">
        <h4>Тарифы <span class="status-dot ' . $tariff_status['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-tariff.svg" alt="Тарифы">
    </div>
    <p>Стартовый набор инструментов</p>
    <div class="card-status">
        <span>' . $tariff_status['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</a>
<a href="#promo-href" class="card">
      <div class="card-topline"><h4>Промо <span class="status-dot ' . $promo_status_data['class'] . '"></span></h4>
        ' . $discount_html . '
        <img src="../assets/img/cabinet/card-promo.svg" alt=""></div>
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
    <p>Гибкие настройки</p>
    <div class="card-status">
        <span>' . $option_status_data['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</a>
<a href="/cabinet/pro-photo" class="card">
    <div class="card-topline">
        <h4>PRO-фото <span class="status-dot ' . $pro_photo_status['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-photo.svg" alt="">
    </div>
    ' . ($pro_photo_status['show_discount'] ? '<div class="circle-pro-photo">-50%</div><p>Скидка 50% в первые две недели</p>' : '') . '
    <div class="card-status">
        <span>' . $pro_photo_status['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
    </div>
</a>';


$top_cards_mobile = '
<a href="/cabinet/page_promo?target=tariff-href" class="card">
    <div class="card-topline">
        <h4>Тарифы <span class="status-dot ' . $tariff_status['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-tariff.svg" alt="Тарифы">
    </div>
    <p>Стартовый набор <br>инструментов</p>
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
    <p>Гибкие настройки</p>
    <div class="card-status">
        <span>' . $option_status_data['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt="Стрелка"></span>
    </div>
</a>
<a href="/cabinet/pro-photo" class="card">
    <div class="card-topline">
        <h4>PRO-фото <span class="status-dot ' . $pro_photo_status['class'] . '"></span></h4>
        <img src="../assets/img/cabinet/card-photo.svg" alt="">
    </div>
    ' . ($pro_photo_status['show_discount'] ? '<div class="circle-pro-photo">-50%</div><p>Скидка 50% в первые две недели</p>' : '') . '
    <div class="card-status">
        <span>' . $pro_photo_status['status'] . '</span>
        <span class="arrow-right"><img src="../assets/img/cabinet/card-arrow.svg" alt=""></span>
    </div>
</a>';


$swiper_tariff = '';
$swiper_banner = '';

// Определение массивов конфигураций для разных классификаций
$tariff_slides = [
  'group_1' => [
    'tariff' => [
      '../assets/img/cabinet/tariff-slide1.webp',
      /* '../assets/img/cabinet/tariff-slide2.png',
      '../assets/img/cabinet/tariff-slide3.png',
      '../assets/img/cabinet/tariff-slide4.png',
      '../assets/img/cabinet/tariff-slide5.png', */
    ],
    'banner' => [
      '../assets/img/cabinet/banner-slide1.png',
      '../assets/img/cabinet/banner-slide2.png',
      '../assets/img/cabinet/banner-slide3.png',
    ],
  ],
  'group_2' => [
    'tariff' => [
      '../assets/img/cabinet/tariff-slide1.png',
      /* '../assets/img/cabinet/tariff-slide2.png',
      '../assets/img/cabinet/tariff-slide3-expert.png',
      '../assets/img/cabinet/tariff-slide4-expert.png',
      '../assets/img/cabinet/tariff-slide5-expert.png', */
    ],
    'banner' => [
      '../assets/img/cabinet/banner-slide1.png',
      '../assets/img/cabinet/banner-slide2.png',
      '../assets/img/cabinet/banner-slide3-expert.png',
    ],
  ],
  'notarius' => [
    'tariff' => [
      '../assets/img/cabinet/tariff-slide1.png',
      /*  '../assets/img/cabinet/tariff-slide2.png',
      '../assets/img/cabinet/tariff-slide3-notarius.png',
      '../assets/img/cabinet/tariff-slide4-notarius.png', */
    ],
    'banner' => [
      '../assets/img/cabinet/banner-slide1.png',
      '../assets/img/cabinet/banner-slide2.png',
      '../assets/img/cabinet/banner-slide3-notarius.png',
    ],
  ],
];

$group_1 = [1, 2, 5, 6, 7];
$group_2 = [3, 8];
$group_notarius = [4];

if (in_array($specialist_classification, $group_1)) {
  $selected_group = 'group_1';
} elseif (in_array($specialist_classification, $group_2)) {
  $selected_group = 'group_2';
} elseif (in_array($specialist_classification, $group_notarius)) {
  $selected_group = 'notarius';
} else {
  $selected_group = 'group_1';
}

// Формирование HTML слайдов
$swiper_tariff = implode('', array_map(function ($src) {
  return "<div class=\"swiper-slide slider-img\"><img class=\"tariff-image\" src=\"$src\" alt=\"Tariff Image\" /></div>";
}, $tariff_slides[$selected_group]['tariff']));

$swiper_banner = implode('', array_map(function ($src) {
  return "<div class=\"swiper-slide slider-img\"><img class=\"banner-image\" src=\"$src\" alt=\"Banner Image\" /></div>";
}, $tariff_slides[$selected_group]['banner']));

$data['payment_form'] = '
<!-- Форма для физ. лиц -->
          <div class="tab-content payer-form" id="physical">
            <form id="physical-payment-form">
              <label for="client_email">Email для отправки платежных документов</label>
              <input type="email" name="client_email" placeholder="Укажите адрес электронной почты" value="' . $specialist['email'] . '" readonly>
              <input type="hidden" name="reg_email" value="' . $specialist['email'] . '">
              <input type="hidden" name="service_name" id="js-service-name">
              <input type="hidden" name="pay_amount" id="js-pay-amount">
              <input type="hidden" name="orderId" id="js-order-id">
              <input type="hidden" name="specilaistId" id="js-specialist-id" value="' . $specialist_id . '">

              <!-- Новые скрытые поля -->
              <input type="hidden" name="service_id" id="js-service-id">
              <input type="hidden" name="date_start" id="js-date-start">
              <input type="hidden" name="date_end" id="js-date-end">

              <div style="margin-top: 40px;" class="popup_after_text"></div>

              <div class="button">
                <button type="button" data-type="sbp_default" class="confirm-btn-option physical-form-button">Оплатить по СБП</button>
                <button type="submit" data-type="tochka" class="confirm-btn-option connect-button physical-form-button">Оплатить банковской
                  картой</button>
                <p>Нажимая на кнопку, вы соглашаетесь с <a href="../o-proekte">Условиями</a> предоставления услуг</p>
              </div>
              <div id="qr-container" style="margin-top:20px;"></div>
            </form>
          </div>

          <!-- Форма для ИП -->
          <div class="tab-content payer-form" id="ip_form" style="display: none;">
            <form id="ip-form-payment-form">
              <input type="text" name="inn" placeholder="ИНН" required>
              <label for="client_email">Email для отправки платежных документов</label>
              <input type="email" name="client_email" placeholder="Укажите адрес электронной почты" required>

              <input type="hidden" name="service_name" id="js-service-name-ip">
              <input type="hidden" name="pay_amount" id="js-pay-amount-ip">
              <input type="hidden" name="orderId" id="js-order-id-ip">
              <input type="hidden" name="specilaistId" id="js-specialist-id-ip" value="' . $specialist_id . '">

              <!-- Новые скрытые поля -->
              <input type="hidden" name="service_id" id="js-service-id-ip">
              <input type="hidden" name="date_start" id="js-date-start-ip">
              <input type="hidden" name="date_end" id="js-date-end-ip">

              <div style="margin-top: 40px;" class="popup_after_text"></div>

              <div class="button">
                <button type="button" class="confirm-btn-option legal-form-button" data-action="invoice">Сформировать
                  счёт</button>
                <button type="button" class="confirm-btn-option connect-button sbp-redirect-button">Оплатить по
                  СБП</button>
                <button type="button" class="confirm-btn-option connect-button physical-form-button">Оплатить банковской
                  картой</button>
                <p>Нажимая на кнопку, вы соглашаетесь с <a href="../o-proekte">Условиями</a> предоставления услуг</p>
              </div>
            </form>
          </div>

          <!-- Форма для юр. лиц -->
          <div class="tab-content payer-form" id="legal" style="display: none;">
            <form id="legal-payment-form">
              <input type="text" name="inn" placeholder="ИНН" required>
              <label for="client_email">Email для отправки платежных документов</label>
              <input type="email" name="client_email" placeholder="Укажите адрес электронной почты" required>

              <input type="hidden" name="service_name" id="js-service-name-legal">
              <input type="hidden" name="pay_amount" id="js-pay-amount-legal">
              <input type="hidden" name="orderId" id="js-order-id-legal">
              <input type="hidden" name="specilaistId" id="js-specialist-legal" value="' . $specialist_id . '">

              <!-- Новые скрытые поля -->
              <input type="hidden" name="service_id" id="js-service-id-legal">
              <input type="hidden" name="date_start" id="js-date-start-legal">
              <input type="hidden" name="date_end" id="js-date-end-legal">

              <div style="margin-top: 40px;" class="popup_after_text"></div>

              <div class="button">
                <button type="submit" class="confirm-btn-option legal-form-button" data-action="invoice">Сформировать
                  счёт</button>
                <p>Нажимая на кнопку, вы соглашаетесь с <a href="../o-proekte">Условиями</a> предоставления услуг</p>
              </div>
            </form>
          </div>';

$data['script'] = $script;
$data['faqs'] = $faq_html;
$data['tariff_cards'] = $tariff_html;
$data['connected_tariff'] = $connected_popup;
$data['top_cards'] = $top_cards;
$data['top_cards_mobile'] = $top_cards_mobile;
$data['mobile_tariff_cards'] = $mobile_tariff_html;
$data['options'] = $options_html;
/* $data['banners'] = $banner_html; */
$data['promo_connected'] = $promo_html;
/* $data['journal'] = $journal_html; */
$data['banner-popup'] = $popup_banner_html;
$data['banner-popup2'] = $popup_banner_html2;
$data['journal-popup'] = $popup_journal_html;
$data['journal-popup2'] = $popup_journal_html2;
$data['promo-popup'] = $popup_promo_html;
$data['forbidden-popup'] = $forbidden_popup;
$data['second-promo-popup'] = $second_popup_promo_html;
$data['swiper-tarrif'] = $swiper_tariff;

$data['request_content'] = $request_html;
$data['mobile_request_content'] = $request_html;
/* $data['swiper-banner'] = $swiper_banner; */
$HTML = html_explode($data, $HTML);
