<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['phone'])) {
    foreach ($_POST as $key => $value) {
        if (in_array($key, ['how_to_get'])) {
            $data[$key] = clean_elem_blog_title($value);
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }
    $data['phone'] = '+7' . $data['phone'];
    // Валидация телефонного номера
    if (!$data['phone'] = validation_phone($data['phone']))
        $json['error']['phone'] = 'Укажите корректный номер телефона';

    // Адрес
    // Оставляем так, потом будем проверять с Яндекс api
    if (!$data['city_id'])
        $json['error']['city'] = 'Укажите ваш город';
    if (!$data['metro_id'])
        $data['metro_id'] = null;
    if (!$data['house'])
        $data['house'] = null;
    if (!$data['how_to_get'])
        $data['how_to_get'] = null;

    // Валидация email
    if ($data['email']) {
        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            $json['error']['email'] = 'Некорректный email';
    }
    $data['coordinates'] = '';

    if ($data['city'] && $data['street']) {
        // Собираем адрес (переменную пишем без опечаток с двумя 's')
        $main_address = $data['city'] . ', ' . $data['street'];
        if ($data['house']) {
            $main_address .= ', ' . $data['house'];
        }

        // Параметры для URL
        $params = [
            'apikey' => 'a078c003-a00e-4d49-aed7-b3b61be29fd7',  // Наш новый рабочий ключ
            'geocode' => $main_address,
            'format' => 'json',
            'lang' => 'ru_RU'
        ];

        $geocode_url = 'https://geocode-maps.yandex.ru/1.x/?' . http_build_query($params);

        // Контекст с Referer для обхода блокировки 403
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: PHP-Backend\r\n"
                    . "Referer: https://yogoort.ru/\r\n"
            ]
        ]);

        // Делаем запрос
        $geocode_response = @file_get_contents($geocode_url, false, $context);

        if ($geocode_response) {
            $geocode_data = json_decode($geocode_response, true);

            // Безопасно вытаскиваем координаты
            if (!empty($geocode_data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'])) {
                $data['coordinates'] = $geocode_data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];

                // Разбираем координаты, если это необходимо дальше по коду
                $coords_array = explode(' ', $data['coordinates']);
                if (count($coords_array) === 2) {
                    $longitude = (float) $coords_array[0];
                    $latitude = (float) $coords_array[1];
                }
            }
        }
    }

    // Валидация доп сетей
    /* if ($data['tg']) {
        $url = explode('@', $data['tg']);
        $data['tg'] = end($url);
    }

    if ($data['vk']) {
        $url = explode('/', $data['vk']);
        $data['vk'] = end($url);
    }

    if ($data['dzen']) {
        $url = explode('/', $data['dzen']);
        $data['dzen'] = end($url);
    } */

    $data['other'] = json_encode([
        'email' => $data['email'],
        'site' => $data['site'],
        'tg' => $data['tg'],
        'vk' => $data['vk'],
        'max' => $data['max'],
        'dzen' => $data['dzen']
    ]);

    // График работы
    $data['schedule'] = [];

    foreach ($data['schedule_start'] as $key => $value) {
        $hasSchedule = !(
            $data['schedule_start'][$key] === '0' &&
            $data['schedule_end'][$key] === '0'
        );

        // 2) Проверяем, установлен ли чекбокс "перерыв"
        $breakChecked = isset($data['break_active'][$key]);

        if (!$hasSchedule) {
            $data['schedule'][$key] = [
                'start' => false,
                'end' => false,
                'b_start' => false,
                'b_end' => false
            ];
        } else {
            $b_start = false;
            $b_end = false;

            if (
                $breakChecked &&
                isset($data['break_start'][$key]) &&
                $data['break_start'][$key] !== '' &&
                isset($data['break_end'][$key]) &&
                $data['break_end'][$key] !== ''
            ) {
                $b_start = $data['break_start'][$key];
                $b_end = $data['break_end'][$key];
            }

            $data['schedule'][$key] = [
                'start' => $data['schedule_start'][$key],
                'end' => $data['schedule_end'][$key],
                'b_start' => $b_start,
                'b_end' => $b_end
            ];
        }

        if (!isset($data['days'][$key])) {
            $data['schedule'][$key]['vac'] = true;
        }
    }

    $data['schedule'] = json_encode($data['schedule']);

    if (isset($json['error']))
        responseJson($json);

    if (update_contacts($data) && update_city($data['city_id']) && update_schedule($data['schedule'])) {
        $phone = format_phone($data['phone']);
        // Возвращаем превью карточки
        $json['success'] = <<<EOF
                <li class="contacts-tel"><a href="tel:{$data['phone']}"><img src="/assets/img/cabinet/contacts1.svg"> {$phone}</a></li>
            EOF;
        $contacts = json_decode($data['other'], true);
        $order = ['email', 'site', 'vk', 'dzen', 'max'];
        foreach ($order as $key) {
            if (!array_key_exists($key, $contacts))
                continue;
            $contact = $contacts[$key];

            $class = '';
            if (!$contact) {
                $class .= 'hidden';
            }
            if ($key === 'email') {
                $class .= ' contacts-email';
            }
            $json['success'] .= "<li class='$class'>";

            switch ($key) {
                case 'email':
                    $json['success'] .= "<a href='mailto:{$contact}'><img src='/assets/img/cabinet/contacts2.svg'> {$contact}</a>";
                    break;
                case 'site':
                    $json['success'] .= "<a href='{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts3.svg'> Сайт</span></a>";
                    break;
                /* case 'tg':
                    $json['success'] .=  "<a href='https://t.me/{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts4.svg'> Телеграм</span></a>";
                    break; */
                case 'vk':
                    $json['success'] .= "<a href='{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts5.svg'> Вконтакте</span></a>";
                    break;
                case 'dzen':
                    $json['success'] .= "<a href='{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts6.svg'> Яндекс Дзен</span></a>";
                    break;
                case 'max':
                    $json['success'] .= "<a href='{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts-max-hover.svg'> MAX</span></a>";
                    break;

                default:
                    // code...
                    break;
            }

            $json['success'] .= '</li>';
        }
    } else {
        $json['error']['fatal_error'] = 'Обновление информации временно не работает';
    }

    responseJson($json);
}
