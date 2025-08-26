<?php

// Контейнер
$data = [];

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");

$company_id = get_specialist($_SESSION['user']['id'])['company_id'];

// Получаем сотрудников компании
$employees = get_company_employees($company_id);

// Формируем HTML документов
if ($employees) {
    foreach ($employees as $key => $employee) {
        $data['employees'] .= <<<EOF
            <tr>
                <td>
                    <div class="employee-info-mobile">
                    <input type="text"
                            value="{$employee['name']} {$employee['lastname']}, {$employee['post']}"
                            readonly>
                    </div>
                    <input type="text"
                        name="name[$key]"
                        data-key="{$key}"
                        placeholder="Имя (не более 12 символов)"
                        maxlength="12"
                        value="{$employee['name']}">
                </td>
                <td>
                    <input type="text"
                        name="lastname[$key]"
                        data-key="{$key}"
                        placeholder="Фамилия (не более 15 символов)"
                        maxlength="15"
                        value="{$employee['lastname']}">
                </td>
                <td>
                    <input type="text"
                        name="post[$key]"
                        data-key="{$key}"
                        placeholder="Должность (не более 20 символов)"
                        maxlength="20"
                        value="{$employee['post']}">
                </td>
                <td>
                    <div class="doc">
                        <a onclick="$(this).siblings('input[type=file]').click()">{$employee['photo']}</a>
                        <input type="file" name="{$key}" hidden>
                    </div>
                </td>
                <td><a class="edit-employee-mobile"></a><a class="delete"></a></td>
            </tr>
            <tr class="invalid-feedback-tr">
               <td colspan="5">
                    <div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div>
                </td>
            </tr>
            <input type="hidden" id="popup-forbidden-words" value="{$escaped_forbidden_words}">
        EOF;
    }
}

// Добавляем пустые инпуты
$count = $employees ? count($employees) : 0;
$empty_input = ((5 - $count) > 0) ? (5 - $count) : 1;

for ($i = 0; $i < $empty_input; $i++) {
    $key = $count + 1 + $i;

    $data['employees'] .= <<<EOF
    <tr class="mob__empty">
        <td>
            <div class="employee-info-mobile"><input type="text" readonly></div>
            <input type="text" name="name[$key]" data-key="{$key}" placeholder="Имя (не более 12 символов)" maxlength="12">
        </td>
        <td>
            <input type="text" name="lastname[$key]" placeholder="Фамилия (не более 15 символов)" maxlength="15">
        </td>
        <td>
            <input type="text" name="post[$key]" placeholder="Должность (не более 20 символов)" maxlength="20">
        </td>
        <td>
            <a class="upload" onclick="$(this).siblings('input').click()">Загрузить</a>
            <input type="file" name="{$key}" hidden>
        </td>
        <td>
            <a class="edit-employee-mobile"></a><a class="delete disabled"></a>
        </td>
    </tr>
    <tr class="invalid-feedback-tr">
        <td colspan="5">
            <div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div>
        </td>
    </tr>
    EOF;
}
// Добавляем id компании
$data['employees'] .= '<input type="hidden" name="company_id" value="' . $company_id . '">';
// Выводим на фронт
$HTML_ = explode('<!--data:employees-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:employees-->{$data['employees']}<!--data:employees-->" . $HTML_[2];
