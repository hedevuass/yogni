<?php

//получаем специалиста
$specialist = get_specialist($_SESSION['user']['id']);

$data['specialist_id_input'] = '<input type="hidden" name="photo_specialist_id" id="photo-specialist-id" value="' . $specialist['id'] . '" />';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialist_id_popup = $_POST['specialist_id'] ?? 0;
    $specialist_phone_popup = $_POST['phone'] ?? 0;
    $pro_photo_id_popup = $_POST['pro_photo_id'] ?? '4';
    $pro_photo_name_popup = $_POST['pro_photo_name'];
    $photo_titles = [
        '4'    => 'Съёмка сотрудников',
        '4_1'  => 'Съёмка сотрудников (ПРО-фото)',
        '4_2'  => 'Съёмка сотрудников (Экспресс-фото)',
        '4_3'  => 'Съёмка сотрудников (Фотосессия)',
    ];
    if (isset($photo_titles[$pro_photo_id_popup])) {
        $pro_photo_name_popup = $photo_titles[$pro_photo_id_popup];
    }
    $price_popup = $_POST['price'] ?? 0;
    $emp_quantity = $_POST['emp_quantity'] ?? null;

    if ($specialist_id_popup !== 0 && $pro_photo_id_popup !== '' && $price_popup !== '') {
        add_pro_photo($specialist_id_popup, $specialist_phone_popup, $pro_photo_id_popup, $price_popup, $emp_quantity);

        $notify_data = [
            'type'  => 'photo',
            'date'  => date('d.m.Y'),
            'name'  => !empty($specialist['company']) ? $specialist['company'] : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? '')),
            'user_email' => $specialist['email'] ?? '',
            'phone' => $specialist_phone_popup,
            'employees' => $emp_quantity,
            'title' => $pro_photo_name_popup,
        ];

        // Настройка POST-запроса
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($notify_data),
                'timeout' => 5
            ]
        ];

        $context  = stream_context_create($opts);
        $response = file_get_contents($_SERVER['HTTP_ORIGIN'] . '/actions/mail/note_notification.php', false, $context);

        echo json_encode(['success' => true, 'message' => 'Данные успешно сохранены']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    }
    exit;
}
$pro_photo_records = get_pro_photo_records($specialist['id']);

$pro_photo1 = get_pro_photo_by_id(1);
$pro_photo2 = get_pro_photo_by_id(2);
$pro_photo3 = get_pro_photo_by_id(3);
$pro_photo_for_employee = get_pro_photo_by_id(4);

$faq_pro_photo_array = get_faq_pro_photo();

$faq_page = get_faq_page_by_id(22);
$data['faq_page'] = '<a href="/cabinet/faq/' . $faq_page . '" class="order-photo-btn">Правила выбора фото</a>';

//вывод информации для специалиста
$phone = $specialist['phone'] ? str_replace('+7 ', '', format_phone($specialist['phone'])) : '';
$data['popup_tel_field'] = '<span class="phone-mask">+7</span>
<input type="text" name="phone" class="phone-input" placeholder="(926) 000-00-00" value="' . $phone . '">';

$pro_photo_status = generate_pro_photo_status($pro_photo_records, $specialist['activated_at']);
$show_discount = $pro_photo_status['show_discount']; // Берём флаг из функции

foreach ([$pro_photo1, $pro_photo2, $pro_photo3] as $index => $photo) {
    $i = $index + 1;

    $price = $photo['price'] ?? '';
    $price_old = $photo['price_old'] ?? '';

    if ($pro_photo_status['status'] === 'Проведена' || !$show_discount || empty($price)) {
        // либо скидка не показывается,
        // либо нет новой цены → показываем price_old как обычную цену
        $final_price = $price_old;

        $data["price$i"] = htmlspecialchars_decode($final_price);
        $data["price_old$i"] = '';
        $data["show_discount$i"] = '0';
        $data["discount_text$i"] = '';
        $data["discount_block$i"] = '';

        $data["mob_price$i"] = htmlspecialchars_decode($final_price);
        $data["mob_price_old$i"] = '';
        $data["mob_show_discount$i"] = '0';
        $data["mob_discount_text$i"] = '';
        $data["mob_discount_block$i"] = '';
    } else {
        // скидка активна, и есть обе цены
        $data["price$i"] = htmlspecialchars_decode($price);
        $data["price_old$i"] = htmlspecialchars_decode($price_old);
        $data["show_discount$i"] = '1';
        $data["discount_text$i"] = '';
        $data["discount_block$i"] = '<div class="action-less">-50%</div>';

        $data["mob_price$i"] = htmlspecialchars_decode($price);
        $data["mob_price_old$i"] = htmlspecialchars_decode($price_old);
        $data["mob_show_discount$i"] = '1';
        $data["mob_discount_text$i"] = '';
        $data["mob_discount_block$i"] = '<div class="action-less">-50%</div>';
    }


    // Общие данные (без изменений)
    $data["title$i"]       = htmlspecialchars_decode($photo['title']);
    $data["description$i"] = htmlspecialchars_decode($photo['description']);
    $data["time$i"]        = htmlspecialchars_decode($photo['time']);
    $data["number$i"]      = htmlspecialchars_decode($photo['number']);

    $data["mob_title$i"]       = htmlspecialchars_decode($photo['title']);
    $data["mob_description$i"] = htmlspecialchars_decode($photo['description']);
    $data["mob_time$i"]        = htmlspecialchars_decode($photo['time']);
    $data["mob_number$i"]      = htmlspecialchars_decode($photo['number']);

    $data["popup_description_li$i"] = '';
    $data["mob_popup_description_li$i"] = '';
    if (!empty($photo['popup_description'])) {
        $popup_desc = json_decode($photo['popup_description'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($popup_desc)) {
            foreach ($popup_desc as $line) {
                $line_text = htmlspecialchars(strip_tags($line));
                $data["popup_description_li$i"] .= '<li>' . $line_text . '</li>';
                $data["mob_popup_description_li$i"] .= '<li>' . $line_text . '</li>';
            }
        }
    }
}

$data["popup_description_li4"] = '';
if (!empty($pro_photo_for_employee['popup_description'])) {
    $popup_desc = json_decode($pro_photo_for_employee['popup_description'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($popup_desc)) {
        foreach ($popup_desc as $line) {
            $line_text = htmlspecialchars(strip_tags($line));
            $data["popup_description_li4"] .= '<li>' . $line_text . '</li>';
        }
    }
}

$price_employee = htmlspecialchars_decode($pro_photo_for_employee['price']);
$title_employee = htmlspecialchars_decode($pro_photo_for_employee['title']);
$mob_title_employee = htmlspecialchars_decode($pro_photo_for_employee['title']);
$description_employee = htmlspecialchars_decode($pro_photo_for_employee['description']);
$time_employee = htmlspecialchars_decode($pro_photo_for_employee['time']);
$number_employee = htmlspecialchars_decode($pro_photo_for_employee['number']);

$data['price_old3_2'] = $mobile_price1 = '5990';
$data['price3_2'] = $mobile_price2 = '2990';

$data['price_old2_2'] = '5990';
$data['price2_2'] = '2990';
$data['price1_2'] = '2990';

$data['employee_photo'] = $photo_title_class = $data['add_photo_emp'] = $data['add_company_photo'] = $data['company_photo_select'] = '';

$data['popup_title'] = '<div class="title pro-photo-title"></div>';

//вывод информации для компании
if (!is_null($specialist['company_id'])) {
    $photo_title_class = 'company';
    $data['employee_photo'] = '<div class="pro-photo__employee hidden-comp">
        <div class="employee__wrapper">
            <div class="employee__photos">
                <img src="/assets/img/cabinet/employees-photos.webp">
            </div>
            <div class="employee__text-block">
                <div class="employee__pre-title"></div>
                <div class="employee__title">' . $title_employee . '</span></div>
                <div class="employee__text">' . $description_employee . '</span></div>
                <ul class="employee__descr">
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-watch.svg);">
                        ' . $time_employee . '
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-pic.svg);">
                        ' . $number_employee . '
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-user.svg);">
                    <div class="employee__price-block">
                    ' . $price_employee . '</span>
                    </div>
                    </li>
                </ul>
              <a class="employee__bottom-btn">Заказать</a>  
            </div>
        </div>
    </div>';
    $data['employee_photo_mobile'] = '<div class="pro-photo__employee mobile-only">
        <div class="employee__wrapper">
            <div class="employee__text-block">
                <div class="employee__pre-title"></div>
                <div class="employee__title">' . $title_employee . '</div>
                <div class="employee__text">' . $description_employee . '</div>
                <ul class="employee__descr">
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-watch.svg);">
                        ' . $time_employee . '
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-pic.svg);">
                        ' . $number_employee . '
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-user.svg);">
                        <div class="employee__price-block">
                            ' . $price_employee . '
                        </div>
                    </li>
                </ul>
                <div class="employee__photos">
                    <img src="/assets/img/cabinet/employees-photos.webp">
                    <a class="employee__bottom-btn">Заказать</a>
                </div>
            </div>
        </div>
    </div>';

    $popup_description_li4 =  $data['popup_description_li4'];
    $data['popup_employee_photo'] = '<div class="title-emp">Съёмка сотрудников</div>
            <ul>
              <li>' . $popup_description_li4 . '</li>
               
            </ul>
            <div class="photo-time-emp">' . $time_employee . '</div>
           <div class="price" style="display: inline-flex; gap: 5px;">
              Цена: <div class="photo-price-emp">' . $price_employee . ' </div>
            </div>';


    $data['add_photo_emp'] = '<div class="add-photo-emp">Добавить съемку сотрудников</div>';
    $data['add_company_photo'] = '<div class="add-company-photo">Добавить съемку компании</div>';
    $pro_photos = [
        '4_1' => $pro_photo1,
        '4_2' => $pro_photo2,
        '4_3' => $pro_photo3
    ];

    $options_html = '<option value="4" class="hidden">Выбрать формат</option>';


    foreach ($pro_photos as $value => $photo) {
        $title_raw = $photo['title'];

        $title_decoded = html_entity_decode($title_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $title_clean = strip_tags($title_decoded);

        $options_html .= '<option value="' . $value . '">' . htmlspecialchars($title_clean) . '</option>';
    }

    $data['company_photo_select'] = '
        <div class="photo-select__wrapper">
            <select class="sel" id="company-photo-select">
                ' . $options_html . '
            </select>
            <span class="delete-photo-select">Удалить</span>
        </div>';
    $data['popup_title'] = '<div class="title pro-photo-title company-title"></div>';
}
$data['pro_photo_title'] = '<div class="top-title ' . $photo_title_class . '"><div><span class="orange">Профессиональное</span> фото, которое <span class="orange">работает</span> на вас</div><a id="scrollToFormati" class="button orange">Выбрать формат</a></div>';

//вывод кнопки для мобилки
$data['mobile_buy_button'] = '<div class="pro-photo__mobile-btn">Заказать съёмку за <span class="crossed-price">' . $mobile_price1 . '</span> <span class="skinny">₽</span>' . $mobile_price2 . '₽</div>';

$data['faq_pro_photo_data'] = '';

if (!empty($faq_pro_photo_array)) {
    foreach ($faq_pro_photo_array as $faq_item) {
        $question = htmlspecialchars($faq_item['title'], ENT_QUOTES, 'UTF-8');
        $answer = htmlspecialchars_decode($faq_item['description']);
        $data['faq_pro_photo_data'] .= '
            <div class="faq-item">
                <button class="faq-question">
                    <div>' . $question . '</div>
                    <img src="../assets/img/cabinet/arrow-faq-down.svg" alt="" />
                </button>
                <div class="faq-answer">
                    <p>' . $answer . '</p>
                </div>
            </div>';
        $data['faq_pro_photo_data_mob'] .= '
            <div class="faq-item">
                <button class="faq-question">
                    <div>' . $question . '</div>
                    <img src="../assets/img/cabinet/arrow-faq-down.svg" alt="" />
                </button>
                <div class="faq-answer">
                    <p>' . $answer . '</p>
                </div>
            </div>';
    }
}

if (is_array($data['user'])) {
    $data['user'] = '';
}
$keys = [
    'show_discount1',
    'discount_text1',
    'discount_block1',
    'mob_show_discount1',
    'mob_discount_text1',
    'mob_discount_block1',
    'price1_2',
    'price_old1_2',
    'show_discount1_2',
    'discount_text1_2',
    'discount_block1_2',
    'mob_price1_2',
    'mob_price_old1_2',
    'mob_show_discount1_2',
    'mob_discount_text1_2',
    'mob_discount_block1_2',
    'price2_2',
    'price_old2_2',
    'show_discount2_2',
    'discount_text2_2',
    'discount_block2_2',
    'mob_price2_2',
    'mob_price_old2_2',
    'mob_show_discount2_2',
    'mob_discount_text2_2',
    'mob_discount_block2_2',
    'price3_2',
    'price_old3_2',
    'show_discount3_2',
    'discount_text3_2',
    'discount_block3_2',
    'mob_price3_2',
    'mob_price_old3_2',
    'mob_show_discount3_2',
    'mob_discount_text3_2',
    'mob_discount_block3_2',
    'mob_show_discount3',
    'mob_show_discount2',
    'mob_show_discount1',
    'show_discount3',
    'show_discount2',
    'popup_description_li1',
    'popup_description_li2',
    'popup_description_li3',
    'mob_popup_description_li1',
    'mob_popup_description_li2',
    'mob_popup_description_li3'    
];

// Проходим по всем ключам и очищаем те, что не являются массивами
foreach ($keys as $key) {

    if (strpos($key, 'popup_description_li') === 0) {
        continue;
    }

    if (strpos($key, 'mob_popup_description_li') === 0) {
        continue;
    }

    if (!is_array($data[$key])) {
        // Для ключей с префиксом mob_ оборачиваем в <div class="mobile-only">
        if (strpos($key, 'mob_') === 0) {
            $data[$key] = '<div class="mobile-only"></div>'; // Пустой div для мобильных ключей
        } else {
            $data[$key] = ''; // Обычная очистка для остальных ключей
        }
    }
}

$HTML = html_explode($data, $HTML);
