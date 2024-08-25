<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['phone'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $data['phone'] = '+7' . $data['phone'];
    // Валидация телефонного номера
    if (!$data['phone'] = validation_phone($data['phone'])) $json['error']['phone'] = 'Укажите корректный номер телефона';

    // Адрес
    // Оставляем так, потом будем проверять с Яндекс api
    if (!$data['city_id']) $json['error']['city'] = 'Укажите ваш город';
    if (!$data['metro_id']) $data['metro_id'] = null;
    if (!$data['house']) $data['house'] = null;
    if (!$data['how_to_get']) $data['how_to_get'] = null;

    // Валидация email
    if ($data['email']) {
        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $json['error']['email'] = 'Некорректный email';
    }
    $data['coordinates'] = '';
    if ($data['city'] && $data['street']) {
        $main_adress = $data['city'] . ', ' . $data['street'];
        if ($data['house']) {
            $main_adress .= ', ' . $data['house'];
        }
        // Отправляем запрос к сервису геокодирования (пример для Яндекс.Карт)
        $geocode_response = file_get_contents('https://geocode-maps.yandex.ru/1.x/?format=json&apikey=b0c770d0-eb80-4f0e-bb33-f3d1bb289c95&geocode=' . urlencode($main_adress));
		$geocode_data = json_decode($geocode_response, true);

        // Получаем координаты из ответа
        $data['coordinates'] = $geocode_data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'] ?? '';
    }

    // Валидация доп сетей
    if ($data['tg']) {
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
    }

    $data['other'] = json_encode([
        'email'     => $data['email'],
        'site'      => $data['site'],
        'tg'        => $data['tg'],
        'vk'        => $data['vk'],
        'dzen'      => $data['dzen']
    ]);

    // График работы
    $data['schedule'] = [];

    foreach ($data['schedule_start'] as $key => $value) {
        $data['schedule'][$key] = [
            'start' => $value,
            'end'   => $data['schedule_end'][$key],
            'b_start' => isset($data['break_start'][$key]) && !empty($data['break_start'][$key]) ? $data['break_start'][$key] : false,
            'b_end' => isset($data['break_end'][$key]) && !empty($data['break_end'][$key]) ? $data['break_end'][$key] : false
        ];

        if (($data['schedule_end'][$key] == '0' && $data['schedule_start'][$key] == '0')) {
            $data['schedule'][$key] = ['start' => false, 'end' => false, 'b_start' => false, 'b_end' => false];
        }
        if (!isset($data['days'][$key])) {
            $data['schedule'][$key]['vac'] = true;
        }
    }

    $data['schedule'] = json_encode($data['schedule']);



    if (isset($json['error'])) responseJson($json);

    if (update_contacts($data) && update_city($data['city_id']) && update_schedule($data['schedule'])) {

        $phone = format_phone($data['phone']);
        // Возвращаем превью карточки
        $json['success'] = <<<EOF
            <li><a href="tel:{$data['phone']}"><img src="/assets/img/cabinet/contacts1.svg"> {$phone}</a></li>
        EOF;
        $contacts = json_decode($data['other']);
        foreach ($contacts as $key => $contact) {
            $class = '';
            if (!$contact) {
                $class .= 'hidden';
            }
            $json['success'] .= "<li class='$class'>";

            switch ($key) {
                case 'email':
                    $json['success'] .=  "<a href='mailto:{$contact}'><img src='/assets/img/cabinet/contacts2.svg'> {$contact}</a>";
                    break;
                case 'site':
                    $json['success'] .=  "<a href='https://{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts3.svg'> Сайт</span></a>";
                    break;
                case 'tg':
                    $json['success'] .=  "<a href='https://t.me/{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts4.svg'> Телеграм</span></a>";
                    break;
                case 'vk':
                    $json['success'] .=  "<a href='https://vk.com/{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts5.svg'> Вконтакте</span></a>";
                    break;
                case 'dzen':
                    $json['success'] .=  "<a href='https://dzen.ru/{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts6.svg'> Яндекс Дзен</span></a>";
                    break;

                default:
                    # code...
                    break;
            }

            $json['success'] .= "</li>";
        }
    } else {
        $json['error']['fatal_error'] = 'Обновление информации временно не работает';
    }

    responseJson($json);
}
