<?php

//получаем специалиста
$specialist = get_specialist($_SESSION['user']['id']);

$pro_photo1 = get_pro_photo_by_id(1);
$pro_photo2 = get_pro_photo_by_id(2);
$pro_photo3 = get_pro_photo_by_id(3);
$pro_photo_for_employee = get_pro_photo_by_id(4);

$faq_pro_photo_array = get_faq_pro_photo();

//вывод информации для специалиста
$phone = $specialist['phone'] ? str_replace('+7 ', '', format_phone($specialist['phone'])) : '';
$data['popup_tel_field'] = '<span class="phone-mask">+7</span>
<input type="text" name="phone" placeholder="926 000-00-00" value="' . $phone . '">';

$data['price_old3'] = htmlspecialchars_decode($mobile_price1 = $pro_photo3['price_old']);
$data['price3'] = htmlspecialchars_decode($mobile_price2 = $pro_photo3['price']);
$data['title3'] = htmlspecialchars_decode($pro_photo3['title']);
$data['mob-title3'] = htmlspecialchars_decode($pro_photo3['title']);
$data['description3'] = $pro_photo3['description'];
$data['time3'] = htmlspecialchars_decode($pro_photo3['time']);
$data['number3'] = htmlspecialchars_decode($pro_photo3['number']);

// Присваиваем данные для записи с id = 2
$data['price_old2'] = htmlspecialchars_decode($pro_photo2['price_old']);
$data['price2'] = htmlspecialchars_decode($pro_photo2['price']);
$data['title2'] = htmlspecialchars_decode($pro_photo2['title']);
$data['mob-title2'] = htmlspecialchars_decode($pro_photo2['title']);    
$data['description2'] = htmlspecialchars_decode($pro_photo2['description']);
$data['time2'] = htmlspecialchars_decode($pro_photo2['time']);
$data['number2'] = htmlspecialchars_decode($pro_photo2['number']);

// Присваиваем данные для записи с id = 1
$data['price1'] = htmlspecialchars_decode($pro_photo1['price']);
$data['title1'] = htmlspecialchars_decode($pro_photo1['title']);
$data['mob-title1'] = htmlspecialchars_decode($pro_photo1['title']);
$data['description1'] = htmlspecialchars_decode($pro_photo1['description']);
$data['time1'] = htmlspecialchars_decode($pro_photo1['time']);
$data['number1'] = htmlspecialchars_decode($pro_photo1['number']);

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

$data['popup_description_li'] = 'Профессиональная портретная бизнес-фотосессия';
$data['popup_title'] = '<div class="title"></div>';

//вывод информации для компании
if (!is_null($specialist['company_id'])) {
    $data['popup_description_li'] = 'Съёмка групповых портретов, а также общих планов';
    $photo_title_class = 'company';
    $data['employee_photo'] = '<div class="pro-photo__employee hidden-comp">
        <div class="employee__wrapper">
            <div class="employee__photos">
                <img src="/assets/img/cabinet/employees-photos.png">
            </div>
            <div class="employee__text-block">
                <div class="employee__pre-title"></div>
                <div class="employee__title">'. $title_employee .'</span></div>
                <div class="employee__text">'. $description_employee .'</span></div>
                <ul class="employee__descr">
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-watch.svg);">
                        '. $time_employee .'
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-pic.svg);">
                        '. $number_employee .'
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-user.svg);">
                    <div class="employee__price-block">
                    '. $price_employee .'</span>
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
                <div class="employee__title">'. $title_employee .'</div>
                <div class="employee__text">'. $description_employee .'</div>
                <ul class="employee__descr">
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-watch.svg);">
                        '. $time_employee .'
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-pic.svg);">
                        '. $number_employee .'
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-user.svg);">
                        <div class="employee__price-block">
                            '. $price_employee .'
                        </div>
                    </li>
                </ul>
                <div class="employee__photos">
                    <img src="/assets/img/cabinet/employees-photos.png">
                    <a class="employee__bottom-btn">Заказать</a>
                </div>
            </div>
        </div>
    </div>';
    $data['popup_employee_photo'] = '<div class="title-emp">Съёмка сотрудников</div>
            <ul>
              <li>Индивидуальная фотосессия для сотрудников</li>
               <li>'. $number_employee .'</li>
            </ul>
            <div class="photo-time-emp">'. $time_employee .'</div>
           <div class="price" style="display: inline-flex; gap: 5px;">
              Цена: '. $price_employee .'
            </div>';


    $data['add_photo_emp'] = '<div class="add-photo-emp">Добавить съемку сотрудников</div>';
    $data['add_company_photo'] = '<div class="add-company-photo">Добавить съемку компании</div>';
    $data['company_photo_select'] = '
        <div class="photo-select__wrapper">
            <select class="sel" id="company-photo-select">
                <option value="0" class="hidden">Выбрать формат</option>
                <option value="1">Про-фото</option>
                <option value="2">Экспресс-фото</option>
                <option value="3">Фотосессия</option>
            </select>
            <span class="delete-photo-select">Удалить</span>
        </div>';
    $data['popup_title'] = '<div class="title company-title"></div>';
}
$data['pro_photo_title'] = '<div class="top-title ' . $photo_title_class . '"><div><span class="orange">Профессиональное</span> фото, которое <span class="orange">работает</span> на вас</div><a href="#formati" class="button orange">Выбрать формат</a></div>';

//вывод кнопки для мобилки
$data['mobile_buy_button'] = '<div class="pro-photo__mobile-btn">Заказать съёмку за <span class="crossed-price">' . $mobile_price1 . '</span> <span class="skinny">₽</span>' . $mobile_price2 . '₽</div>';

$data['faq_pro_photo_data'] = '';

if (!empty($faq_pro_photo_array)) {
    foreach ($faq_pro_photo_array as $faq_item) {
        $question = htmlspecialchars($faq_item['title'], ENT_QUOTES, 'UTF-8');
        $answer = htmlspecialchars($faq_item['description'], ENT_QUOTES, 'UTF-8');
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

$HTML = html_explode($data, $HTML);
