<?php

/**
 * @param array $specialist
 * @return string
 */
function experience(array $specialist): string
{
    $content = '';

    if ($specialist['foundation']) {
        $foundation = (int)$specialist['foundation'];

        if ($foundation < 1000) {
            $experience = $foundation;

            if ($experience <= 0) {
                $insert = 'менее года';
            } else {
                $insert = num_decline($experience, 'год, года, лет');
            }

            if (!$specialist['company']) {
                $content = "<span>Опыт работы:</span> {$insert}";
            }
        } else {
            if ($specialist['company']) {
                $content = "<span>Год основания:</span> {$foundation}";
            } else {
                $experience = date('Y') - $foundation;

                if ($experience <= 0) {
                    $insert = 'менее года';
                } else {
                    $insert = num_decline($experience, 'год, года, лет');
                }

                $content = "<span>Опыт работы:</span> {$insert}";
            }
        }
    }

    if ($content !== '') {
        return '<div class="year">' . $content . '</div>';
    }

    return '';
}

// Функция склонения
function pluralForm(int $number, string $one, string $few, string $many): string
{
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    if ($mod10 == 1 && $mod100 != 11) return $one;
    if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) return $few;
    return $many;
}

// Блок специалистов
function block_catalog(array $specialists, $class = false, bool $randomSpecialization = false)
{
    $data = '';

    $all_equipments = function_exists('get_all_equipment') ? get_all_equipment() : [];

    foreach ($specialists as $key => $specialist) {
        // Определение класса карточки и стилей
        $cl = (!$class) ? '' : $class;
        $style = '';

        // Получаем информацию о специалисте и тарифах
        $specialist_info = get_specialist($specialist['id']);
        $connected_tariffs = get_connected_tariffs($specialist['id']);
        $specialist_id = $specialist_info['id'] ?? $specialist['id'];

        $studio_slug = !empty($specialist_info['slug']) ? $specialist_info['slug'] : 'id' . $specialist_info['id'];
        $studio_url = "/studio/{$studio_slug}";

        // Избранное и Контакты
        $other = !empty($specialist_info['other']) ? json_decode($specialist_info['other'], true) : [];
        $spec_email = htmlspecialchars($other['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $fav_all = array_map('intval', $_SESSION['favorite']['all'] ?? []);
        $fav_class = in_array((int)$specialist_id, $fav_all, true) ? 'active' : '';
        $has_email = !empty($other['email']);

        // Проверяем наличие тарифов с ID = 2 и 3
        $tariff2Connected = false;
        $tariff3Connected = false;
        if (!empty($connected_tariffs) && is_array($connected_tariffs)) {
            foreach ($connected_tariffs as $tariff) {
                if (($tariff['tariff_id'] ?? null) == 2) $tariff2Connected = true;
                if (($tariff['tariff_id'] ?? null) == 3) $tariff3Connected = true;
            }
        }

        // Бейдж ТОП-студия
        $topSpecialistBadge = '';
        if ($tariff3Connected) {
            $topSpecialistBadge = <<<HTML
            <div style="position: absolute; left: 33px; top: 33px; max-width: 118px; z-index: 45; width: 100%; height: 28px; background: #F8A3AAE5; border-radius: 40px; color: #fff; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 14px; font-weight: 300; pointer-events: none;">
                ТОП-cтудия
                <img src='/assets/img/cabinet/fire-card.svg' alt="топ-студия">
            </div>
HTML;
        }

        // 1. Обработка аватара
        if (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/catalog_' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/catalog_' . $specialist['photo'];
            $image_class = '';
        } elseif (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/' . $specialist['photo'];
            $image_class = 'rounded';
        } else {
            $specialist['photo_path'] = !empty($specialist['company_name']) ? '/uploads/avatars/022.webp' : '/uploads/avatars/02.webp';
            $image_class = 'rounded';
        }

        $displayName = !empty($specialist['company_name']) 
            ? $specialist['company_name'] 
            : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));

        $classification = $specialist_info['classification'] ?? '';
        $alt_text = htmlspecialchars(trim($classification . ' ' . $displayName), ENT_QUOTES, 'UTF-8');

        // 2. Сборка галереи / Swiper
        $slides_html = '';
        $has_gallery = false;

        if (!empty($specialist['photo_path'])) {
            $slides_html .= '<div class="swiper-slide">';
            $slides_html .= '<div class="image-box">';
            $slides_html .= '<img src="' . $specialist['photo_path'] . '" alt="' . $alt_text . '" class="' . $image_class . '">';
            $slides_html .= '</div>';
            $slides_html .= '</div>';
        }

        $gallery_photos = [];
        if (!empty($specialist_info['gallery'])) {
            $gallery_photos = is_array($specialist_info['gallery'])
                ? $specialist_info['gallery']
                : (json_decode($specialist_info['gallery'], true) ?: []);
        }
        $gallery_photos = array_filter($gallery_photos);

        if (!empty($gallery_photos)) {
            $has_gallery = true;
            foreach ($gallery_photos as $photo) {
                $gallery_photo_path = '/uploads/gallery/catalog_' . $photo;
                $slides_html .= '<div class="swiper-slide">';
                $slides_html .= '<div class="image-box">';
                $slides_html .= '<img src="' . $gallery_photo_path . '" alt="' . $alt_text . '" class="' . $image_class . '" loading="lazy">';
                $slides_html .= '</div>';
                $slides_html .= '</div>';
            }
        }

        $classification_id_safe = $specialist_info['classification_id'] ?? '';

        // Кнопка избранного
        $fav_button_html = <<<EOF
        <button type="button" class="btn-fav company-fav catalog-fav-btn {$fav_class}" data-id="{$specialist_id}" data-cat="{$classification_id_safe}">
            <input type="hidden" id="favorite_cat" value="{$classification_id_safe}">
            <input type="hidden" id="favorite_id" value="{$specialist_id}">
            <img src='/assets/img/cabinet/izbrannoe-heart-card.svg' class="icon-default icon-fav" alt="" loading="lazy">
            <img src='/assets/img/cabinet/izbrannoe-heart-card-blue.svg' class="icon-hover icon-fav" style="display:none;" alt="" loading="lazy">
            <img src='/assets/img/cabinet/izbrannoe-heart-card-active-catalog.svg' class="icon-active icon-fav" style="display:none;" alt="" loading="lazy">
            <img src='/assets/img/cabinet/izbrannoe-heart-card-active-hover.svg' class="icon-active-hover icon-fav" style="display:none;" alt="" loading="lazy">
        </button>
EOF;

        // Изображение / Слайдер
        if ($has_gallery) {
            $image_block_html = <<<EOF
            <a href="{$studio_url}" target="_blank" rel="noopener noreferrer" style="border-radius: 16px;" class="swiper spec-card-inline-swiper">
                <div class="swiper-wrapper">
                    {$slides_html}
                </div>
                <div class="swiper-button-prev gallery-arrow-prev"></div>
                <div class="swiper-button-next gallery-arrow-next"></div>
                <div class="swiper-pagination mobile-gallery-pagination"></div>
            </a>
EOF;
        } else {
            $image_block_html = <<<EOF
            <a href="{$studio_url}" target="_blank" rel="noopener noreferrer">
                <div class="image-box">
                    <img src="{$specialist['photo_path']}" alt="{$alt_text}" class="{$image_class}" style="border-radius: 16px;">
                </div>
            </a>
EOF;
        }

        // 3. Расчет рейтинга
        $rating = isset($specialist_info['rating']) ? min(floatval($specialist_info['rating']), 5) : 0;
        $rating_formatted = number_format($rating, 1);
        $reviews_count = isset($specialist_info['count_rating']) ? (int)$specialist_info['count_rating'] : 0;
        $star_class = $reviews_count > 0 ? 'fa fa-star' : 'fa fa-star-o';

        // 4. Формирование строки Город + Метро в одну строку
        $location_parts = [];
        if (!empty($specialist['city_name'])) {
            $location_parts[] = 'г. ' . $specialist['city_name'];
        }
        if (!empty($specialist['metro_name'])) {
            $location_parts[] = 'м. ' . $specialist['metro_name'];
        }

        $location_html = '';
        if (!empty($location_parts)) {
            $location_text = implode(', ', $location_parts);
            $location_html = <<<EOF
            <div class="city" style="margin-top: 4px;">
                <div class="city-name" style="margin-top: 4px;">
                    {$location_text}
                </div>
            </div>
EOF;
        }

        // 5. Минимальная цена
        $services = get_specialist_service($specialist['id']);
        $minPrice = null;
        if ($services && is_array($services)) {
            foreach ($services as $svc) {
                if (preg_match('/\d+/', trim($svc['price'] ?? ''), $matches)) {
                    $priceNum = (int)$matches[0];
                    if ($minPrice === null || $priceNum < $minPrice) $minPrice = $priceNum;
                }
            }
        }

        $price_html = '';
        if ($minPrice !== null) {
            $formattedMinPrice = number_format($minPrice, 0, '.', "'");
            $price_html = <<<EOF
            <div class="meta-price" style="margin-top: 16px;">от {$formattedMinPrice} ₽</div>
EOF;
        }

        $selected_equipment = function_exists('get_specialist_equipment') ? get_specialist_equipment($specialist['id']) : [];
        $equipments = [];
        $amenities = [];

        if (!empty($all_equipments) && is_array($all_equipments) && !empty($selected_equipment)) {
            foreach ($all_equipments as $item) {
                if (array_key_exists($item['id'], $selected_equipment)) {
                    $category = trim($item['category'] ?? '');

                    if (in_array($category, ['equipment', 'equipments'])) {
                        $equipments[] = $item;
                    } elseif (in_array($category, ['amenity', 'amenities'])) {
                        $amenities[] = $item;
                    }
                }
            }
        }

        // Сортировка каждой группы по алфавиту
        $sortByName = function ($a, $b) {
            return mb_strtolower(trim($a['name'] ?? '')) <=> mb_strtolower(trim($b['name'] ?? ''));
        };
        usort($equipments, $sortByName);
        usort($amenities, $sortByName);

        // Функция рендеринга иконок
        $renderIcons = function ($items) {
            $html = '';
            foreach ($items as $item) {
                if (!empty($item['img'])) {
                    if (strpos($item['img'], '<svg') !== false) {
                        $html .= '<div class="studio-mini-icon-item studio-mini-icon-item--svg" title="' . htmlspecialchars($item['name']) . '">' . $item['img'] . '</div>';
                    } else {
                        $icon_file = !empty($item['img_pink']) ? $item['img_pink'] : $item['img'];
                        $html .= '<img class="studio-mini-icon-item" src="/assets/img/equipment/' . htmlspecialchars($icon_file) . '" alt="' . htmlspecialchars($item['name']) . '" title="' . htmlspecialchars($item['name']) . '">';
                    }
                }
            }
            return $html;
        };

        $all_features = array_merge($amenities, $equipments);

        $equipment_html = $renderIcons($all_features);

        $features_html = '';
        if (!empty($equipment_html)) {
            $features_html = <<<EOF
            <div class="studio-mini-icons-mobile" style="margin-top: 18px;position: relative;z-index: 40;pointer-events: all;cursor: pointer;">
                <div class="studio-mini-icons-row studio-mini-icons-row--mobile">
                    {$equipment_html}
                </div>
            </div>
        EOF;
        }

        // 6. Описание
        $description_html = '';
        if (!empty($specialist_info['description'])) {
            $description = html_entity_decode(htmlspecialchars_decode($specialist_info['description']));
            $description_html = <<<EOF
            <div class="specialist-desc cut-text-cards" style="width: 100%; margin-top: 16px;">
                <noindex>{$description}</noindex>
            </div>
EOF;
        }

        // 7. Кнопки действий
        $actions_html = <<<EOF
        <div class="actions-column" style="display: flex; flex-direction: column; margin-top: auto; max-width: 100%; width: 100%; align-items: flex-end; gap: 8px; flex-shrink: 0; position: relative; z-index: 10;">
            <div class="buttons-row" style="display: flex; gap: 8px; width: 100%;">
EOF;

        if ($has_email) {
            $actions_html .= <<<EOF
                <button type="button" class="btn-zapis company-write" style="width: 100%; height: 40px; background: #4F77B4; color: #FFFFFF; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 12px; transition: all 0.2s;"
                data-id="{$specialist['id']}" 
                data-email="{$spec_email}"
                >
                    <img src='/assets/img/cabinet/zapis-card.svg' class="icon-default" alt="">
                    <img src='/assets/img/cabinet/zapis-card-blue.svg' class="icon-hover" style="display:none;" alt="">
                    <span>Записаться на занятие</span>
                </button>
EOF;
        }

        $actions_html .= <<<EOF
            </div>
        </div>
EOF;

        // 8. Финальная сборка карточки
        $data .= <<<EOF
        <div class="item {$cl}" style="{$style}">
            {$topSpecialistBadge}
            <div class="image">
                {$fav_button_html}
                {$image_block_html}
            </div>
            
            <a href="{$studio_url}" target="_blank" rel="noopener noreferrer" class="link"></a>
            <div class="rocket" style="{$rocketStyle}"></div>

            <div class="main-info-wrapper" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; position: relative; padding: 0;">
                <div class="left-info-side" style="flex: 1;">
                    <!-- Заголовок + Рейтинг в одной строке -->
                    <div class="name-rating-row" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;">
                        <div class="name cut-title-catalog" style="font-weight: 500;">{$displayName}</div>
                        <div class="rating" style="display: flex; align-items: center; gap: 4px; margin: 0; flex-shrink: 0;">
                            <span>
                                <i class="{$star_class}"></i> <span>{$rating_formatted}</span>
                            </span>
                            <span style="color: #6F6F6F; font-weight: 300;">
                                ({$reviews_count})
                            </span>
                        </div>
                    </div>

                    {$location_html}
                    {$price_html}
                    {$features_html}
                </div>
            </div>

            {$description_html}
            {$actions_html}
        </div> 
EOF;
    }

    return $data;
}

function block_mobile(array $specialists, $class = false, bool $randomSpecialization = false)
{
    $data = '';

    $all_equipments = function_exists('get_all_equipment') ? get_all_equipment() : [];

    foreach ($specialists as $key => $specialist) {
        // Определение класса карточки и стилей
        $cl = (!$class) ? '' : $class;
        $style = ''; 

        // Получаем расширенную информацию о специалисте и тарифах
        $specialist_info = get_specialist($specialist['id']);
        $connected_tariffs = get_connected_tariffs($specialist['id']);
        $specialist_id = $specialist['id'];

        $studio_slug = !empty($specialist_info['slug']) ? $specialist_info['slug'] : 'id' . $specialist_info['id'];
        $studio_url = "/studio/{$studio_slug}";

        // Проверяем наличие тарифов
        $tariff2Connected = false;
        $tariff3Connected = false;
        if (!empty($connected_tariffs) && is_array($connected_tariffs)) {
            foreach ($connected_tariffs as $tariff) {
                if (($tariff['tariff_id'] ?? null) == 2) $tariff2Connected = true;
                if (($tariff['tariff_id'] ?? null) == 3) $tariff3Connected = true;
            }
        }

        $rocketStyle = $tariff3Connected ? 'display: block;' : 'display: none;';

        // 1. Обработка главного фото (аватара)
        if (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/catalog_' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/catalog_' . $specialist['photo'];
            $image_class = '';
        } elseif (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/' . $specialist['photo'];
            $image_class = 'rounded';
        } else {
            $specialist['photo_path'] = !empty($specialist['company_name']) ? '/uploads/avatars/022.webp' : '/uploads/avatars/02.webp';
            $image_class = 'rounded';
        }

        $displayName = !empty($specialist['company_name']) 
            ? $specialist['company_name'] 
            : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));

        // 2. Сборка слайдов для Swiper
        $slides_html = '';
        $has_gallery = false;
        $classification = $specialist_info['classification'] ?? '';
        $alt_text = htmlspecialchars(trim($classification . ' ' . $displayName), ENT_QUOTES, 'UTF-8');

        if (!empty($specialist['photo_path'])) {
            $slides_html .= '<div class="swiper-slide">';
            $slides_html .= '<a href="' . $studio_url . '" target="_blank" style="display: block;" rel="noopener noreferrer" class="image-box">';
            $slides_html .= '<img src="' . $specialist['photo_path'] . '" alt="' . $alt_text . '" class="' . $image_class . '">';
            $slides_html .= '</a>';
            $slides_html .= '</div>';
        }

        $gallery_photos = [];
        if (!empty($specialist_info['gallery'])) {
            $gallery_photos = is_array($specialist_info['gallery'])
                ? $specialist_info['gallery']
                : (json_decode($specialist_info['gallery'], true) ?: []);
        }
        $gallery_photos = array_filter($gallery_photos);

        if (!empty($gallery_photos)) {
            $has_gallery = true;
            foreach ($gallery_photos as $photo) {
                $gallery_photo_path = '/uploads/gallery/catalog_' . $photo;
                $slides_html .= '<div class="swiper-slide">';
                $slides_html .= '<a href="' . $studio_url . '" target="_blank" style="display: block;" rel="noopener noreferrer" class="image-box">';
                $slides_html .= '<img src="' . $gallery_photo_path . '" alt="' . $alt_text . '" class="' . $image_class . '" loading="lazy">';
                $slides_html .= '</a>';
                $slides_html .= '</div>';
            }
        }

        if ($has_gallery) {
            $image_block_html = <<<EOF
            <div style="border-radius: 16px 16px 16px 16px;" class="swiper spec-card-inline-swiper">
                <div class="swiper-wrapper">
                    {$slides_html}
                </div>
                <div class="swiper-button-prev gallery-arrow-prev"></div>
                <div class="swiper-button-next gallery-arrow-next"></div>
                <div class="swiper-pagination mobile-gallery-pagination"></div>
            </div>
EOF;
        } else {
            $image_block_html = <<<EOF
            <a href="{$studio_url}" target="_blank" style="display: block;" rel="noopener noreferrer" class="image-box">
                <img src="{$specialist['photo_path']}" alt="{$alt_text}" class="{$image_class}" style="border-radius: 8px;">
            </a>
EOF;
        }

        // 3. Расчет рейтинга и отзывов
        $rating = isset($specialist_info['rating']) ? min(floatval($specialist_info['rating']), 5) : 0;
        $rating_formatted = number_format($rating, 1);
        $reviews_count = isset($specialist_info['count_rating']) ? (int)$specialist_info['count_rating'] : 0;
        $star_class = $reviews_count > 0 ? 'fa fa-star' : 'fa fa-star-o';
        $reviews_text = function_exists('pluralForm') ? pluralForm($reviews_count, 'отзыв', 'отзыва', 'отзывов') : 'отзывов';

        $quantity = $reviews_count > 0
            ? '<a class="show-reviews">' . $reviews_count . ' ' . $reviews_text . '</a>'
            : '<a class="show-reviews" style="pointer-events: none; cursor: default; text-decoration: none;">' . $reviews_count . ' ' . $reviews_text . '</a>';

        // 4. Расчет минимальной цены
        $services = get_specialist_service($specialist['id']);
        $minPrice = null;
        if ($services && is_array($services)) {
            foreach ($services as $svc) {
                if (preg_match('/\d+/', trim($svc['price'] ?? ''), $matches)) {
                    $priceNum = (int)$matches[0];
                    if ($minPrice === null || $priceNum < $minPrice) $minPrice = $priceNum;
                }
            }
        }

        $description_html = '';
        if (!empty($specialist_info['description'])) {
            $description = html_entity_decode(htmlspecialchars_decode($specialist_info['description']));
            $description_html = <<<EOF
            <div class="specialist-desc cut-text-cards" style="width: 100%; margin-top: 16px; margin-bottom: 16px; font-size: 16px; color: #222222; line-height: 18px; max-height: calc(18px * 4);">
                <noindex>{$description}</noindex>
            </div>
EOF;
        }

        // 5. Формирование строки Город + Метро в одну строку БЕЗ иконок
        $location_parts = [];
        if (!empty($specialist['city_name'])) {
            $location_parts[] = 'г. ' . $specialist['city_name'];
        }
        if (!empty($specialist['metro_name'])) {
            $location_parts[] = 'м. ' . $specialist['metro_name'];
        }

        $location_html = '';
        if (!empty($location_parts)) {
            $location_text = implode(', ', $location_parts);
            $location_html = <<<EOF
            <div class="city" style="margin-bottom: 4px;">
                <div class="city-name" style="margin-top: 4px;">
                    {$location_text}
                </div>
            </div>
EOF;
        }

        $price_html = '';
        if ($minPrice !== null) {
            $formattedMinPrice = number_format($minPrice, 0, '.', "'");
            $price_html = <<<EOF
            <div class="meta-price" style="margin-top:14px;">от {$formattedMinPrice} ₽</div>
EOF;
        }

        $selected_equipment = function_exists('get_specialist_equipment') ? get_specialist_equipment($specialist['id']) : [];
        $equipments = [];
        $amenities = [];

        if (!empty($all_equipments) && is_array($all_equipments) && !empty($selected_equipment)) {
            foreach ($all_equipments as $item) {
                if (array_key_exists($item['id'], $selected_equipment)) {
                    $category = trim($item['category'] ?? '');

                    if (in_array($category, ['equipment', 'equipments'])) {
                        $equipments[] = $item;
                    } elseif (in_array($category, ['amenity', 'amenities'])) {
                        $amenities[] = $item;
                    }
                }
            }
        }

        // Сортировка каждой группы по алфавиту
        $sortByName = function ($a, $b) {
            return mb_strtolower(trim($a['name'] ?? '')) <=> mb_strtolower(trim($b['name'] ?? ''));
        };
        usort($equipments, $sortByName);
        usort($amenities, $sortByName);

        // Функция рендеринга иконок
        $renderIcons = function ($items) {
            $html = '';
            foreach ($items as $item) {
                if (!empty($item['img'])) {
                    if (strpos($item['img'], '<svg') !== false) {
                        $html .= '<div class="studio-mini-icon-item studio-mini-icon-item--svg" title="' . htmlspecialchars($item['name']) . '">' . $item['img'] . '</div>';
                    } else {
                        $icon_file = !empty($item['img_pink']) ? $item['img_pink'] : $item['img'];
                        $html .= '<img class="studio-mini-icon-item" src="/assets/img/equipment/' . htmlspecialchars($icon_file) . '" alt="' . htmlspecialchars($item['name']) . '" title="' . htmlspecialchars($item['name']) . '">';
                    }
                }
            }
            return $html;
        };

        $all_features = array_merge($amenities, $equipments);

        $mobile_html = $renderIcons($all_features);

        // Формируем блок в 2 строки (удобства в 1-й строке, оборудование во 2-й)
        $features_html = '';
        if (!empty($mobile_html)) {
            $features_html = <<<EOF
            <div class="studio-mini-icons-mobile" style="margin-top: 18px;">
                <div class="studio-mini-icons-row studio-mini-icons-row--mobile">
                    {$mobile_html}
                </div>
            </div>
        EOF;
        }

        // Конструкция блока main-info-wrapper с сохранением всех оберток
        $classification_title = $specialist['classification_title'] ?? '';
        $info_wrapper_html = <<<EOF
        <div class="main-info-wrapper" style="display: flex; flex-direction: column; justify-content: flex-start; gap: 8px; position: relative; padding: 0;">
            <div class="left-info-side" style="flex: 1;">
                {$location_html}
                {$price_html}
                {$features_html}
                {$description_html}
            </div>
        </div>
EOF;

        // 6. Сборка списка специализаций
        $specs_html = '';
        $show_specs_list = $show_specs_list ?? true;
        $specs = [];
        $allSpecs = $specialist['all_specializations'] ?? $specialist['specialization'] ?? '';

        if (!empty($specialist['specialization_list']) && is_array($specialist['specialization_list'])) {
            foreach ($specialist['specialization_list'] as $item) {
                $title = is_array($item) ? ($item['title'] ?? '') : (string)$item;
                $title = trim($title);
                if ($title !== '') $specs[] = $title;
            }
        } elseif (!empty($allSpecs)) {
            $raw = trim((string)$allSpecs);
            if (strpos($raw, ',') !== false) {
                $parts = array_map('trim', explode(',', $raw));
            } else {
                $parts = preg_split('/\r\n|\n|\\|/', $raw);
                $parts = array_map('trim', $parts);
            }
            foreach ($parts as $p) {
                if ($p !== '') $specs[] = $p;
            }
        }

        $specText = $specialist['specialization'] ?? '';
        if ($randomSpecialization && !empty($specialist['specialization_list'])) {
            $list = array_column($specialist['specialization_list'], 'title');
            if (!empty($list)) {
                $specText = $list[array_rand($list)];
            }
        }

        // 7. Сборка панели кнопок и бейджа ТОП
        $topSpecialistBadge = '';
        if ($tariff3Connected) {
            $topSpecialistBadge = <<<HTML
            <div style="position: absolute; left: 33px; top: 43px; max-width: 118px; z-index: 25; width: 100%; height: 28px; background: #F8A3AA; border-radius: 40px; color: #fff; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 14px; font-weight: 300; pointer-events: none;">
                ТОП-студия
                <img src='/assets/img/cabinet/fire-card.svg' alt="">
            </div>
HTML;
        }

        $specialist_id_fav = $specialist_info['id'] ?? $specialist['id'];
        $other = !empty($specialist_info['other']) ? json_decode($specialist_info['other'], true) : [];
        $spec_email = htmlspecialchars($other['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $fav_all = array_map('intval', $_SESSION['favorite']['all'] ?? []);
        $fav_class = in_array((int)$specialist_id_fav, $fav_all, true) ? 'active' : '';
        $has_email = !empty($other['email']);

        $actions_html = <<<EOF
        <div class="actions-column" style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0; position: relative; z-index: 10;">
            <div class="buttons-row" style="display: flex; justify-content: flex-end; gap: 8px; width: 100%; margin-top: 24px;">
EOF;

        if ($has_email) {
            $actions_html .= <<<EOF
                <button type="button" class="btn-zapis company-write" style="width: 100%; height: 40px; background: #4F77B4; color: #FFFFFF; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 12px; transition: all 0.2s;"
                data-id="{$specialist['id']}" 
                data-email="{$spec_email}"
                >
                    <img src='/assets/img/cabinet/zapis-card.svg' class="icon-default" alt="">
                    <img src='/assets/img/cabinet/zapis-card-blue.svg' class="icon-hover" style="display:none;" alt="">
                    <span>Записаться на занятие</span>
                </button>
EOF;
        }

        $classification_id_safe = $specialist_info['classification_id'] ?? '';
        $actions_html .= <<<EOF
            </div>
        </div>
EOF;

        // 8. Финальный вывод всей структуры карточки
        $data .= <<<EOF
        <div class="item {$cl}" style="{$style}">
            <div class="main-info">
                <button type="button" class="btn-fav company-fav catalog-fav-btn {$fav_class}" data-id="{$specialist_id_fav}" data-cat="{$classification_id_safe}">
                    <input type="hidden" id="favorite_cat" value="{$classification_id_safe}">
                    <input type="hidden" id="favorite_id" value="{$specialist_id_fav}">
                    <img src='/assets/img/cabinet/izbrannoe-heart-card.svg' class="icon-default icon-fav" alt="" loading="lazy">
                    <img src='/assets/img/cabinet/izbrannoe-heart-card-blue.svg' class="icon-hover icon-fav" style="display:none;" alt="" loading="lazy">
                    <img src='/assets/img/cabinet/izbrannoe-heart-card-active-catalog.svg' class="icon-active icon-fav" style="display:none;" alt="" loading="lazy">
                    <img src='/assets/img/cabinet/izbrannoe-heart-card-active-hover.svg' class="icon-active-hover icon-fav" style="display:none;" alt="" loading="lazy">
                </button>
                {$topSpecialistBadge}
                <div class="image">
                    {$image_block_html}
                </div>
                
                <a href="{$studio_url}" target="_blank" rel="noopener noreferrer" class="link"></a>
            </div> 
            
            <div class="title-descr-block">
                <!-- Заголовок + Рейтинг в одной строке -->
                <div class="name-rating-row" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 6px;">
                    <div class="name cut-title-catalog" style="font-weight: 500;">{$displayName}</div>
                    <div class="rating" style="display: flex; align-items: center; gap: 4px; margin: 0; flex-shrink: 0;">
                        <span>
                            <i class="{$star_class}"></i> <span>{$rating_formatted}</span>
                        </span>
                        <span style="color: #6F6F6F; font-weight: 300;">
                            ({$reviews_count})
                        </span>
                    </div>
                </div>
                
                {$info_wrapper_html}
                
                {$specs_html}
                
                {$actions_html}
            </div>
        </div> 
EOF;
    }

    return $data;
}

function block(array $specialists, $class = false, bool $randomSpecialization = false)
{
    $data = '';

    foreach ($specialists as $key => $specialist) {
        if (!$class)
            $cl = '';
        else
            $cl = $class;

        // Получаем список подключенных тарифов для специалиста
        $connected_tariffs = get_connected_tariffs($specialist['id']);

        // Проверяем наличие тарифов с ID = 2 и 3
        $tariff2Connected = false;
        $tariff3Connected = false;
        foreach ($connected_tariffs as $tariff) {
            if ($tariff['tariff_id'] == 2) {
                $tariff2Connected = true;
            }
            if ($tariff['tariff_id'] == 3) {
                $tariff3Connected = true;
            }
        }

        // Устанавливаем стиль фона и видимость rocket в зависимости от тарифов
        /* $style = $tariff2Connected || $tariff3Connected ? 'background-color: #D9EAF6;' : ''; */
        $rocketStyle = $tariff3Connected ? 'display: block;' : 'display: none;';

        if (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/catalog_' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/catalog_' . $specialist['photo'];
            $image_class = '';
        } elseif (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/' . $specialist['photo'];
            $image_class = 'rounded';
        } else {
            $specialist['photo_path'] = !empty($specialist['company_name']) ? '/uploads/avatars/022.webp' : '/uploads/avatars/02.webp';
            $image_class = 'rounded';
        }

        $slides_html = '';
        $has_gallery = false;
        $specialist_info = get_specialist($specialist['id']);
        $studio_slug = !empty($specialist_info['slug']) ? $specialist_info['slug'] : 'id' . $specialist_info['id'];
        $studio_url = "/studio/{$studio_slug}";
        $displayName = $specialist['company_name'] ? $specialist['company_name'] : $specialist['name'] . ' ' . $specialist['lastname'];
        $alt_text = htmlspecialchars($specialist_info['classification'] . ' ' . $displayName, ENT_QUOTES, 'UTF-8');

        // Слайд 1: Аватар
        if (!empty($specialist['photo_path'])) {
            $slides_html .= '<div class="swiper-slide">';
            $slides_html .= '<div class="image-box">';
            $slides_html .= '<img src="' . $specialist['photo_path'] . '" alt="' . $alt_text . '" class="' . $image_class . '">';
            $slides_html .= '</div>';
            $slides_html .= '</div>';
        }

        // Слайды 2+: Галерея
        $gallery_photos = [];
        if (!empty($specialist_info['gallery'])) {
            $gallery_photos = is_array($specialist_info['gallery'])
                ? $specialist_info['gallery']
                : (json_decode($specialist_info['gallery'], true) ?: []);
        }
        $gallery_photos = array_filter($gallery_photos);

        if (!empty($gallery_photos)) {
            $has_gallery = true;
            foreach ($gallery_photos as $photo) {
                $gallery_photo_path = '/uploads/gallery/catalog_' . $photo;
                $slides_html .= '<div class="swiper-slide">';
                $slides_html .= '<div class="image-box">';
                $slides_html .= '<img src="' . $gallery_photo_path . '" alt="' . $alt_text . '" class="' . $image_class . '" loading="lazy">';
                $slides_html .= '</div>';
                $slides_html .= '</div>';
            }
        }

        $data .= <<<EOF
        <a href="{$studio_url}" target="_blank" rel="noopener noreferrer" class="item $cl" style="$style">
            <div class="image">
        EOF;

        if ($has_gallery) {
            $data .= <<<EOF
                <div style="border-radius: 16px;" class="swiper spec-card-inline-swiper">
                    <div class="swiper-wrapper">
                        {$slides_html}
                    </div>
                    <div class="swiper-button-prev gallery-arrow-prev"></div>
                    <div class="swiper-button-next gallery-arrow-next"></div>
                    <div class="swiper-pagination mobile-gallery-pagination"></div>
                </div>
            EOF;
        } else {
            $data .= <<<EOF
            <div class="image-box">
                <img src="{$specialist['photo_path']}" alt="{$alt_text}" class="{$image_class}" loading="lazy">
            </div>
            EOF;
        }

        $data .= <<<EOF
            </div>
            <div class="rocket" style="$rocketStyle"></div>
            <div class="flex">
                <div class="name cut-text-catalog">
        EOF;

        if ($specialist['company_name']) {
            $data .= $specialist['company_name'];
        }

        if (!$specialist['company_name']) {
            $data .= $specialist['name'] . ' ' . $specialist['lastname'];
        }

        $reviews_count = isset($specialist_info['count_rating']) ? (int)$specialist_info['count_rating'] : 0;

        $data .= <<<EOF
                </div>
                        <div style="margin-top: unset; white-space: nowrap;"class="rating"><span>
        EOF;


        $data .= rating($specialist['rating']);


        $data .= <<<EOF
                    </span>
                    <span style="color: #6F6F6F; font-weight: 300;">
                        ({$reviews_count})
                        </span>
                    </div>
                    </div>
                <div class="city">
                    г. {$specialist['city_name']}
        EOF;

        if ($specialist['metro_name']) {
            $data .= ", м. {$specialist['metro_name']}";
        }

        $data .= <<<EOF
            </div>
            </a>
        EOF;
    }

    return $data;
}

// Рейтинг
function rating($rating)
{
    $rating = min(max(floatval($rating), 0), 5); // Ограничиваем диапазон от 0 до 5
    $rating_formatted = number_format($rating, 1, '.', '');

    return "<i class='fa fa-star'></i> " . $rating_formatted;
}

//получение ссылки классификации
function get_classification_link(int $classification): string
{
    if ($classification == 3 || $classification == 8) {
        $link = 'yoga-studii';
    } else if ($classification == 4) {
        $link = 'notariusy';
    } else {
        $link = 'yuristy-i-advokaty';
    }
    return $link;
}

//выводит пост(ы) блога, если переменная is_singe равна true, то выводится публикация полностью (для страницы публикации)
/**
 * @param array $articles
 * @param bool $is_single
 * @param int $blog_id
 * @return string
 */
function block_articles(array $articles, bool $is_single = false, int $blog_id = 0): string
{
    $code = '';

    if ($articles) {
        foreach ($articles as $article) {
            $specialist = get_specialist($article['specialist_id']);

            $studio_slug = !empty($specialist['slug']) ? $specialist['slug'] : 'id' . $specialist['id'];
            $studio_url = "/studio/{$studio_slug}";
            $specialist_rating = '';
            if ($specialist['rating']) {
                $specialist_rating = rating($specialist['rating']);
            }
            if ($specialist['company']) {
                $specialist_name = $specialist['company'];
            } else {
                $specialist_name = $specialist['name'] . ' ' . $specialist['lastname'];
            }

            $date = date("d.m.Y", strtotime($article['date']));
            $article_title = '<div class="link nohover">' . htmlspecialchars_decode($article['title']) . '</div>';
            $article_content = $article['content'];
            $article_content = nl2br($article_content);
            $article_link = '';
            if (!$is_single) {
                $article_title = '<a href="/article/' . $article['id'] . '" class="link">' . $article['title'] . '</a>';
                $article_content = (htmlspecialchars_decode($article['content']));
                $article_link = '<div class="article-bottom-link"><a href="/article/' . $article['id'] . '">ЧИТАТЬ ДАЛЕЕ</a></div>';
            } else if ($blog_id) {
                $article_link = '<a href="/journal/id' . $specialist['id'] . '" class="articles__back">ВСЕ ЗАПИСИ АВТОРА</a><a href="/journal" class="articles__to-main bottom-button">Смотреть все блоги</a>';
            }
            $article_mobile_title = '<div class="mobile-article_profile">
										<span>Автор: </span><a href="' . $studio_url . '" class="link">' . $specialist_name . '</a>
										<span class="profile-type">/' . $specialist['classification'] . '/</span>
									</div>';

            // $themes = '';
            // $themes_arr = get_article_tags($article['id']);
            // if ($themes_arr) {
            //     $themes = '<div class="tags">';
            //     foreach ($themes_arr as $theme) {
            //         $themes .= '<a href="/journal/' . $theme['alias'] . '">#' . $theme['title'] . '</a>';
            //     }
            //     $themes .= '</div>';
            // }
            $themes = '<div class="tags"><a href="/journal/' . $article['service_slug'] . '">' . $article['service_title'] . '</a></div>';

            $code .= <<<EOF
            <div class="article_item">
                <div class="article_profile item">
			    	<div class="image">
                        <div class="image-box">
                            <img src="/uploads/avatars/{$specialist['photo']}" alt="{$alt_text}" loading="lazy">
                        </div>
                        <div class="rating">{$specialist_rating}</div>
                    </div>
			    	<div class="name">{$specialist_name}</div>
			    	<div class="prof">{$specialist['classification']}</div>
                    <div class="city">{$specialist['city']}
EOF;
            if ($specialist['metro'])
                $code .= "<br /> м. {$specialist['metro']}";

            $code .= <<<EOF
			        </div>
			        <div class="cats">
EOF;
            if ($specialist['specialization'])
                $code .= mb_strimwidth($specialist['specialization'], 0, 21, '...', 'UTF-8');;


            if ($specialist['specialization_count'] > 1) {
                $specialization = num_decline(($specialist['specialization_count'] - 1), 'специализация, специализации, специализаций');
                $code .= "<br> и ещё <a href='/studio/{$studio_url}'>$specialization</a>";
            }
            $code .= <<<EOF
                    </div>
                    <a href="{$studio_url}" class="link"></a>
				</div>
                <div class="article_body">
                    <div class="date">{$date}{$themes}</div>
                    {$article_title}
					{$article_mobile_title}
                    <div class="article_text">{$article_content}</div>
                    {$article_link}
                </div>
            </div>
EOF;
        }
    }
    return $code;
}

//выводит пост(ы) блога в ЛИЧНОМ КАБИНЕТЕ, если переменная is_singe равна true, то выводится публикация полностью (для страницы публикации)
/**
 * @param array $articles
 * @param bool $is_single
 * @param int $blog_id
 * @return string
 */
function block_article_prosmotr(array $article): string
{
    $code = '';

    $date = date("d.m.Y", strtotime($article['date']));
    $article_title = '<div class="link nohover">' . htmlspecialchars_decode($article['title']) . '</div>';
    $article_content = nl2br(htmlspecialchars_decode($article['content']));
    $article_mobile_title = '<div class="mobile-article_profile">
							</div>';

    // $themes = '';
    // $themes_arr = get_article_tags($article['id']);
    // if ($themes_arr) {
    //     $themes = '<div class="tags">';
    //     foreach ($themes_arr as $theme) {
    //         $themes .= '<a href="/journal/' . $theme['alias'] . '">#' . $theme['title'] . '</a>';
    //     }
    //     $themes .= '</div>';
    // }
    $themes = '<div class="tags"><a href="/journal/' . $article['service_slug'] . '">' . $article['service_title'] . '</a></div>';
    $code .= <<<EOF
    <div class="article_item prosmotr">
EOF;
    $code .= <<<EOF
        <div class="article_body">
            <div class="date">{$date}{$themes}</div>
            {$article_title}
			{$article_mobile_title}
            <div class="article_text">{$article_content}</div>
        </div>
    </div>
EOF;
    return $code;
}

//выводит блок пагинации
/**
 * @param int $count
 * @param int $limit
 * @param int $page
 * @param string $requestion
 * @param int|array $vars
 * @return string
 */
function block_pagination(int $count, int $limit, int $page, string $requestion, string|array $vars): string
{
    $code = '';
    $total_page = ceil($count / $limit);

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        if (isset($_GET['company']))
            $get = 'company=true&';
        if (isset($_GET['specialist']))
            $get = 'specialist=true&';
        if (isset($_GET['company']) && isset($_GET['specialist']))
            $get = '';

        $code = "<div class='pages'>";

        if ($start > 1)
            $code .= "<a href='/$requestion'>1</a><a>...</a>";

        for ($i = $start; $i <= $end; $i++) {
            $class = ($page == $i) ? 'active' : '';
            $code .= "<a href='/$requestion?{$get}page=$i' class='$class'>$i</a>";
        }

        if ($end < $total_page)
            $code .= "<a>...</a><a href='/$requestion?{$get}page=$total_page'>$total_page</a>";

        $code .= "</div>";

        //скрытые поля для передачи значений по GET + кнопка "Показать еще"
        if ($page != $total_page) {
            $next_page = $page + 1;
            $category = is_string($vars) ? $vars : 0;
            $classification = isset($vars['classification']) ? implode(',', $vars['classification']) : 0;
            $specialist = isset($_GET['specialist']) && !isset($_GET['company']) ? 1 : 0;
            $company = !isset($_GET['specialist']) && isset($_GET['company']) ? 1 : 0;

            $code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-specialist">
					<input type="hidden" name="requestion" value="{$requestion}">
					<input type="hidden" name="category" value="{$category}">
					<input type="hidden" name="classification" value="{$classification}">
					<input type="hidden" name="specialist" value="{$specialist}">
					<input type="hidden" name="company" value="{$company}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
        }
    }

    return $code;
}

//пагинация для поиска

function block_pagination_search(int $count, int $limit, int $page, string $requestion, string $filter_req = ''): string
{
    $code = '';
    $total_page = ceil($count / $limit);

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        $code = "<div class='pages'>";

        if ($start > 1)
            $code .= '<a href="/search?text=' . $requestion . ($filter_req ? '&filter=' . $filter_req : '') . '">1</a><a>...</a>';

        for ($i = $start; $i <= $end; $i++) {
            $class = ($page == $i) ? 'active' : '';
            $code .= '<a href="/search?text=' . $requestion . ($filter_req ? '&filter=' . $filter_req : '') . '&page=' . $i . '" class="' . $class . '">' . $i . '</a>';
        }

        if ($end < $total_page)
            $code .= '<a>...</a><a href="/search?text=' . $requestion . ($filter_req ? '&filter=' . $filter_req : '') . '&page=' . $total_page . '">' . $total_page . '</a>';

        $code .= "</div>";

        //скрытые поля для передачи значений по GET + кнопка "Показать еще"
        if ($page != $total_page) {
            $next_page = $page + 1;

            $code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-search-result">
					<input type="hidden" name="requestion" value="{$requestion}">
					<input type="hidden" name="filter" value="{$filter_req}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
        }
    }

    return $code;
}

//пагинация для избранного
function block_pagination_fav(int $count, int $limit, int $page, string $specialization): string
{
    $code = '';
    $total_page = ceil($count / $limit);

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        /* $code = "<div class='pages'>";

        if ($start > 1)
            $code .= "<a href='/izbrannoe?specialization={$specialization}'>1</a><a>...</a>";

        for ($i = $start; $i <= $end; $i++) {
            $class = ($page == $i) ? 'active' : '';
            $code .= "<a href='/izbrannoe?specialization={$specialization}&page=$i' class='$class'>$i</a>";
        }

        if ($end < $total_page)
            $code .= "<a>...</a><a href='/izbrannoe?specialization={$specialization}&page=$total_page'>$total_page</a>";

        $code .= "</div>"; */

        //скрытые поля для передачи значений по GET + кнопка "Показать еще"
        if ($page != $total_page) {
            $next_page = $page + 1;
            $specialization = isset($_GET['specialization']) ? $_GET['specialization'] : 'lawyers';

            $code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-favorite">
					<input type="hidden" name="specialization" value="{$specialization}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
        }
    }

    return $code;
}


//пагинация для блогов
function block_pagination_blog(int $count, int $limit, int $page, int $blog_id): string
{
    $code = '';
    $total_page = ceil($count / $limit);

    $blog_href = '';
    if ($blog_id !== -1) {
        $specialist_id = get_blog_by_id($blog_id)['specialist_id'];
        $blog_href = '/id' . $specialist_id;
    }

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        $code = "<div class='pages'>";

        if ($start > 1)
            $code .= "<a href='/journal{$blog_href}'>1</a><a>...</a>";

        for ($i = $start; $i <= $end; $i++) {
            $class = ($page == $i) ? 'active' : '';
            $code .= "<a href='/journal{$blog_href}?page=$i' class='$class'>$i</a>";
        }

        if ($end < $total_page)
            $code .= "<a>...</a><a href='/journal{$blog_href}?page=$total_page'>$total_page</a>";

        $code .= "</div>";

        //скрытые поля для передачи значений по GET + кнопка "Показать еще"
        if ($page != $total_page) {
            $next_page = $page + 1;

            $code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-blog">
					<input type="hidden" name="blog_id" value="{$blog_id}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
        }
    }

    return $code;
}

/**
 * @param array $articles
 * @param bool $has_text_no_articles
 * @return string
 */
function block_articles_cabinet(array $articles, bool $has_text_no_articles): string
{
    $code = '';
    if ($articles) {
        foreach ($articles as $article) {
            $date = date("d.m.Y", strtotime($article['date']));
            $moderation = '';
            if ($article['moderation'] == 1) {
                $moderation = '<div class="moderation">на модерации</div>';
            } elseif ($article['moderation'] == 2) {
                $moderation = '<div class="moderation">требует доработки</div>';
            }
            // $themes = '';
            // $themes_arr = get_article_tags($article['id']);
            // if ($themes_arr) {
            //     $themes = '<div class="tags">';
            //     foreach ($themes_arr as $theme) {
            //         $themes .= '<a href="/journal/' . $theme['alias'] . '">#' . $theme['title'] . '</a>';
            //     }
            //     $themes .= '</div>';
            // }
            $themes = '<div class="tags"><a href="/journal/' . $article['service_slug'] . '">' . $article['service_title'] . '</a></div>';
            $content = htmlspecialchars_decode($article["content"], ENT_QUOTES);

            // Заменяем переносы и блочные теги на пробел
            $content = preg_replace('/<\s*br\s*\/?>/i', ' ', $content);
            $content = preg_replace('/<\/(p|div|li|h[1-6])>/i', ' ', $content);

            // Убираем все оставшиеся теги
            $content = strip_tags($content);

            // Убираем множественные пробелы
            $content = preg_replace('/\s{2,}/u', ' ', trim($content));

            // Передаём в функцию обрезки
            $short_content = cut_text($content, 300);
            $decodedTitle = strip_tags(htmlspecialchars_decode($article['title']));
            if ($article['moderation'] == 0) {
                $link = "<a href='/journal/id{$article['blog_id']}/{$article['alias']}'class='link'>{$decodedTitle}</a>";
                $open_option = "<a class='menu-point open' href='/journal/id{$article['blog_id']}/{$article['alias']}'>
                                <div class='menu-point-img'><svg width='8' height='15' viewBox='0 0 9 15' fill='none' xmlns='http://www.w3.org/2000/svg'>
<path d='M0.210271 1.16312L6.3579 7.01909C6.49273 7.14686 6.56836 7.31934 6.56836 7.49907C6.56836 7.6788 6.49273 7.85128 6.3579 7.97905L0.211604 13.8363C0.0768444 13.9648 0.00133379 14.1379 0.00133379 14.3181C0.00133379 14.4984 0.0768444 14.6714 0.211604 14.8C0.277436 14.8633 0.356119 14.9136 0.443016 14.9479C0.529914 14.9823 0.623267 15 0.717574 15C0.811881 15 0.905234 14.9823 0.992131 14.9479C1.07903 14.9136 1.15771 14.8633 1.22354 14.8L7.36984 8.94525C7.77372 8.55962 8 8.04077 8 7.50032C8 6.95988 7.77372 6.44102 7.36984 6.05539L1.22354 0.200668C1.15769 0.137169 1.07893 0.0866928 0.991905 0.0522261C0.904884 0.0177593 0.811375 9.53674e-07 0.716907 9.53674e-07C0.622439 9.53674e-07 0.52893 0.0177593 0.44191 0.0522261C0.354889 0.0866928 0.276124 0.137169 0.210271 0.200668C0.0755112 0.329231 0 0.502271 0 0.68252C0 0.86277 0.0755112 1.03581 0.210271 1.16437' fill='currentColor'/>
</svg></div>
                                Открыть
                            </a>";
            } else {
                $link = "<div style='margin-bottom: 8px; font-weight: 500; font-size: 16px; color: #222222;'>{$decodedTitle}</div>";
                $open_option = '';
            }
            $code .= <<<EOF
                <div class="article__item" data-article-id="{$article['id']}">
                    <div class="article_body">
                        <div class="date">{$date}{$themes}</div>
                        {$link}
                        <div class="article_text cut-text-lk">{$short_content}</div>
                    </div>
                    {$moderation}
                    <div class="article-menu-btn">
                        <div class="article-menu">
                            {$open_option}
                            <a class="menu-point edit" href="/cabinet/sozdat-zapis/{$article['id']}">
                                <div class="menu-point-img"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 13H15V14H1V13ZM12.7 4.5C13.1 4.1 13.1 3.5 12.7 3.1L10.9 1.3C10.5 0.9 9.9 0.9 9.5 1.3L2 8.8V12H5.2L12.7 4.5ZM10.2 2L12 3.8L10.5 5.3L8.7 3.5L10.2 2ZM3 11V9.2L8 4.2L9.8 6L4.8 11H3Z" fill="currentColor"/></svg></div>
                                Редактировать
                            </a>
                            <a class="menu-point delete" data-id="{$article['id']}">
                                <div class="menu-point-img"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_1_5432)"><path d="M9.52 1.33301C9.79984 1.33308 10.0726 1.4212 10.2995 1.58488C10.5265 1.74856 10.6962 1.97951 10.7847 2.24501L11.1467 3.33301H13.3333C13.5101 3.33301 13.6797 3.40325 13.8047 3.52827C13.9298 3.65329 14 3.82286 14 3.99967C14 4.17649 13.9298 4.34605 13.8047 4.47108C13.6797 4.5961 13.5101 4.66634 13.3333 4.66634L13.3313 4.71367L12.7533 12.809C12.7173 13.3134 12.4915 13.7854 12.1214 14.13C11.7513 14.4747 11.2644 14.6663 10.7587 14.6663H5.24133C4.73564 14.6663 4.24874 14.4747 3.87864 14.13C3.50855 13.7854 3.28274 13.3134 3.24667 12.809L2.66867 4.71301C2.66746 4.69748 2.66679 4.68192 2.66667 4.66634C2.48986 4.66634 2.32029 4.5961 2.19526 4.47108C2.07024 4.34605 2 4.17649 2 3.99967C2 3.82286 2.07024 3.65329 2.19526 3.52827C2.32029 3.40325 2.48986 3.33301 2.66667 3.33301H4.85333L5.21533 2.24501C5.3038 1.9794 5.47362 1.74838 5.70073 1.58468C5.92784 1.42099 6.20071 1.33294 6.48067 1.33301H9.51933H9.52ZM11.998 4.66634H4.002L4.57667 12.7137C4.58863 12.8818 4.66385 13.0392 4.78717 13.1541C4.9105 13.269 5.07277 13.3329 5.24133 13.333H10.7587C10.9272 13.3329 11.0895 13.269 11.2128 13.1541C11.3362 13.0392 11.4114 12.8818 11.4233 12.7137L11.998 4.66634ZM6.66667 6.66634C6.82996 6.66636 6.98756 6.72631 7.10958 6.83482C7.23161 6.94332 7.30956 7.09284 7.32867 7.25501L7.33333 7.33301V10.6663C7.33315 10.8363 7.26808 10.9997 7.15143 11.1233C7.03479 11.2468 6.87536 11.3212 6.70574 11.3311C6.53611 11.3411 6.36908 11.2859 6.23878 11.1768C6.10848 11.0678 6.02474 10.9131 6.00467 10.7443L6 10.6663V7.33301C6 7.1562 6.07024 6.98663 6.19526 6.8616C6.32029 6.73658 6.48986 6.66634 6.66667 6.66634ZM9.33333 6.66634C9.51014 6.66634 9.67971 6.73658 9.80474 6.8616C9.92976 6.98663 10 7.1562 10 7.33301V10.6663C10 10.8432 9.92976 11.0127 9.80474 11.1377C9.67971 11.2628 9.51014 11.333 9.33333 11.333C9.15652 11.333 8.98695 11.2628 8.86193 11.1377C8.73691 11.0127 8.66667 10.8432 8.66667 10.6663V7.33301C8.66667 7.1562 8.73691 6.98663 8.86193 6.8616C8.98695 6.73658 9.15652 6.66634 9.33333 6.66634ZM9.52 2.66634H6.48L6.258 3.33301H9.742L9.51933 2.66634H9.52Z" fill="currentColor"/></g></svg></div>
                                Удалить
                            </a>
                        </div>
                    </div>
                </div>
            EOF;
        }
    } else if ($has_text_no_articles) {
        $code .= <<<EOF
                <div class="no_articles">
                    В вашем блоге еще нет записей.
                </div>
            EOF;
    }

    return $code;
}

function truncateText($text)
{
    $maxLength = 16;
    if (mb_strlen($text) > $maxLength) {
        $truncated = mb_substr($text, 0, $maxLength - 2);
        $lastChar = mb_substr($text, $maxLength - 1, 1);
        return $truncated . '...' . $lastChar;
    }
    return $text;
}
