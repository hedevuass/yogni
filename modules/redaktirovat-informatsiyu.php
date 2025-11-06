<?php

$data = [];

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");

$user = get_specialist($_SESSION['user']['id']);

$data['main_info'] = "<div class='block1'>";

$data['main_info'] .= input_photo($user);

$data['main_info'] .= input_company($user);

$nameLabel = $user['company'] ? 'Имя контактного лица' : 'Имя';
$lastnameLabel = $user['company'] ? 'Фамилия контактного лица' : 'Фамилия';

$namePlaceholder = $user['company'] ? 'Имя контактного лица' : 'Ваше имя';
$lastnamePlaceholder = $user['company'] ? 'Фамилия контактного лица' : 'Ваша фамилия';

// Если у пользователя есть компания (контактное лицо) — поля редактируемые
$readonlyAttr = $user['company'] ? '' : 'readonly';

$data['main_info'] .= <<<EOF
    <div class="item">
        <p>{$nameLabel}</p>
        <input type="text" name="name" placeholder="{$namePlaceholder}" value="{$user['name']}" {$readonlyAttr}>
    </div>
    <div class="item">
        <p>{$lastnameLabel}</p>
        <input type="text" name="lastname" placeholder="{$lastnamePlaceholder}" value="{$user['lastname']}" {$readonlyAttr}>
    </div>
EOF;

$data['main_info'] .= input_change_status($user);

$data['main_info'] .= <<<EOF
        <div class="item">
            <p>Город</p>
            <input type="hidden" name="city_id" value="{$user['city_id']}" readonly>
            <input type="hidden" id="popup-forbidden-words" value="{$escaped_forbidden_words}">
            <input type="text" name="city" placeholder="Основной город" data-id="{$user['city_id']}" value="{$user['city']}" readonly>
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
    $code = <<<EOF
    <div class="item">
        <p>Образование</p>
        <div class="university-inputs">
    EOF;

    if ($universities) {
        foreach ($universities as $key => $university) {
            $university = htmlspecialchars($university, ENT_QUOTES); // защита от XSS
            $code .= <<<EOF
                <input type="text" name="university[$key]" data-key="{$key}" placeholder="Наименование учебного заведения (не более 70 символов)" value="{$university}" maxlength="70">
                <div class="invalid-feedback" style="display: none;">Превышено допустимое количество символов</div>
            EOF;
        }
    } else {
        $code .= <<<EOF
            <input type="text" name="university[1]" data-key="1" placeholder="Наименование учебного заведения (не более 70 символов)" value="" maxlength="70">
            <div class="invalid-feedback" style="display: none;">Превышено допустимое количество символов</div>
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
            <p class="photo__title">Фото профиля (jpeg, png)</p>
            <div style="justify-content: center;" class="flex photo__wrapper">
                <div style="padding: 8px 0 16px;  min-width: 0; min-height: 0;" class="photo__block">
                    <div class="photo__image {$class}" style="background-image: url('{$photo}')"></div>
                    <label class="photo-btn" for="photo_file"></label>
                    <input type="file" name="photo_file" id="photo_file">
                    <div class="photo__rules-block">
                </div>
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
                <input type="text" name="company" placeholder="Название компании" value="{$user['company']}" readonly>
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

    $description = strip_tags(html_entity_decode($user['description']));

    $code = <<<EOF
        </div>
        <div class="block2">
            <div class="name">
                {$title}
            </div>
            <p>{$descr}</p>
            <p class="orange">Внимание: контакты размещаются в отдельном блоке.</p>
            <textarea name="description" placeholder="Текст (не более 850 символов)" maxlength="850">{$description}</textarea>
            <div class="invalid-feedback" style="display: none;">Превышено допустимое количество символов</div>
        </div>
    EOF;

    return $code;
}