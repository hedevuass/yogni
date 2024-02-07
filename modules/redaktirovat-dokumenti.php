<?php

// Контейнер
$data = [];

// Получаем опсание специалиста
$specialist_description = get_specialist_description($_SESSION['user']['id']);

// Декодируем его документы
$documents = is_null($specialist_description['document']) ? [] : (json_decode($specialist_description['document'], true) ?: []);

// Формируем HTML документов
foreach ($documents as $key => $document) {
    $data['documents'] .= <<<EOF
        <tr>
            <td><input type="text" name="name[$key]" data-key="{$key}" placeholder="Наименование документа" value="{$document['name']}"></td>
            <td>
                <div class="doc">
                    <a href="/uploads/documents/{$document['file']}" target="_blank">{$document['file']}</a>
                </div>
            </td>
            <td><a class="edit-docs-mobile"></a><a class="delete"></a></td>
        </tr>
    EOF;
}

$last_doc = array_key_last($documents);
// Добавляем пустые инпуты
$count = count($documents);
$empty_input = ((5 - $count) > 0) ? (5 - $count) : 1;

for ($i = 0; $i < $empty_input; $i++) {
    $key = $last_doc + $i + 1;

    $data['documents'] .= <<<EOF
        <tr class="mob__empty">
            <td><input type="text" name="name[$key]" data-key="{$key}" placeholder="Наименование документа"></td>
            <td>
                <a class="upload" onclick="$(this).siblings('input').click()">Загрузить</a>
                <input type="file" name="{$key}" hidden>
            </td>
            <td><a class="edit-docs-mobile"></a><a class="delete disabled"></a></td>
        </tr>
    EOF;
}

// Выводим на фронт
$HTML_ = explode( '<!--data:documents-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:documents-->{$data['documents']}<!--data:documents-->" . $HTML_[2];