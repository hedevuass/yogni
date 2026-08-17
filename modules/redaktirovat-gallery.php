<?php

$data = [];


$user = get_specialist($_SESSION['user']['id']);

$user_photos = json_decode($user['gallery'] ?? '[]', true) ?: []; 
$max_photos = 12;

$data['main_info'] = "<div class='block1 block gallery-block'>";
$data['main_info'] .= "<h2 class='gallery-title hidden-comp'>Галерея</h2>";
$data['main_info'] .= "<p class='gallery-desc'>Максимальное количество фотографий - 12 (jpeg, png). Первое фото также станет вашей аватаркой на страницах сайта.</p>";

$data['main_info'] .= "<div class='gallery-grid'>";

// Добавим микро-стиль прямо сюда, чтобы не раздувать основной файл стилей
$data['main_info'] .= "<style>
    .gallery-item.empty .delete-btn { display: none !important; }
    .gallery-item.has-image .upload-btn { display: none !important; }
</style>";

for ($i = 0; $i < $max_photos; $i++) {
    // Для первого слота берем главную аватарку, для остальных — фотки из JSON
    $photo_name = ($i === 0 && !empty($user['photo'])) ? $user['photo'] : ($user_photos[$i - 1] ?? null);
    $folder = ($i === 0) ? 'avatars' : 'gallery';
    
    $item_class = $photo_name ? 'has-image' : 'empty';
    $style = $photo_name ? "style=\"background-image: url('/uploads/{$folder}/{$photo_name}');\"" : "";
    $hidden_val = $photo_name ? htmlspecialchars($photo_name, ENT_QUOTES) : "";

    $data['main_info'] .= <<<EOF
    <div class="gallery-item {$item_class}" data-index="{$i}" {$style}>
        
        <div class="gallery-action-btn delete-btn" title="Удалить фото">
            <img src="/assets/img/cabinet/gallery-delete.svg">
            <img src="/assets/img/cabinet/gallery-delete-hover.svg">
        </div>

        <label class="gallery-action-btn upload-btn" for="gallery_file_{$i}" title="Добавить фото">
            <img src="/assets/img/cabinet/gallery-upload.svg">
            <img src="/assets/img/cabinet/gallery-upload-hover.svg">
        </label>
        
        <input type="file" name="gallery_files[{$i}]" id="gallery_file_{$i}" class="gallery-file-input" accept="image/jpeg, image/png" style="display: none;">
        
        <input type="hidden" name="existing_photos[{$i}]" class="existing-photo-input" value="{$hidden_val}">
    </div>
EOF;
}

$data['main_info'] .= "</div>"; 
$data['main_info'] .= "<p class='gallery-footer'>Для ускорения загрузки сохраняйте информацию после добавления каждого фото.</p>";
$data['main_info'] .= "<span class='mob-add mobile-only-gallery gallery-add-photo'>Добавить фотографии</span>";
$data['main_info'] .= "</div>"; 

$HTML = html_explode($data, $HTML);