<?php

$data = [];

$user = get_specialist($_SESSION['user']['id']);

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");
$data['rooms_forbidden-words'] .= <<<EOF
<input type="hidden" id="popup-forbidden-words" value="{$escaped_forbidden_words}">
EOF;

$user_rooms = json_decode($user['rooms'] ?? '[]', true) ?: [];
$photos_per_room = 4;     // фото на один зал

$data['rooms_info'] = "<div class='block1 block rooms-editor-block'>";
$data['rooms_info'] .= "<h2 class='rooms-editor-title hidden-comp'>Залы для занятий (формат фото - jpeg, png)</h2>";

$data['rooms_info'] .= "<style>
    .room-photo-item.empty .delete-btn { display: none !important; }
    .room-photo-item.has-image .upload-btn { display: none !important; }
</style>";

$data['rooms_info'] .= "<div class='rooms-editor-list' id='rooms-editor-list'>";

// Лимита по количеству залов нет — всегда добавляем один пустой в конец для удобного добавления
$rooms_to_render = $user_rooms;
$rooms_to_render[] = ['name' => '', 'photos' => []];

while (count($rooms_to_render) < 1) {
    $rooms_to_render[] = ['name' => '', 'photos' => []];
}

foreach ($rooms_to_render as $r => $room) {
    $room_name = htmlspecialchars($room['name'] ?? '', ENT_QUOTES);
    $room_photos = $room['photos'] ?? [];
    $is_empty_room = ($room_name === '' && empty($room_photos));
    $item_class_extra = $is_empty_room ? ' is-empty-room' : '';
    $doc_loaded_class = $is_empty_room ? '' : ' loaded';

    $data['rooms_info'] .= "<div class='room-editor-item{$item_class_extra}' data-room-index=\"{$r}\">";

    // Строка с именем зала
    $data['rooms_info'] .= <<<EOF
        <div class="room-name-row">
            <div class="room-input-col">
                <input
                    type="text"
                    name="rooms[{$r}][name]"
                    class="room-name-input"
                    placeholder="Наименование зала (не более 35 символов)"
                    maxlength="35"
                    value="{$room_name}"
                >
                
                <!-- Кнопки действий для мобильной версии -->
                <div class="doc{$doc_loaded_class} mobile-only">
                    <a onclick="$(this).siblings('input[type=file]').click()"></a>
                </div>
                <div class="room-mobile-actions">
                    <a class="edit-docs-mobile" title="Редактировать"></a>
                </div>

                <!-- Десктопная кнопка удаления -->
                <div class="room-action-btn room-delete-btn desktop-only-btn {$item_class_extra}" title="Удалить зал">
                    <img src="/assets/img/cabinet/delete.svg">
                    <img src="/assets/img/cabinet/delete_hover.svg">
                    <img src="/assets/img/cabinet/delete-disabled.svg">
                </div>
            </div>
        </div>
    EOF;

    // Сетка из 4 фото для этого зала (Отображается на десктопе)
    $data['rooms_info'] .= "<div class='room-photos-grid'>";

    for ($i = 0; $i < $photos_per_room; $i++) {
        $photo_name = $room_photos[$i] ?? null;
        $item_class = $photo_name ? 'has-image' : 'empty';
        $style = $photo_name ? "style=\"background-image: url('/uploads/rooms/{$photo_name}');\"" : "";
        $hidden_val = $photo_name ? htmlspecialchars($photo_name, ENT_QUOTES) : "";

        $data['rooms_info'] .= <<<EOF
            <div class="room-photo-item {$item_class}" data-photo-index="{$i}" {$style}>

                <div class="room-action-btn delete-btn" title="Удалить фото">
                    <img src="/assets/img/cabinet/gallery-delete.svg">
                    <img src="/assets/img/cabinet/gallery-delete-hover.svg">
                </div>

                <label class="room-action-btn upload-btn" for="room_{$r}_photo_{$i}" title="Добавить фото">
                    <img src="/assets/img/cabinet/gallery-upload.svg">
                    <img src="/assets/img/cabinet/gallery-upload-hover.svg">
                </label>

                <input type="file" name="rooms[{$r}][photos][{$i}]" id="room_{$r}_photo_{$i}" class="room-photo-file-input" accept="image/jpeg, image/png" style="display: none;">

                <input type="hidden" name="rooms[{$r}][existing_photos][{$i}]" class="existing-room-photo-input" value="{$hidden_val}">
            </div>
        EOF;
    }

    $data['rooms_info'] .= "</div>"; // .room-photos-grid
    $data['rooms_info'] .= "</div>"; // .room-editor-item
}

$data['rooms_info'] .= "</div>"; // .rooms-editor-list

$data['rooms_info'] .= "<span class='under_table-text'>Для ускорения загрузки сохраняйте информацию после добавления каждого поля.</span>";
$data['rooms_info'] .= "<span class='add add-room-btn'>Добавить зал</span>";

$data['rooms_info'] .= "</div>"; // .rooms-editor-block

$HTML = html_explode($data, $HTML);
?>