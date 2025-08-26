<?php

// Контейнер
$data = [];

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");

// Получаем опсание специалиста
$specialist_description = get_specialist_description($_SESSION['user']['id']);

// Декодируем его документы
$documents = is_null($specialist_description['document']) ? [] : (json_decode($specialist_description['document'], true) ?: []);

// Формируем HTML документов
foreach ($documents as $key => $document) {
    $data['documents'] .= <<<EOF
    <tr style="white-space: nowrap;">
        <td><input type="text" name="name[$key]" data-key="{$key}" placeholder="Наименование документа (не более 65 символов)" maxlength="65" value="{$document['name']}"><div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div></td>
        <td>
            <div class="doc">
                <!-- Старая ссылка меняем: убираем href, добавляем класс и onclick -->
                <a onclick="$(this).siblings('input[type=file]').click()">
                    {$document['file']}
                </a>
                <!-- Тут добавляем hidden file-input -->
                <input type="file" name="{$key}" hidden>
            </div>
        </td>
        <td>
            <a class="edit-docs-mobile"></a>
            <a class="delete"></a>
        </td>
        <input type="hidden" id="popup-forbidden-words" value="{$escaped_forbidden_words}">
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
        <tr style="white-space: nowrap;" class="mob__empty">
            <td><input type="text" name="name[$key]" data-key="{$key}" placeholder="Наименование документа (не более 65 символов)" maxlength="65"><div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div></td>
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