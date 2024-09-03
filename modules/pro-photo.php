<?php

//получаем специалиста
$specialist = get_specialist($_SESSION['user']['id']);

//вывод информации для специалиста
$phone = $specialist['phone'] ? str_replace('+7 ', '', format_phone($specialist['phone'])) : '';
$data['popup_tel_field'] = '<span class="phone-mask">+7</span>
<input type="text" name="phone" placeholder="926 000-00-00" value="' . $phone . '">';

$data['price_old3'] = $mobile_price1 = '5990';
$data['price3'] = $mobile_price2 = '2990';

$data['price_old2'] = '5990';
$data['price2'] = '2990';
$data['price1'] = '2990';

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
                <div class="employee__title">Ваша <span class="orange">команда</span> – настоящие <span class="orange">звёзды</span></div>
                <div class="employee__text">Персональные фото сотрудников компании: для портфолио, промо-материалов и продвижения бренда. Фото от профессионалов <span class="orange">для профессионалов</span>.</div>
                <ul class="employee__descr">
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-watch.svg);">
                        ~10 минут / чел.
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-pic.svg);">
                        1-2 фото в обработке / чел.
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-user.svg);">
                    <div class="employee__price-block">
                    2000 <span class="currency">₽.</span> <span class="note"> / чел.</span>
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
                <div class="employee__title">Ваша <span class="orange">команда</span> – настоящие <span class="orange">звёзды</span></div>
                <div class="employee__text">Персональные фото сотрудников компании: для портфолио, промо-материалов и продвижения бренда. Фото от профессионалов <span class="orange">для профессионалов</span>.</div>
                <ul class="employee__descr">
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-watch.svg);">
                        ~10 минут / чел.
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-pic.svg);">
                        1-2 фото в обработке / чел.
                    </li>
                    <li style="background-image: url(/assets/img/cabinet/pro-photo-user.svg);">
                    <div class="employee__price-block">
                    2000 <span class="currency">руб.</span> <span class="note"> / чел.</span>
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

    $data['price_old3'] = '19990';
    $data['price3'] = '9990';
    $data['price_old2'] = $mobile_price1 = '11990';
    $data['price1'] = $data['price2'] = $mobile_price2 = '5990';

    $data['price_old3_2'] = '19990';
    $data['price3_2'] = '9990';
    $data['price_old2_2'] = $mobile_price1 = '11990';
    $data['price1_2'] = $data['price2_2'] = $mobile_price2 = '5990';

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
$data['pro_photo_title'] = '<div class="top-title ' . $photo_title_class . '"><div><span class="orange">Профессиональное</span> фото, которое <span class="orange">работает</span> на вас</div><div class="descr">Фотография - это важнейший инструмент продвижения профиля, формирующий первое впечатление</div><a href="#formati" class="button orange">Выбрать формат</a></div>';

//вывод кнопки для мобилки
$data['mobile_buy_button'] = '<div class="pro-photo__mobile-btn">Заказать съёмку за <span class="crossed-price">' . $mobile_price1 . '</span> <span class="skinny">₽</span>' . $mobile_price2 . '₽</div>';

$HTML = html_explode($data, $HTML);
