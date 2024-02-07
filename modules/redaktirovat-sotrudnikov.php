<?php

// Контейнер
$data = [];

$company_id = get_specialist($_SESSION['user']['id'])['company_id'];

// Получаем сотрудников компании
$employees = get_company_employees($company_id);

// Формируем HTML документов
if ($employees) {
    foreach ($employees as $key => $employee) {
        $data['employees'] .= <<<EOF
            <tr>
                <td>
                    <div class="employee-info-mobile"><input type="text" value="{$employee['name']} {$employee['lastname']}, {$employee['post']}" readonly></div>
                    <input type="text" name="name[$key]" data-key="{$key}" placeholder="Имя" value="{$employee['name']}">
                </td>
                <td><input type="text" name="lastname[$key]" data-key="{$key}" placeholder="Фамилия" value="{$employee['lastname']}"></td>
                <td><input type="text" name="post[$key]" data-key="{$key}" placeholder="Должность" value="{$employee['post']}"></td>
                <td>
                    <div class="doc">
                        <a href="/uploads/employee/{$employee['photo']}" target="_blank">{$employee['photo']}</a>
                    </div>
                </td>
                <td><a class="edit-employee-mobile"></a><a class="delete"></a></td>
            </tr>
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
        <td><div class="employee-info-mobile"><input type="text" readonly></div><input type="text" name="name[$key]" data-key="{$key}" placeholder="Имя"></td>
        <td><input type="text" name="lastname[$key]" placeholder="Фамилия"></td>
        <td><input type="text" name="post[$key]" placeholder="Должность"></td>
        <td>
            <a class="upload" onclick="$(this).siblings('input').click()">Загрузить</a>
            <input type="file" name="{$key}" hidden>
        </td>
        <td><a class="edit-employee-mobile"></a><a class="delete disabled"></a></td>
    </tr>
    EOF;
}
// Добавляем id компании
$data['employees'] .= '<input type="hidden" name="company_id" value="'.$company_id.'">';
// Выводим на фронт
$HTML_ = explode( '<!--data:employees-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:employees-->{$data['employees']}<!--data:employees-->" . $HTML_[2];