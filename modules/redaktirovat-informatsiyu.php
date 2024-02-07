<?php

$data = [];

$user = get_specialist($_SESSION['user']['id']);

$data['main_info'] = "<div class='block1'>";

$data['main_info'] .= input_photo($user);

$data['main_info'] .= input_company($user);

$data['main_info'] .= <<<EOF
        <div class="item">
            <p>Имя</p>
            <input type="text" name="name" placeholder="Ваше имя" value="{$user['name']}">
        </div>
        <div class="item">
            <p>Фамилия</p>
            <input type="text" name="lastname" placeholder="Ваша фамилия" value="{$user['lastname']}">
        </div>
EOF;

$data['main_info'] .= input_change_status($user);

$data['main_info'] .= <<<EOF
        <div class="item">
            <p>Город</p>
            <input type="hidden" name="city_id" value="{$user['city_id']}">
            <input type="text" name="city" placeholder="Основной город" data-id="{$user['city_id']}" value="{$user['city']}">
        </div>
EOF;

if(!$user['company']) {
    // Декодируем его учреждения образования
    $universities = json_decode($user['university'], true) ?: [];
    $data['main_info'] .= input_university($universities);
}

$data['main_info'] .= input_foundation($user);

$data['main_info'] .= input_about_block($user);

$HTML = html_explode($data, $HTML);


/**
 * @param array $universities
 * @return string
 */
function input_university(array $universities)
{
    // Формируем HTML документов
    $code = <<<EOF
    <div class="item">
        <p>Образование</p>
        <div class="university-inputs">
    EOF;
    if ($universities) {
        foreach ($universities as $key => $university) {
            $code .= <<<EOF
                <input type="text" name="university[$key]" data-key="{$key}" placeholder="Наименование учебного заведения" value="{$university}">
            EOF;
        }
    } else {
        $code .= <<<EOF
            <input type="text" name="university[1]" data-key="1" placeholder="Наименование учебного заведения" value="">
        EOF;
    }
    $code .= <<<EOF
        </div>
        <span class="add-university">Добавить образование</span>
    </div>
    EOF;
    return $code;
}

/**
 * @param array $user
 * @return string
 */
function input_photo(array $user)
{
    if ($user['photo']) { 
        $photo = '/uploads/avatars/'.$user['photo'];
        $class='refresh';
        $btn = '<i></i>Изменить';
    } else {
        $photo = '/assets/img/cabinet/no-photo.svg';
        $class='';
        $btn = '<i></i>Загрузить';
    }
    $code = <<<EOF
        <div class="item photo__item">
            <p class="photo__title">Фото профиля</p>
            <div class="flex photo__wrapper">
                <div class="photo__block">
                    <div class="photo__image {$class}" style="background-image: url('{$photo}')"></div>
                    <input type="file" name="photo_file" id="photo_file">
                    <label for="photo_file" class="photo__btn {$class}">
                        {$btn}
                    </label>
                </div>
                <div class="photo__rules-block">
                </div>
            </div>
        </div>
    EOF;
    return $code;
}

/**
 * @param array $user
 * @return string
 */
function input_company(array $user)
{
    if ($user['company']) {
        $code = <<<EOF
            <div class="item">
                <p>Название компании</p>
                <input type="hidden" name="company_id" value="{$user['company_id']}">
                <input type="text" name="company" placeholder="Название компании" value="{$user['company']}">
            </div>
        EOF;

        return $code;
    }
}

/**
 * @param array $user
 * @return string
 */
function input_change_status(array $user)
{
    $classifications = get_classifications();
    $code = <<<EOF
        <div class="item">
            <p class="large-margin">Статус</p>
    EOF;
    if (in_array($user['classification_id'], [1, 2])) {

        foreach ($classifications as $key => $classification) {
            if ($key > 2) break;

            $checked = $key == $user['classification_id'] ? 'checked' : '';

            $code .= <<<EOF
                <input type="radio" class="radio" id="classification{$key}" name="classification" value="$key" $checked>
                <label for="classification{$key}">{$classification['title']}</label>
            EOF;
        }
        
    } else if (in_array($user['classification_id'], [5, 6, 7])) {
        foreach ($classifications as $key => $classification) {
            if ($key < 5 || $key > 7) continue;

            $checked = $key == $user['classification_id'] ? 'checked' : '';

            $code .= <<<EOF
                <input type="radio" class="radio" id="classification{$key}" name="classification" value="$key" $checked>
                <label for="classification{$key}">{$classification['title']}</label>
            EOF;
        }
    } else {
        $code .= <<<EOF
            <input type="radio" class="radio" id="classification{$user['classification_id']}" name="classification" value="{$user['classification_id']}" checked>
            <label for="classification{$user['classification_id']}">{$user["classification"]}</label>
        EOF;
    }

    $code .= "</div>";

    return $code;
}

/**
 * @param array $user
 * @return string
 */
function input_foundation(array $user): string
{
    $placeholder = $user['company'] ? 'Год основания' : 'Опыт работы';
    $subplaceholder = $user['company'] ? 'Укажите год основания компании' : 'Полных лет практики';
    $max_value = $user['company'] ? intval(date("Y")) : 50;
    $def_value = $user['foundation'] ?? ($user['company'] ? intval(date("Y")) : 1);
    $class = is_null($user['foundation']) ? 'default' : '';
    $code = <<<EOF
        <div class="item">
            <p>$placeholder</p>
            <p class="sub">$subplaceholder</p>
            <div class="quantity_inner">
                <span class="bt_minus">-</span>
                <input type="text" name="foundation" class="quantity {$class}" value="{$def_value}" data-max-count="{$max_value}">
                <span class="bt_plus">+</span>
            </div>
        </div>
    EOF;

    return $code;
}


/**
 * @param array $user
 * @return string
 */
function input_about_block(array $user)
{
    if ($user['company']) {
        $title = 'О компании';
        $descr = 'Расскажите о своей компании, успешно завершенных делах и любимых направлениях деятельности. Любая информация может быть полезна.';
    } else {
        $title = 'О себе';
        $descr = 'Расскажите о своем опыте, успешно завершенных делах и любимых направлениях деятельности. Любая информация может быть полезна.';
    }
    $code = <<<EOF
        </div>
            <div class="block2">
            <div class="name">
                {$title}
            </div>
            <p>{$descr}</p>
            <p class="orange">Внимание: контакты размещаются в отдельном блоке.</p>
            <textarea name="description" placeholder="Текст">{$user['description']}</textarea>
        </div>
    EOF;
    return $code;
}