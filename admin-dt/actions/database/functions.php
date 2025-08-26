<?php

use Actions\Database\DataBase;

if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT'])) {
    define('DOCUMENT_ROOT', '/var/www/html'); // вручную задаём путь для cron
} else {
    define('DOCUMENT_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
}

require_once DOCUMENT_ROOT . '/actions/database/DataBase.php';

$data_base = new DataBase;

/**
 * @return mixed
 */
function count_specialists(): mixed
{
    global $data_base;
    return $data_base->count("SELECT COUNT(*) FROM dt_specialist");
}

/**
 * @param string $query
 * @param array $param
 * @return mixed
 */
function count_specialists_filter(string $query, array $param): mixed
{
    global $data_base;
    return $data_base->count("SELECT COUNT(*) FROM dt_specialist ds 
                                    LEFT JOIN dt_specialist_contacts dsc on ds.id = dsc.specialist_id
                                    LEFT JOIN dt_classification dc on ds.classification_id = dc.id 
                                    LEFT JOIN dt_city dc2 on ds.city_id = dc2.id
                                    LEFT JOIN dt_company dcom ON ds.company_id = dcom.id
                                    WHERE 1" . $query, $param);
}

/**
 * @param int $specialist_id
 * @return mixed
 */
/**
 * @param int $specialist_id
 * @return mixed
 */
function get_specialist(int $specialist_id): mixed
{
    global $data_base;

    $specialist = $data_base->selectRow("SELECT 
                                            s.*, 
                                            sc.*, 
                                            sc.metro as metro_id,
                                            sc.address, 
                                            sc.street, 
                                            sc.house, 
                                            sc.how_to_get, 
                                            sc.coordinates, 
                                            m.name as metro,
                                            c.title as classification,
                                            c.type as type,
                                            city.name as city,
                                            sd.description as page_description, 
                                            sd.foundation, 
                                            sd.schedule as schedule, 
                                            sd.document as document,
                                            sd.university as university,
                                            com.title as company,
                                            com.classification as company_classification,
                                            com.id as company_id,
                                            (SELECT COUNT(*) + 1 FROM dt_specialist WHERE rating > s.rating) as rang
                                        FROM dt_specialist s
                                        LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
                                        LEFT JOIN dt_classification c ON s.classification_id = c.id
                                        LEFT JOIN dt_city city ON s.city_id = city.id
                                        LEFT JOIN dt_specialist_description sd ON s.id = sd.specialist_id
                                        LEFT JOIN dt_company com ON s.company_id = com.id
                                        LEFT JOIN dt_metro m ON sc.metro = m.id
                                        WHERE s.id = ?", [$specialist_id]);

    // Получение сотрудников компании, если есть связанная компания
    if (!empty($specialist['company_id'])) {
        $specialist['employees'] = $data_base->selectAll(
            "SELECT 
                                                            name, 
                                                            lastname, 
                                                            post, 
                                                            photo,
                                                            id 
                                                        FROM dt_company_employee 
                                                        WHERE company_id = ?",
            [$specialist['company_id']]
        );
    } else {
        $specialist['employees'] = [];
    }

    if ($specialist) {
        // Декодирование поля `other` и добавление с префиксом
        if (!empty($specialist['other'])) {
            $otherData = json_decode($specialist['other'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($otherData as $key => $value) {
                    $specialist["contacts_$key"] = $value;
                }
            }
        }

        // Декодирование поля `schedule`
        if (!empty($specialist['schedule'])) {
            $scheduleData = json_decode($specialist['schedule'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $specialist['schedule_parsed'] = $scheduleData;
            }
        }

        // Декодирование поля `document`
        if (!empty($specialist['document'])) {
            $documentData = json_decode($specialist['document'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $specialist['documents'] = [];
                foreach ($documentData as $doc) {
                    $specialist['documents'][] = [
                        'name' => $doc['name'] ?? '',
                        'file' => $doc['file'] ?? '',
                    ];
                }
            }
        }

        // Получение данных о сервисах специалиста из dt_specialist_service
        $services = $data_base->selectAll("SELECT 
                                               name, 
                                               price, 
                                               ot 
                                           FROM dt_specialist_service 
                                           WHERE specialist_id = ?", [$specialist_id]);

        if ($services) {
            $specialist['services'] = array_map(function ($service) {
                return [
                    'name' => $service['name'],
                    'price' => intval($service['price']),
                    'is_active' => (bool) $service['ot'], // Преобразование `ot` в булевое значение
                ];
            }, $services);
        } else {
            $specialist['services'] = []; // Если сервисов нет, устанавливаем пустой массив
        }
    }

    return $specialist;
}

// Возвращает все специализации специалиста
function get_specialist_specialization(int $specialist_id)
{
    global $data_base;
    $specializations = $data_base->selectAll("SELECT * FROM dt_specialist_specialization ss
                                    LEFT JOIN dt_service s ON ss.service_id = s.id
                                    WHERE specialist_id = ? AND original_id = 0
                                    ORDER BY s.id", [$specialist_id]);

    $result = [];

    foreach ($specializations as $specialization) {
        if ($specialization['p_id']) {
            $parrent = get_service($specialization['p_id']);

            $specialization['p_slug'] = $parrent['slug'];
        }
        $result[$specialization['id']] = $specialization;
    }

    return $result;
}

// Возвращает категории по направлениям
function get_services_by_classification($classification, int|bool $p_id = false)
{
    global $data_base;

    if (is_int($classification)) {
        $classification = match ($classification) {
            1, 2, 5, 6, 7 => 1,
            3, 8 => 3,
            4 => 0
        };
        $where = '= ?';
        $array = [$classification];
    }

    if (is_array($classification)) {
        $where = 'IN (' . implode(',', $classification) . ')';
    }

    if (!is_bool($p_id)) {
        $where .= 'AND p_id = ?';

        $array ? array_push($array, $p_id) : ($array = [$p_id]);
    }

    $where .= ' AND original_id = 0';

    $categories = $data_base->selectAll("SELECT * FROM dt_service WHERE classification $where ORDER BY title", $array);

    $result = [];

    $last_cat = "";
    foreach ($categories as $category) {
        if (mb_stripos($category['title'], 'прочие') !== false) {
            $last_cat = $category;
            continue;
        }
        $result[$category['id']] = $category;
    }
    if ($last_cat && $last_cat != "")
        $result[$last_cat['id']] = $last_cat;

    return $result;
}

function renderSpecializations(array $specializations, array $activeSpecializations, $specialist_classification): string
{
    $selectedCount = count($activeSpecializations);
    $hasSpecializations = $selectedCount > 0;

    // Открываем контейнер выбора направлений
    $html = <<<HTML
<div class="card">
    <div class="card-header bg-primary text-white">
        Выбор направлений ({$selectedCount})
    </div>
    <div class="card-body">
        <div class="list-group overflow-auto" style="max-height: 300px;">
HTML;

    // Добавляем специализации в список
    $html .= list_specialization($specializations, $activeSpecializations, $specialist_classification);

    // Закрываем список
    $html .= <<<HTML
        </div>
    </div>
HTML;

    // Добавляем информацию о количестве выбранных направлений
    $html .= $hasSpecializations
        ? "<div class='card-footer bg-light text-success'>Выбрано {$selectedCount} направлений</div>"
        : "<div class='card-footer bg-light text-danger'>Направления не выбраны</div>";

    $html .= "</div>"; // Закрываем контейнер

    return $html;
}


function list_specialization(array $specializations, array $activeSpecializations, ?int $specialistClassification): string
{
    $html = '';

    // Группы классификаций
    $group_one = [1, 2, 5, 6, 7];
    $group_two = [3, 8];
    $group_three = [4];

    foreach ($specializations as $specialization) {
        if ($specialization['p_id'] != 0) {
            continue; // Пропускаем вложенные
        }

        // Если классификация не указана — показываем всё
        if ($specialistClassification !== null) {
            $spec_class = $specialization['classification'];

            // Проверка соответствия классификации
            if (
                !(
                    (in_array($specialistClassification, $group_one) && in_array($spec_class, $group_one)) ||
                    (in_array($specialistClassification, $group_two) && in_array($spec_class, $group_two)) ||
                    (in_array($specialistClassification, $group_three) && in_array($spec_class, $group_three))
                )
            ) {
                continue; // Если не соответствует — пропускаем
            }
        }

        $checked = array_key_exists($specialization['id'], $activeSpecializations) ? 'checked' : '';
        $children = get_services_by_parrent_id($specialization['id']);
        $hasChildrenClass = $children ? 'has-children' : '';

        // Основная специализация
        $html .= <<<HTML
<div class="list-group-item">
    <input type="checkbox" name="specializations[{$specialization['id']}]" class="form-check-input me-2" id="specialization{$specialization['id']}" {$checked}>
    <label for="specialization{$specialization['id']}" class="form-check-label {$hasChildrenClass}">{$specialization['title']}</label>
HTML;

        // Вложенные специализации
        if ($children) {
            $html .= "<ul class='list-group ms-3'>";
            foreach ($children as $child) {
                $checkedChild = array_key_exists($child['id'], $activeSpecializations) ? 'checked' : '';
                $html .= <<<HTML
<li class="list-group-item">
    <input type="checkbox" name="specializations[{$child['id']}]" class="form-check-input me-2" id="specialization{$child['id']}" {$checkedChild}>
    <label for="specialization{$child['id']}" class="form-check-label">{$child['title']}</label>
</li>
HTML;
            }
            $html .= "</ul>";
        }

        $html .= "</div>"; // Закрываем основной блок
    }

    return $html;
}

// Возвращает категории по id родителя
function get_services_by_parrent_id(int $p_id = 0)
{
    global $data_base;
    $categories = $data_base->selectAll("SELECT * FROM dt_service WHERE p_id = $p_id AND original_id = 0 ORDER BY title");
    $p_category = $data_base->selectRow("SELECT * FROM dt_service WHERE id = $p_id AND original_id = 0");

    $result = [];

    foreach ($categories as $category) {
        $category['p_slug'] = $p_category['slug'];
        $result[$category['id']] = $category;
    }

    return $result;
}
function get_services_by_parrent_id_with_prefix(int $p_id = 0)
{
    global $data_base;
    $categories = $data_base->selectAll("SELECT * FROM dt_service WHERE p_id = $p_id AND original_id = 0 ORDER BY title");
    $p_category = $data_base->selectRow("SELECT * FROM dt_service WHERE id = $p_id AND original_id = 0");

    $result = [];

    foreach ($categories as $category) {
        // Добавляем префикс для идентификатора, чтобы соответствовать основным элементам
        $category['id'] = 's_' . $category['id'];
        $category['p_slug'] = $p_category['slug'];
        $result[$category['id']] = $category;
    }

    return $result;
}

function get_services_with_prefix(): array
{
    global $data_base;
    $services = $data_base->selectAll("
        SELECT *, 
            CASE 
                WHEN title LIKE '%прочие%' THEN 1 
                ELSE 0 
            END AS is_misc
        FROM dt_service
        WHERE original_id = 0
        ORDER BY is_misc, title
    ");

    foreach ($services as &$service) {
        $service['id'] = 's_' . $service['id']; // Префикс для ID
        $service['type'] = 'service'; // Маркер, что это из таблицы dt_service
    }

    return $services;
}

/**
 * Получает все теги из таблицы dt_tags с необходимыми полями для метаданых
 *
 * @return array
 */
function get_all_tags(): array
{
    global $data_base;
    // Выбираем основные поля и переименовываем meta_* в формат _journal
    $tags = $data_base->selectAll("SELECT 
        id,
        title,
        meta_title,
        meta_description,
        meta_keywords
    FROM dt_tags
    ORDER BY title
    ");

    return $tags;
}

function renderTagsWithMetaJournal(array $tags): string
{
    // Общий стиль для каждой строки, с дополнительной колонкой для кнопки
    $rowStyle = 'display: grid; grid-template-columns: 1fr 160px 160px 140px auto; gap: .5rem; align-items: center;';

    $html = <<<HTML
<div class="card">
    <div class="card-header bg-primary text-white">
        Управление метаданными тегов
    </div>
    <div class="card-body">
        <div class="list-group">
HTML;

    // Заголовок таблицы
    $html .= "<div class=\"list-group-item bg-light\" style=\"{$rowStyle}\">";
    $html .= '<div><strong>Хештег</strong></div>';
    $html .= '<div><strong>Title</strong></div>';
    $html .= '<div><strong>Description</strong></div>';
    $html .= '<div><strong>Ключевые слова</strong></div>';
    $html .= '<div><strong>Действия</strong></div>';
    $html .= "</div>";

    // Строки с данными
    foreach ($tags as $tag) {
        $id = (int) $tag['id'];
        $html .= "<div class=\"list-group-item\" style=\"{$rowStyle}\">";

        $html .= "<input type=\"text\" 
                         class=\"form-control form-control-sm tag-title\" 
                         placeholder=\"Название тега\" 
                         data-id=\"{$id}\" 
                         value=\"" . htmlspecialchars($tag['title'], ENT_QUOTES) . "\">";

        $html .= "<input type=\"text\" 
                         class=\"form-control form-control-sm meta-title\" 
                         placeholder=\"Title\" 
                         data-id=\"{$id}\" 
                         value=\"" . htmlspecialchars($tag['meta_title'], ENT_QUOTES) . "\">";

        $html .= "<input type=\"text\" 
                         class=\"form-control form-control-sm meta-description\" 
                         placeholder=\"Description\" 
                         data-id=\"{$id}\" 
                         value=\"" . htmlspecialchars($tag['meta_description'], ENT_QUOTES) . "\">";

        $html .= "<input type=\"text\" 
                         class=\"form-control form-control-sm meta-keywords\" 
                         placeholder=\"\" 
                         data-id=\"{$id}\" 
                         value=\"" . htmlspecialchars($tag['meta_keywords'], ENT_QUOTES) . "\">";

        // Кнопка удаления тега
        $html .= "<button type=\"button\" 
                         class=\"btn btn-sm btn-danger delete-tag\" 
                         data-id=\"{$id}\">Удалить</button>";

        $html .= "</div>";
    }

    $html .= <<<HTML
        </div>
    </div>
</div>
HTML;

    return $html;
}

/**
 * Обновляет название тега и метаданные в таблице dt_tags
 *
 * @param int         $id
 * @param string|null $title
 * @param string|null $metaTitle
 * @param string|null $metaDescription
 * @param string|null $metaKeywords
 * @return bool
 */
function update_tags_menu(int $id, ?string $title, ?string $metaTitle, ?string $metaDescription, ?string $metaKeywords): bool
{
    global $data_base;

    $alias = $title ? generateAlias($title) : null;

    return $data_base->update("
        UPDATE dt_tags
           SET 
               title            = ?, 
               alias            = ?, 
               meta_title       = ?, 
               meta_description = ?, 
               meta_keywords    = ?
         WHERE id = ?
    ", [
        $title,
        $alias,
        $metaTitle,
        $metaDescription,
        $metaKeywords,
        $id,
    ]);
}

function get_promo_blocks_with_prefix(): array
{
    global $data_base;
    $promoBlocks = $data_base->selectAll("SELECT * FROM dt_promo_block");

    foreach ($promoBlocks as &$block) {
        $block['id'] = 'p_' . $block['id']; // Префикс для ID
        $block['type'] = 'promo'; // Маркер, что это из таблицы dt_promo_block
    }

    return $promoBlocks;
}

function renderSpecializationsWithPrice(array $specializations, string $priceField): string
{
    $html = <<<HTML
<div class="card">
    <div class="card-header bg-primary text-white">
        Управление ценами специализаций
    </div>
    <div class="card-body">
        <div class="list-group">
HTML;

    $html .= listSpecializationsWithPrice($specializations, $priceField);

    $html .= <<<HTML
        </div>
    </div>
</div>
HTML;

    return $html;
}

function listSpecializationsWithPrice(array $specializations, string $priceField): string
{
    $html = '';
    $previousType = null; // для разделителей по типу

    usort($specializations, function ($a, $b) {
        $aIsOther = mb_stripos($a['title'], 'Прочие') !== false;
        $bIsOther = mb_stripos($b['title'], 'Прочие') !== false;

        if ($aIsOther === $bIsOther) {
            return 0;
        }

        return $aIsOther ? 1 : -1;
    });

    foreach ($specializations as $specialization) {
        // Пропускаем дочерние элементы, чтобы они не дублировались в основном списке
        if (isset($specialization['p_id']) && $specialization['p_id'] != 0) {
            continue;
        }

        if ($previousType !== null && $specialization['type'] !== $previousType) {
            $html .= '<div class="my-3"><hr></div>';
        }
        $previousType = $specialization['type'];

        if (substr($specialization['id'], 0, 2) === 's_') {
            $parentId = (int) substr($specialization['id'], 2);
            $children = get_services_by_parrent_id_with_prefix($parentId);
            $hasChildren = !empty($children);

            $childIds = $hasChildren ? implode(',', array_map(function ($child) {
                return (int) substr($child['id'], 2);
            }, $children)) : '';

            $toggleButton = $hasChildren
                ? '<button class="btn btn-sm btn-link toggle-children" data-target="#children-' . $specialization['id'] . '">+</button>'
                : '<span class="invisible">+</span>';

            $html .= <<<HTML
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span>{$specialization['title']}</span>
                    </div>
                    <div class="d-flex align-items-center" style="min-width: 160px;">
                        <input 
                            type="number" 
                            class="form-control form-control-sm me-2 parent-price" 
                            style="width: 100px;" 
                            placeholder="Цена" 
                            data-id="{$specialization['id']}" 
                            data-children="{$childIds}" 
                            value="{$specialization[$priceField]}">
                        {$toggleButton}
                    </div>
                </div>
            HTML;

            if ($hasChildren) {
                $html .= "<div id='children-{$specialization['id']}' class='ms-3 mt-2 d-none'>";
                foreach ($children as $child) {
                    $childId = $child['id']; // дочерние уже с префиксом
                    $html .= <<<HTML
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span>{$child['title']}</span>
                    </div>
                    <div class="d-flex align-items-center" style="min-width: 160px;">
                        <input 
                            type="number" 
                            class="form-control form-control-sm me-2 child-price" 
                            style="width: 100px;" 
                            placeholder="Цена" 
                            data-id="{$childId}" 
                            value="{$child[$priceField]}">
                        <span class="invisible">+</span>
                    </div>
                </div>
            </div>
            HTML;
                }
                $html .= "</div>";
            }
            $html .= "</div>";
        }

        if (substr($specialization['id'], 0, 2) === 'p_') {
            $html .= <<<HTML
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span>{$specialization['title']}</span>
                    </div>
                    <div class="d-flex align-items-center" style="min-width: 160px;">
                        <input 
                            type="number" 
                            class="form-control form-control-sm me-2 parent-price" 
                            style="width: 100px;" 
                            placeholder="Цена" 
                            data-id="{$specialization['id']}" 
                            value="{$specialization[$priceField]}">
                        <span class="invisible">+</span>
                    </div>
                </div>
            </div>
            HTML;
        }
    }
    return $html;
}

function renderSpecializationsWithMetaJournal(array $specializations): string
{
    $html = <<<HTML
<div class="card">
    <div class="card-header bg-primary text-white">
        Управление метаданными
    </div>
    <div class="card-body">
        <div class="list-group">
HTML;

    // Заголовки столбцов с сеткой
    $html .= <<<HTML
            <div class="list-group-item bg-light" style="display: grid; grid-template-columns: 1fr 160px 160px 140px; gap: .5rem; align-items: center;">
                <div><strong>Заголовок</strong></div>
                <div><strong>Title</strong></div>
                <div><strong>Description</strong></div>
                <div><strong>Ключевые слова</strong></div>
            </div>
HTML;

    $html .= listSpecializationsWithMetaJournal($specializations);

    $html .= <<<HTML
        </div>
    </div>
</div>
HTML;

    return $html;
}

function listSpecializationsWithMetaJournal(array $specializations): string
{
    usort($specializations, function ($a, $b) {
        $aIsOther = stripos($a['title'], 'Прочие') !== false;
        $bIsOther = stripos($b['title'], 'Прочие') !== false;
        if ($aIsOther === $bIsOther)
            return 0;
        return $aIsOther ? 1 : -1;
    });

    $html = '';
    $previousType = null;

    foreach ($specializations as $spec) {
        if (!empty($spec['p_id']))
            continue;
        if ($previousType !== null && $spec['type'] !== $previousType) {
            $html .= '<div class="my-3"><hr></div>';
        }
        $previousType = $spec['type'];

        // Настройка общей сетки для строк
        $rowStyle = 'display: grid; grid-template-columns: 1fr 160px 160px 140px; gap: .5rem; align-items: center;';

        if (strpos($spec['id'], 's_') === 0) {
            $parentId = (int) substr($spec['id'], 2);
            $children = get_services_by_parrent_id_with_prefix($parentId);
            $childIds = !empty($children)
                ? implode(',', array_map(fn($c) => (int) substr($c['id'], 2), $children))
                : '';

            $html .= "<div class=\"list-group-item\" style=\"{$rowStyle}\">";
            $html .= "<div>{$spec['title']}</div>";
            $html .= "<input type=\"text\" class=\"form-control form-control-sm meta-title\" placeholder=\"Заголовок\" style=\"width:160px;\" data-id=\"{$spec['id']}\" data-children=\"{$childIds}\" value=\"{$spec['meta_title_journal']}\">";
            $html .= "<input type=\"text\" class=\"form-control form-control-sm meta-description\" placeholder=\"Описание\" style=\"width:160px;\" data-id=\"{$spec['id']}\" value=\"{$spec['meta_description_journal']}\">";
            $html .= "<input type=\"text\" class=\"form-control form-control-sm meta-keywords\" placeholder=\"\" style=\"width:140px;\" data-id=\"{$spec['id']}\" value=\"{$spec['meta_keywords_journal']}\">";
            $html .= "</div>";

            if (!empty($children)) {
                $html .= "<div id='children-{$spec['id']}' class='ms-3 mt-2 d-none'>";
                foreach ($children as $child) {
                    $html .= "<div class=\"list-group-item\" style=\"{$rowStyle}\">";
                    $html .= "<div>{$child['title']}</div>";
                    $html .= "<input type=\"text\" class=\"form-control form-control-sm meta-title\" placeholder=\"Meta Title\" data-id=\"{$child['id']}\" value=\"{$child['meta_title_journal']}\">";
                    $html .= "<input type=\"text\" class=\"form-control form-control-sm meta-description\" placeholder=\"Meta Description\" data-id=\"{$child['id']}\" value=\"{$child['meta_description_journal']}\">";
                    $html .= "<div></div>"; // пустой блок для колонки Keywords
                    $html .= "</div>";
                }
                $html .= "</div>";
            }
        } elseif (strpos($spec['id'], 'p_') === 0) {
            $html .= "<div class=\"list-group-item\" style=\"{$rowStyle}\">";
            $html .= "<div>{$spec['title']}</div>";
            $html .= "<input type=\"text\" class=\"form-control form-control-sm meta-title\" placeholder=\"Meta Title\" data-id=\"{$spec['id']}\" value=\"{$spec['meta_title_journal']}\">";
            $html .= "<input type=\"text\" class=\"form-control form-control-sm meta-description\" placeholder=\"Meta Description\" data-id=\"{$spec['id']}\" value=\"{$spec['meta_description_journal']}\">";
            $html .= "<div></div>";
            $html .= "</div>";
        }
    }
    return $html;
}


/**
 * @param string $query
 * @param string $columnName
 * @param string $columnOrder
 * @param int $row
 * @param int $rowperpage
 * @param array $param
 * @return array
 */
function get_specialists_filter(
    string $query,
    string $columnName,
    string $columnOrder,
    int $row,
    int $rowperpage,
    array $param
): array {
    global $data_base;

    if ($columnName === 'name') {
        $orderExpr = "COALESCE(dcom.title, CONCAT(ds.lastname, ' ', ds.name))";
        $orderClause = "$orderExpr $columnOrder, ds.id ASC";
    } else {
        $orderClause = "$columnName $columnOrder, ds.id ASC";
    }

    $offset = (int) $row;
    $limit = (int) $rowperpage;

    $sql = "
    SELECT 
        ds.id, 
        ds.name, 
        ds.lastname, 
        ds.email, 
        ds.active, 
        ds.photo, 
        IFNULL(ROUND((
            SELECT AVG(rating)
            FROM dt_specialist_review
            WHERE specialist_id = ds.id
        ), 1), 0) AS rating,
        ROUND(
            LEAST(
                2.0 * IFNULL((
                    SELECT AVG(rating)
                    FROM dt_specialist_review
                    WHERE specialist_id = ds.id
                ), 0) +
                0.1 * (
                    SELECT COUNT(*)
                    FROM dt_post
                    WHERE author_id = ds.id
                    AND moderation = 0
                ),
                99
            ), 1
        ) AS computed_rating,
        dsc.phone,
        dc.title   AS classification,
        dc2.name   AS city,
        dcom.title AS company_name
    FROM dt_specialist ds
    LEFT JOIN dt_specialist_contacts dsc
        ON ds.id = dsc.specialist_id
    LEFT JOIN dt_classification dc
        ON ds.classification_id = dc.id
    LEFT JOIN dt_city dc2
        ON ds.city_id = dc2.id
    LEFT JOIN dt_company dcom
        ON ds.company_id = dcom.id
    WHERE 1 $query
    ORDER BY $orderClause
    LIMIT $offset, $limit
";


    // 3) Параметры только для части WHERE
    return $data_base->selectAll($sql, $param);
}

/**
 * @param int $specialist_id
 * @return bool
 */
function delete_specialist(int $specialist_id): bool
{
    global $data_base;
    return $data_base->delete("DELETE FROM dt_specialist WHERE id = ?", [$specialist_id]);
}

function set_specialist(array $data): string
{
    global $data_base;

    /* Добавление компании */
    if (!empty($data['company_name']) && !empty($data['company_classification_id'])) {
        $company_id = set_company(
            $data['company_name'],
            (int) $data['company_classification_id']
        );
    }

    if (empty($data['password'])) {
        throw new Exception('Пароль не был передан или не был захеширован');
    }

    $result = $data_base->add(
        "INSERT INTO dt_specialist (
            name, lastname, slug, email, password, hash, photo,
            classification_id, city_id,
            meta_title, meta_description, meta_keywords,
            company_id, active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $data['name'],
            $data['lastname'],
            $data['slug'],
            $data['email'],
            $data['password'],
            $data['hash'],
            $data['photo'],
            (int) $data['classification_id'],
            (int) $data['city_id'],
            $data['meta_title'],
            $data['meta_description'],
            $data['meta_keywords'],
            $company_id,
            (int) $data['active']
        ]
    );

    if ($result) {
        // Используем возвращенный ID специалиста
        set_specialist_description($data, $result);
        set_specialist_contacts($data, $result);
        set_specialist_blog($result);
    }

    return $result;
}

function update_specialist($data): bool
{
    global $data_base;

    if (empty($data['photo'])) {
        $currentSpecialist = $data_base->selectRow("SELECT photo FROM dt_specialist WHERE id = ?", [(int) $data['id']]);
        $data['photo'] = $currentSpecialist ? $currentSpecialist['photo'] : null;
    }

    if (empty($data['classification_id'])) {
        $currentSpecialist = $data_base->selectRow("SELECT classification_id FROM dt_specialist WHERE id = ?", [(int) $data['id']]);
        $data['classification_id'] = $currentSpecialist ? $currentSpecialist['classification_id'] : null;
    }

    $query = "UPDATE dt_specialist SET 
        name = ?, 
        lastname = ?, 
        slug = ?, 
        email = ?, 
        photo = ?, 
        classification_id = ?,
        password = ?,
        city_id = ?,
        meta_title = ?,
        meta_description = ?,
        meta_keywords = ?,
        active = ? 
        WHERE id = ?";

    $params = [
        $data['name'],
        $data['lastname'],
        $data['slug'],
        $data['email'],
        $data['photo'],
        (int) $data['classification_id'],
        $data['password'],
        (int) $data['city_id'],
        $data['meta_title'],
        $data['meta_description'],
        $data['meta_keywords'],
        (int) $data['active'],
        (int) $data['id']
    ];

    $result = $data_base->update($query, $params);

    $exists = $data_base->count("SELECT COUNT(*) FROM dt_specialist_contacts WHERE specialist_id = ?", [$data['id']]);

    if ($exists > 0) {
        update_specialist_contacts($data, $data['id']);
    } else {
        set_specialist_contacts($data, $data['id']);
    }

    if ($result) {
        // Проверяем наличие записи
        $checkQuery = "SELECT COUNT(*) FROM dt_specialist_description WHERE specialist_id = ?";
        $count = $data_base->count($checkQuery, [$data['id']]);

        $foundationValue = isset($data['foundation']) && $data['foundation'] !== '' ? (int) $data['foundation'] : null;

        if ($count > 0) {
            $updateDescriptionQuery = "UPDATE dt_specialist_description
            SET schedule = ?, document = ?, foundation = ?, description = ?, university = ?
            WHERE specialist_id = ?";
            $result = $data_base->update($updateDescriptionQuery, [
                $data['schedule'],
                $data['documents'],
                $foundationValue,
                $data['page-discription'],
                $data['universityJson'],
                $data['id']
            ]);
        } else {
            // Создаём новую запись
            $insertDescriptionQuery = "INSERT INTO dt_specialist_description (schedule, document, foundation, description, specialist_id)
                                        VALUES (?, ?, ?, ?, ?)";
            $result = $data_base->add($insertDescriptionQuery, [
                $data['schedule'],
                $data['documents'],
                $foundationValue,
                $data['page-discription'],
                $data['id']
            ]);
        }
    }

    if ($result && !empty($data['services'])) {
        $result = update_specialist_services($data['id'], $data['services']);
    }

    return $result;
}

/**
 * Обновляет специализации специалиста.
 * Удаляет старые записи и добавляет новые.
 *
 * @param int   $specialist_id     ID специалиста
 * @param array $specializations   Массив специализаций, где ключ — ID специализации, значение — булево
 *
 * @return bool
 */
function update_specialist_specializations($specialist_id, array $specializations): bool
{
    global $data_base;

    $deleteQuery = "DELETE FROM dt_specialist_specialization WHERE specialist_id = ?";
    $deleteResult = $data_base->delete($deleteQuery, [$specialist_id]);

    if ($deleteResult === false) {
        return false;
    }

    $insertQuery = "INSERT INTO dt_specialist_specialization (specialist_id, service_id) VALUES (?, ?)";
    $successCount = 0;

    foreach ($specializations as $service_id => $is_active) {
        if ($is_active) {
            $insertResult = $data_base->add($insertQuery, [$specialist_id, $service_id]);

            if ($insertResult) {
                $successCount++;
            }
        }
    }

    return $successCount > 0;
}

function unset_specialist_specializations($specialist_id)
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_specialist_specialization` WHERE specialist_id = ?", [$specialist_id]);
}



function update_specialist_services($specialist_id, $services): bool
{
    global $data_base;

    // Сначала удаляем все старые услуги для данного специалиста
    $deleteQuery = "DELETE FROM dt_specialist_service WHERE specialist_id = ?";
    $data_base->delete($deleteQuery, [(int) $specialist_id]);

    // Обработка новых или обновленных услуг
    foreach ($services as $service) {
        if (isset($service['id']) && $service['id']) {
            // Если услуга существует, обновляем её
            $query = "
                UPDATE dt_specialist_service 
                SET name = ?, price = ?, ot = ? 
                WHERE specialist_id = ? AND id = ?
            ";
            $params = [
                $service['name'],
                $service['price'],
                (int) $service['is_active'],
                (int) $specialist_id,
                (int) $service['id']
            ];
            $data_base->update($query, $params);
        } elseif (isset($service['name']) && isset($service['price'])) {
            // Если новая услуга, добавляем её
            $query = "
                INSERT INTO dt_specialist_service (specialist_id, name, price, ot) 
                VALUES (?, ?, ?, ?)
            ";
            $params = [
                (int) $specialist_id,
                $service['name'],
                $service['price'],
                (int) $service['is_active']
            ];
            $data_base->add($query, $params);
        }
    }

    return true;
}


/**
 * @param array $data
 * @param int $specialist_id
 * @return string
 */
function set_specialist_description(array $data, int $specialist_id): string
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_description (specialist_id, description, university, foundation, document) 
                        VALUE (?, ?, ?, ?, ?)", [
        $specialist_id,
        $data['page-discription'] ?: null,
        $data['university'] ?: '',
        $data['foundation'] ?: null,
        $data['documents'] ?: null
    ]);
}

// Добавляет запись в таблицу блогов
function set_specialist_blog(int $specialist_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_blog (specialist_id) VALUES (?)", [$specialist_id]);
}

/**
 * @param array $data
 * @param int $specialist_id
 * @return string
 */
function set_specialist_contacts(array $data, int $specialist_id): string
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_contacts (specialist_id, address, metro, street, house, how_to_get, coordinates, phone, other) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        $specialist_id,
        $data['address'] ?: null,
        $data['metro'] ?: null,
        $data['street'] ?: '',
        $data['house'] ?: null,
        $data['how_to_get'] ?: '',
        $data['coordinates'],
        $data['phone'],
        $data['other'] ?: ''
    ]);
}
function update_specialist_contacts(array $data, int $specialist_id): bool
{
    global $data_base;

    return $data_base->update("
        UPDATE dt_specialist_contacts
        SET 
            address = ?, 
            metro = ?, 
            street = ?, 
            house = ?, 
            coordinates = ?, 
            how_to_get = ?, 
            phone = ?, 
            other = ?
        WHERE specialist_id = ?
    ", [
        $data['address'] ?: null,
        $data['metro'] ?: null,
        $data['street'] ?: '',
        $data['house'] ?: null,
        $data['coordinates'] ?: null,
        $data['how_to_get'] ?: '',
        $data['phone'],
        $data['other'] ?: '',
        $specialist_id
    ]);
}


/**
 * @param string $phone
 * @param int $specialist_id
 * @return bool
 * @throws Exception
 */
function update_specialist_phone(string $phone, int $specialist_id): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist_contacts SET phone = ? WHERE specialist_id = ?", [
        $phone,
        $specialist_id
    ]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_password(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET password = ?, hash = ? WHERE id = ?", [
        $data['password'],
        $data['hash'],
        (int) $data['id']
    ]);
}

/**
 * @return array
 */
function get_classifications(): array
{
    global $data_base;
    $classifications = $data_base->selectAll("SELECT * FROM dt_classification");

    $result = [];

    foreach ($classifications as $classification) {
        $result[$classification['id']] = $classification;
    }

    return $result;
}

/**
 * Получение отфильтрованных классификаций (ID: 1, 3, 4)
 * @return array
 */
function get_filtered_classifications(): array
{
    global $data_base;
    // Фильтрация на уровне SQL
    $classifications = $data_base->selectAll("SELECT * FROM dt_classification WHERE id IN (1, 3, 4)");

    $result = [];
    foreach ($classifications as $classification) {
        $result[$classification['id']] = $classification;
    }

    return $result;
}

/**
 * @param string $order
 * @return array
 */
function get_cities(string $order = 'id ASC'): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_city SORT ORDER BY $order");
}

/**
 * @param int $id
 * @return array
 */
function get_reviews($id): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_specialist_review SORT WHERE specialist_id = ? ORDER BY date DESC", [
        $id,
    ]);
}

/**
 * 
 * @param string $company_name Название компании
 * @param int $classification_id ID классификации компании
 * @return int ID добавленной компании
 */
function set_company(string $company_name, int $classification_id): int
{
    global $data_base;
    // Вставляем новую компанию с названием и классификацией
    return $data_base->add("INSERT INTO dt_company (title, classification) VALUE (?, ?)", [$company_name, $classification_id]);
}

/**
 * 
 * @param string $company_name Название компании
 * @param int $classification_id ID классификации компании
 * @param int $company_id ID компании, которую нужно обновить
 * @return bool Результат выполнения обновления
 * @throws Exception
 */
function update_company(string $company_name, int $classification_id, int $company_id): bool
{
    global $data_base;
    // Обновляем информацию о компании по ее ID
    return $data_base->update("UPDATE dt_company SET title = ?, classification = ? WHERE id = ?", [
        $company_name,
        $classification_id,
        $company_id
    ]);
}

function update_employees(array $employees, int $company_id): void
{
    global $data_base;

    foreach ($employees as $employeeId => $employee) {
        // Если ключ не является числом, считаем сотрудника новым
        if (!is_numeric($employeeId)) {
            $data_base->add("INSERT INTO dt_company_employee (name, lastname, post, photo, company_id) VALUES (?, ?, ?, ?, ?)", [
                $employee['name'],
                $employee['lastname'],
                $employee['post'],
                $employee['photo'] ?? '',
                $company_id
            ]);
            continue;
        }

        // Если сотрудник существует, обновляем данные
        $existingEmployee = $data_base->selectRow("SELECT id FROM dt_company_employee WHERE id = ? AND company_id = ?", [
            $employeeId,
            $company_id
        ]);

        if ($existingEmployee) {
            $data_base->update("UPDATE dt_company_employee SET name = ?, lastname = ?, post = ?, photo = ? WHERE id = ? AND company_id = ?", [
                $employee['name'],
                $employee['lastname'],
                $employee['post'],
                $employee['photo'] ?? '',
                $employeeId,
                $company_id
            ]);
        } else {
            $data_base->add("INSERT INTO dt_company_employee (name, lastname, post, photo, company_id) VALUES (?, ?, ?, ?, ?)", [
                $employee['name'],
                $employee['lastname'],
                $employee['post'],
                $employee['photo'] ?? '',
                $company_id
            ]);
        }
    }
}

/**
 * @param string $email
 * @return int
 */
function check_email(string $email): int
{
    global $data_base;
    return $data_base->exists("SELECT EXISTS(SELECT 1 FROM dt_specialist WHERE email = ?)", [$email]);
}

/**
 * @return array
 */
function get_blocks(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_blocks");
}

/**
 * Возвращает все категории
 * @return array
 */
function get_menu_items(): array
{
    global $data_base;

    return $data_base->selectAll("
        SELECT 
            s.id AS id, 
            s.title AS title, 
            s.p_id, 
            s.slug,
            c.id AS classification_id, 
            s.img AS img, 
            s.icon AS icon,
            CASE 
                WHEN s.p_id = 0 THEN '-'
                ELSE (SELECT title FROM dt_service WHERE id = s.p_id)
            END AS parent_title, 
            CASE 
                WHEN s.title LIKE '%прочие%' THEN 999999
                WHEN s.title = 'Миграционные вопросы' THEN 30
                ELSE ROW_NUMBER() OVER (ORDER BY s.title)
            END AS sort_order
        FROM dt_service s
        LEFT JOIN dt_classification c ON s.classification = c.id
        WHERE s.original_id = 0
        ORDER BY sort_order, s.title
    ");
}

/**
 * @param int $id
 * @return mixed
 */
function get_menu_item(int $id): array
{
    global $data_base;
    $item = $data_base->selectRow("SELECT * FROM dt_service WHERE id = ?", [$id]);
    return $item ?: [];
}

/**
 * @param int $id
 * @return mixed
 */
function get_menu_parent_item(int $id): array
{
    global $data_base;
    $item = $data_base->selectRow("SELECT * FROM dt_service_parent WHERE id = ?", [$id]);
    return $item ?: [];
}

/**
 * Возвращает альтернативные сервисы (дубликаты) для оригинала с заданным id
 * @param int $original_id
 * @return array
 */
function get_alternative_page(int $page_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_service_parent WHERE original_id = ?", [$page_id]);
}

/**
 * Возвращает альтернативные сервисы (дубликаты) для оригинала с заданным id
 * @param int $original_id
 * @return array
 */
function get_alternative_services(int $original_id): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_service WHERE original_id = ?", [$original_id]);
}

function get_all_service_parents()
{
    global $data_base;
    $services = $data_base->selectAll("SELECT slug, title, meta_title, meta_description, meta_keywords FROM dt_service_parent");

    $result = [];
    foreach ($services as $service) {
        $result[$service['slug']] = [
            'title'       => $service['meta_title'],       // оставляем как есть
            'page_title'  => $service['title'],            // добавляем новое поле с реальным title
            'description' => $service['meta_description'],
            'keywords'    => $service['meta_keywords']
        ];
    }

    return $result;
}
function get_service_parent(int $page_id)
{
    global $data_base;
    $service = $data_base->selectRow("SELECT * FROM dt_service_parent WHERE id = ?", [$page_id]);
    return $service;
}

function update_service_parent($data): bool
{
    global $data_base;

    // Выполняем обновление записи по slug
    $result = $data_base->update(
        "UPDATE dt_service_parent 
         SET title = ?, 
             description = ?, 
             icon = ?, 
             meta_title = ?, 
             meta_description = ?, 
             meta_keywords = ? 
         WHERE slug = ?",
        [
            $data['title'],
            $data['description'],
            $data['icon'],
            $data['meta_title'] ?? '',
            $data['meta_description'] ?? '',
            $data['meta_keywords'] ?? '',
            $data['slug']
        ]
    );

    return $result;
}

/**
 * @return array
 */
function get_all_tariffs(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_tariff");
}

/**
 * @return array
 */
function get_all_posts(): array
{
    global $data_base;
    return $data_base->selectAll("
        SELECT 
            p.*, 
            COALESCE(
                c.title,  -- Если есть компания, берём её название
                s.name    -- Если компании нет, берём имя специалиста
            ) AS author_name
        FROM dt_post p
        LEFT JOIN dt_specialist s ON p.author_id = s.id
        LEFT JOIN dt_company c ON s.company_id = c.id
    ");
}

/**
 * @param int $id
 * @return mixed
 */
function get_post(int $post_id): mixed
{
    global $data_base;
    return $data_base->selectRow("
        SELECT 
            dt_post.*, 
            dt_service.title AS theme_title, 
            dt_specialist.email AS specialist_email,
            GROUP_CONCAT(dt_tags.title SEPARATOR ', ') AS tags_title
        FROM dt_post
        LEFT JOIN dt_service ON dt_post.theme_id = dt_service.id
        LEFT JOIN dt_blog ON dt_post.blog_id = dt_blog.id
        LEFT JOIN dt_specialist ON dt_blog.specialist_id = dt_specialist.id
        LEFT JOIN dt_post_tags ON dt_post.id = dt_post_tags.article_id
        LEFT JOIN dt_tags ON dt_post_tags.tag_id = dt_tags.id
        WHERE dt_post.id = ?
        GROUP BY dt_post.id
    ", [$post_id]);
}

function update_service_meta(int $id, string $metaTitle = null, string $metaDescription = null, string $metaKeywords = null): bool
{
    global $data_base;
    if ($id <= 0) {
        return false;
    }
    $query = "UPDATE dt_service SET meta_title_journal = ?, meta_description_journal = ?, meta_keywords_journal = ? WHERE id = ?";
    return $data_base->update($query, [$metaTitle, $metaDescription, $metaKeywords, $id]);
}

/**
 * @return array
 */
function get_blog_filter_themes()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_service WHERE p_id = 0 ORDER BY title");
}

/**
 * @param int $post_id
 * @return bool
 */
function delete_post(int $post_id): bool
{
    global $data_base;
    return $data_base->delete("DELETE FROM dt_post WHERE id = ?", [$post_id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_post($data): bool
{
    global $data_base;

    $alias = $data['alias'];

    // Проверяем, существует ли такой алиас и принадлежит ли он другому посту
    $existing = $data_base->selectRow(
        "SELECT id FROM dt_post WHERE alias = ? AND id != ? LIMIT 1",
        [$alias, $data['id']]
    );

    if ($existing) {
        $alias .= '-' . $data['id'];
    }

    $result = $data_base->update(
        "UPDATE dt_post 
         SET title = ?, 
             alias = ?, 
             theme_id = ?, 
             date = ?,
             site_from_link = ?, 
             site_from_name = ?, 
             content = ?, 
             moderation = ?,
             comments = ?,
             meta_title = ?,
             meta_description = ?,
             meta_keywords = ?
         WHERE id = ?",
        [
            $data['title'],
            $alias,
            $data['theme_id'],
            $data['timestamp'],
            $data['site_from_link'],
            $data['site_from_name'],
            $data['content'],
            $data['moderation'],
            $data['comment'],
            $data['meta-title'],
            $data['meta-description'],
            $data['meta-keywords'],
            $data['id']
        ]
    );

    return $result;
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_journal($data): bool
{
    global $data_base;

    $alias = $data['alias'];

    // Проверяем, существует ли такой алиас и принадлежит ли он другому посту
    $existing = $data_base->selectRow(
        "SELECT id FROM dt_post WHERE alias = ? AND id != ? LIMIT 1",
        [$alias, $data['id']]
    );

    if ($existing) {
        $alias .= '-' . $data['id'];
    }

    $result = $data_base->update(
        "UPDATE dt_post 
         SET title = ?, 
             alias = ?, 
             photo = ?, 
             theme_id = ?, 
             post_type = ?,
             date = ?,
             site_from_link = ?, 
             site_from_name = ?, 
             content = ?, 
             moderation = 0,
             meta_title = ?,
             meta_description = ?,
             meta_keywords = ?
         WHERE id = ?",
        [
            $data['title'],
            $alias,
            $data['photo'],
            $data['theme_id'],
            $data['post_type'],
            $data['timestamp'],
            $data['site_from_link'],
            $data['site_from_name'],
            $data['content'],
            $data['meta-title'],
            $data['meta-description'],
            $data['meta-keywords'],
            $data['id']
        ]
    );

    return $result;
}


/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function add_journal($data): string
{
    global $data_base;

    // Генерируем alias из заголовка
    $alias = $data['alias'];

    // Проверяем, существует ли уже такой alias
    $existing = $data_base->selectRow("SELECT id FROM dt_post WHERE alias = ? LIMIT 1", [$alias]);

    if ($existing) {
        // Пока alias не уникален — добавляем числовой суффикс
        $i = 1;
        $new_alias = $alias . '-' . $i;
        while ($data_base->selectRow("SELECT id FROM dt_post WHERE alias = ? LIMIT 1", [$new_alias])) {
            $i++;
            $new_alias = $alias . '-' . $i;
        }
        $alias = $new_alias;
    }

    return $data_base->add(
        "INSERT INTO dt_post (title, alias, photo, theme_id, post_type, site_from_link, site_from_name, content, moderation)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)",
        [
            $data['title'],
            $alias,
            $data['photo'],
            $data['theme_id'],
            $data['post_type'],
            $data['site_from_link'],
            $data['site_from_name'],
            $data['content']
        ]
    );
}

/**
 * @return array
 */
function get_all_faq_pro_photo()
{

    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_faq_pro_photo");
}

/**
 * @param int $id
 * @return mixed
 */
function get_faq_pro_photo(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_faq_pro_photo where id = ?", [$id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_faq_pro_photo(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_faq_pro_photo SET title = ?, description = ? WHERE id = ?", [
        $data['title'],
        $data['content'],
        (int) $data['id']
    ]);
}

function add_faq_pro_photo(array $data): bool
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_faq_pro_photo (title, description) VALUES (?, ?)", [
        $data['title'],
        $data['content']
    ]);
}

function delete_faq_pro_photo(int $id): bool
{
    global $data_base;
    return $data_base->delete("DELETE FROM dt_faq_pro_photo WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_all_faq_promo()
{

    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_faq_promo");
}

/**
 * @param int $id
 * @return mixed
 */
function get_faq_promo(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_faq_promo where id = ?", [$id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_faq_promo(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_faq_promo SET title = ?, description = ? WHERE id = ?", [
        $data['title'],
        $data['content'],
        (int) $data['id']
    ]);
}

function delete_faq_promo(int $id): bool
{
    global $data_base;
    return $data_base->delete("DELETE FROM dt_faq_promo WHERE id = ?", [$id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function add_faq_promo(array $data): bool
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_faq_promo (title, description) VALUES (?, ?)", [
        $data['title'],
        $data['content']
    ]);
}

/**
 * @return array
 */
function get_all_faq_pages(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_faq_pages");
}

/**
 * @param int $id
 * @return mixed
 */
function get_faq_page(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_faq_pages where id = ?", [$id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_faq_page(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_faq_pages SET name = ?, short_descr = ?, alias = ?, content = ?, meta_title = ?, meta_description = ? WHERE id = ?", [
        $data['name'],
        $data['short-descr'],
        $data['alias'],
        $data['content'],
        $data['meta_title'],
        $data['meta_description'],
        (int) $data['id']
    ]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function add_faq_page(array $data): bool
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_faq_pages (name, short_descr, alias, content) VALUES (?, ?, ?, ?)", [
        $data['name'],
        $data['short-descr'],
        $data['alias'],
        $data['content']
    ]);
}

function delete_faq_page(int $id): bool
{
    global $data_base;
    return $data_base->delete("DELETE FROM dt_faq_pages WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_all_documents_o_proekte(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_o_proekte");
}

/**
 * @return array
 */
function get_page($id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_pages WHERE id=?", [$id]);
}

/**
 * @param int $id
 * @return mixed
 */
function get_document_o_proekte(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_o_proekte where id = ?", [$id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_document_o_proekte(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_o_proekte SET title = ?, content = ? WHERE id = ?", [
        $data['title'],
        $data['content'],
        (int) $data['id']
    ]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function add_document_o_proekte(array $data): bool
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_o_proekte (title, content) VALUES (?, ?)", [
        $data['title'],
        $data['content']
    ]);
}


function delete_document_o_proekte(int $id): bool
{
    global $data_base;
    return $data_base->delete("DELETE FROM dt_o_proekte WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_all_social(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_social_network");
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_social(array $data): bool
{
    global $data_base;
    return $data_base->update(
        "UPDATE dt_social_network SET link = ? WHERE id = ?",
        [$data['link'], $data['id']]
    );
}

/**
 * @return array
 */
function get_journal_banners(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_banners_journal ORDER BY id");
}

/**
 * @param int $id
 * @return mixed
 */
function get_journal_banner(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_banners_journal WHERE id = ?", [$id]);
}

function get_journal_banner_price(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_journal WHERE id = ?", [$id]);
}

/**
 * Получить список Journal
 *
 * @param int|null $limit Ограничение на количество записей (null для всех записей)
 * @return array
 */
function get_connection_banner_list(int $limit = null): array
{
    global $data_base;

    $limitQuery = $limit ? "LIMIT " . intval($limit) : "";

    $query = "
    (
        SELECT 
            jl.*, 
            'dt_journal_list' AS table_name,
            'journals' AS tab,
            s.name AS specialist_name, 
            s.lastname AS specialist_lastname, 
            s.email AS specialist_email,
            sc.phone AS specialist_phone,
            s.company_id AS company_id,
            c.title AS company_name,
            pp.name AS name
        FROM 
            dt_journal_list jl
        LEFT JOIN dt_specialist s ON jl.specialist_id = s.id
        LEFT JOIN dt_journal pp ON jl.journal_id = pp.id
        LEFT JOIN dt_specialist_contacts sc ON jl.specialist_id = sc.specialist_id
        LEFT JOIN dt_company c ON s.company_id = c.id
        WHERE 
            jl.moderation IN (0, 1, 2)
        " . ($limit ? "ORDER BY jl.id DESC $limitQuery" : "") . "
    )
    UNION ALL
    (
        SELECT 
            bl.*, 
            'dt_banners_list' AS table_name,
            'banners' AS tab,
            s.name AS specialist_name, 
            s.lastname AS specialist_lastname, 
            s.email AS specialist_email,
            sc.phone AS specialist_phone,
            s.company_id AS company_id,
            c.title AS company_name,
            pp.name AS name
        FROM 
            dt_banners_list bl
        LEFT JOIN dt_specialist s ON bl.specialist_id = s.id
        LEFT JOIN dt_banners pp ON bl.banner_id = pp.id
        LEFT JOIN dt_specialist_contacts sc ON bl.specialist_id = sc.specialist_id
        LEFT JOIN dt_company c ON s.company_id = c.id
        WHERE 
            bl.moderation IN (0, 1, 2)
        " . ($limit ? "ORDER BY bl.id DESC $limitQuery" : "") . "
    )
    ORDER BY id DESC
    " . ($limit && false ? "LIMIT " . ($limit * 2) : "") . "
    ";

    return $data_base->selectAll($query);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_journal_banner(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_banners_journal SET title = ?, service_type = ?, link = ?, button_text = ?, button_link = ?, description = ?, image = ? WHERE id = ?", [
        $data['title'],
        $data['service_type'],
        $data['alias'],
        $data['button_text'],
        $data['button_link'],
        $data['content'],
        $data['photo'],
        $data['id']
    ]);
}

/**
 * @return array
 */
function get_banners(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_banners ORDER BY id");
}

/**
 * @param int $id
 * @return mixed
 */
function get_block(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_blocks WHERE id = ?", [$id]);
}

/**
 * @param int $id
 * @return mixed
 */
function get_banner(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_banners WHERE id = ?", [$id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_block(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_blocks SET title = ?, description = ?, content = ? WHERE id = ?", [
        $data['title'],
        $data['description'],
        $data['content'],
        (int) $data['id']
    ]);
}

/**
 * @return array
 */
function get_services(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_service");
}

/**
 * @return array
 */
function get_actual_services(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_service_actual");
}

/**
 * @param int $id
 * @return mixed
 */
function get_service(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_service WHERE id = ?", [$id]);
}

function get_service_actual(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_service_actual WHERE id = ?", [$id]);
}

/**
 * @param int $id
 * @return mixed
 */
function get_actual_service(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_service_actual WHERE id = ?", [$id]);
}


/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function update_service($data): bool
{
    global $data_base;
    $result = $data_base->update("UPDATE dt_service SET title = ?, slug = ?, img = ? WHERE id = ?", [
        $data['title'],
        $data['slug'],
        $data['photo'],
        $data['id']
    ]);

    return $result;
}

/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function update_actual_service($data): bool
{
    global $data_base;
    $result = $data_base->update("UPDATE dt_service_actual SET title = ?, slug = ?, img = ? WHERE id = ?", [
        $data['title'],
        $data['slug'],
        $data['photo'],
        $data['id']
    ]);

    return $result;
}

/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function update_page($data): bool
{
    global $data_base;
    return $data_base->update(
        "UPDATE dt_pages SET title = ?, description = ?, keywords = ? WHERE id = ?",
        [
            $data['title'],
            $data['description'],
            $data['keywords'],
            $data['id']
        ]
    );
}

/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function update_menu($data): array
{
    global $data_base;
    $base_slug = $data['slug'];
    $slug = $base_slug;
    $suffix = 0;

    // Проверяем уникальность slug, исключая текущую запись
    while (true) {
        $result = $data_base->selectRow("SELECT COUNT(*) as count FROM dt_service WHERE slug = ? AND id != ?", [$slug, $data['id']]);
        $count = $result['count'] ?? 0;

        if ($count == 0) {
            break;
        }

        $suffix++;
        $slug = $base_slug . $suffix;
    }

    // Выполняем обновление с уникальным slug
    $result = $data_base->update(
        "UPDATE dt_service SET title = ?, description = ?, slug = ?, img = ?, icon = ?, classification = ?, p_id = ?, meta_title = ?, meta_description = ?, meta_keywords = ? WHERE id = ?",
        [
            $data['title'],
            $data['content'],
            $slug,
            $data['photo'],
            $data['icon'],
            $data['classification_id'],
            $data['p_id'],
            $data['meta_title'],
            $data['meta_description'],
            $data['meta_keywords'],
            $data['id']
        ]
    );

    // Проверяем результат обновления
    if ($result === false) {
        return [
            'success' => false,
            'error' => 'Не удалось обновить услугу'
        ];
    }

    return [
        'success' => true,
        'new_slug' => $slug
    ];
}

/**
 * Обновляет страницу в таблице dt_pages
 *
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_alternative_page($data): array
{
    global $data_base;
    $base_slug = $data['slug'];
    $slug = $base_slug;
    $suffix = 0;

    // Проверяем, существует ли slug, исключая текущую запись
    while (true) {
        $result = $data_base->selectRow("SELECT COUNT(*) as count FROM dt_service_parent WHERE slug = ? AND id != ?", [$slug, $data['id']]);
        $count = $result['count'] ?? 0;

        if ($count == 0) {
            break;
        }

        $suffix++;
        $slug = $base_slug . $suffix;
    }

    // Выполняем обновление с уникальным slug
    $result = $data_base->update(
        "UPDATE dt_service_parent SET title = ?, description = ?, slug = ?, original_id = ?, meta_title = ?, meta_description = ?, meta_keywords = ? WHERE id = ?",
        [
            $data['title'],
            $data['content'],
            $slug,
            $data['original_id'],
            $data['meta_title'],
            $data['meta_description'],
            $data['meta_keywords'],
            $data['id']
        ]
    );

    if ($result === false) {
        return [
            'success' => false,
            'error' => 'Не удалось обновить альтернативную страницу'
        ];
    }

    return [
        'success' => true,
        'new_slug' => $slug
    ];
}

/**
 * Добавляет новую страницу в таблицу dt_pages
 *
 * @param array $data
 * @return bool
 * @throws Exception
 */
function add_alternative_page($data): array
{
    global $data_base;
    $original_id = isset($data['original_id']) && $data['original_id'] !== '' ? $data['original_id'] : 0;

    $base_slug = $data['slug'];
    $slug = $base_slug;
    $suffix = 0;

    // Проверяем, существует ли slug, и генерируем новый при необходимости
    while (true) {
        $result = $data_base->selectRow("SELECT COUNT(*) as count FROM dt_service_parent WHERE slug = ?", [$slug]);
        $count = $result['count'] ?? 0;

        if ($count == 0) {
            break;
        }

        $suffix++;
        $slug = $base_slug . $suffix;
    }

    // Выполняем вставку с уникальным slug
    $result = $data_base->add(
        "INSERT INTO dt_service_parent (title, description, slug, original_id, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [
            $data['title'],
            $data['content'],
            $slug,
            $original_id,
            $data['meta_title'],
            $data['meta_description'],
            $data['meta_keywords']
        ]
    );

    if ($result === false) {
        return [
            'success' => false,
            'error' => 'Не удалось добавить альтернативную страницу'
        ];
    }

    return [
        'success' => true,
        'new_slug' => $slug
    ];
}

/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function add_service($data): array
{
    global $data_base;
    $original_id = isset($data['original_id']) && $data['original_id'] !== '' ? $data['original_id'] : 0;

    $base_slug = $data['slug'];
    $slug = $base_slug;
    $suffix = 0;

    // Проверяем, существует ли slug, и генерируем новый при необходимости
    while (true) {
        $result = $data_base->selectRow("SELECT COUNT(*) as count FROM dt_service WHERE slug = ?", [$slug]);
        $count = $result['count'] ?? 0;

        if ($count == 0) {
            break;
        }

        $suffix++;
        $slug = $base_slug . $suffix;
    }

    // Выполняем вставку с уникальным slug
    $result = $data_base->add(
        "INSERT INTO dt_service (title, description, slug, img, classification, p_id, original_id, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $data['title'],
            $data['content'],
            $slug,
            $data['photo'],
            $data['classification_id'],
            $data['p_id'],
            $original_id,
            $data['meta_title'],
            $data['meta_description'],
            $data['meta_keywords']
        ]
    );

    if ($result === false) {
        return [
            'success' => false,
            'error' => 'Не удалось добавить услугу'
        ];
    }

    return [
        'success' => true,
        'new_slug' => $slug
    ];
}

/**
 * @param int $id
 * @return bool
 * @throws Exception
 */
function delete_service(int $id): bool
{
    global $data_base;
    $result = $data_base->delete("DELETE FROM dt_service WHERE id = ?", [$id]);

    return $result;
}

function delete_alternative_page($id): bool
{
    global $data_base;
    return $data_base->delete("DELETE FROM dt_service_parent WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_pro_photo(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_pro_photo");
}

/**
 * Получить список Pro Photo
 *
 * @param int|null $limit Ограничение на количество записей (null для всех записей)
 * @return array
 */
function get_pro_photo_list(int $limit = null): array
{
    global $data_base;
    $query = "
    SELECT 
        ppl.*,
        'dt_pro_photo_list' AS table_name,
        'pro-photo' AS tab, 
        s.name AS specialist_name, 
        s.lastname AS specialist_lastname, 
        s.email AS specialist_email,
        s.company_id AS company_id,
        c.title AS company_name,
        pp.title,
        pp.description
    FROM 
        dt_pro_photo_list ppl
    LEFT JOIN 
        dt_specialist s ON ppl.specialist_id = s.id
    LEFT JOIN 
        dt_company c ON s.company_id = c.id
    LEFT JOIN 
        dt_pro_photo pp ON ppl.pro_photo_id = pp.id
    ORDER BY ppl.connected_at DESC
";

    if ($limit) {
        $query .= " LIMIT " . intval($limit);
    }

    return $data_base->selectAll($query);
}

function render_pro_photo_request(array $req, array $tariffEmpPrice = []): string
{
    ob_start(); ?>
    <div class="card my-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                Архивный запрос:
                <?php
                if ($req['pro_photo_id'] == 4):
                    echo '<p>Съемка сотрудников</p>';
                else:
                    echo htmlspecialchars_decode($req['title']);
                endif;
                ?>
            </span>

            <button type="button" class="btn btn-dark bg-dark toggle-pro-photo-details" data-request-id="<?= $req['id'] ?>">
                Подробнее
            </button>
        </div>
        <div class="card-body" id="pro-photo-request-details-<?= $req['id'] ?>" style="display: none;">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <strong>Дата запроса:</strong> <?= date('d.m.Y', strtotime($req['connected_at'])) ?>
                </div>
                <div class="mb-2 text-start fw-bold text-success">
                    Статус: Проведена
                </div>
            </div>

            <?php if ($req['pro_photo_id'] != 4): ?>
                <p><strong>Стоимость:</strong> <?= htmlspecialchars_decode($req['price']) ?></p>
            <?php endif; ?>

            <?php if (!empty($req['emp_quantity'])): ?>
                <p><strong>Съемка сотрудников (кол-во человек):</strong>
                    <?= strip_tags(html_entity_decode($req['emp_quantity'])) ?>
                </p>
                <p><strong>Стоимость за сотрудников:</strong>
                    <?= strip_tags(html_entity_decode($tariffEmpPrice['price'] ?? '')) ?>
                </p>
            <?php endif; ?>

            <p><strong>Контактный номер:</strong> <?= htmlspecialchars_decode($req['specialist_phone']) ?></p>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * @param int $id
 * @return mixed
 */
function get_pro_photo_by_id(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_pro_photo WHERE id = ?", [$id]);
}

/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function update_pro_photo($data): bool
{
    global $data_base;
    $result = $data_base->update("UPDATE dt_pro_photo SET title = ?, price = ?, price_old = ?, time = ?, number = ?, description = ? WHERE id = ?", [
        $data['title'],
        $data['price'],
        $data['price_old'],
        $data['time'],
        $data['number'],
        $data['description'],
        $data['id']
    ]);

    return $result;
}

/**
 * @param int $id
 * @return array
 */
function get_slides(int $id): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_banners_sliders WHERE banner_id = ?", [$id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_slide(array $slideData): bool
{
    global $data_base;

    return $data_base->update("UPDATE dt_banners_sliders 
        SET 
            title = ?, 
            subtitle = ?, 
            description = ?, 
            specialist_name = ?, 
            classification = ?, 
            link = ?, 
            button_text = ?, 
            button_link = ? 
        WHERE id = ? AND banner_id = ?", [
        $slideData['title'],
        $slideData['subtitle'],
        $slideData['description'],
        $slideData['specialist_name'],
        $slideData['classification'],
        $slideData['link'],
        $slideData['button_text'],
        $slideData['button_link'],
        (int) $slideData['id'],
        (int) $slideData['banner_id'],
    ]);
}

// Функция для обновления изображения конкретного слайда
function update_slide_image(int $slideId, string $fileName): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_banners_sliders SET image = ? WHERE id = ?", [$fileName, $slideId]);
}

// Функция проверки, существует ли слайд в базе данных
function slide_exists($id, $banner_id)
{
    global $data_base;
    $result = $data_base->selectAll("SELECT COUNT(*) FROM dt_banners_sliders WHERE id = ? AND banner_id = ?", [$id, $banner_id]);
    return $result && $result[0]["COUNT(*)"] > 0;
}

// Функция для создания нового слайда
function create_slide(array $slideData): bool
{
    global $data_base;

    // Выполняем SQL-запрос на вставку данных
    return $data_base->add("INSERT INTO dt_banners_sliders 
        (banner_id, image, title, subtitle, description, specialist_name, classification, link, button_text, button_link) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        $slideData['banner_id'],
        $slideData['image'],
        $slideData['title'],
        $slideData['subtitle'],
        $slideData['description'],
        $slideData['specialist_name'],
        $slideData['classification'],
        $slideData['link'],
        $slideData['button_text'],
        $slideData['button_link']
    ]);
}

function delete_slide(int $slideId, int $bannerId): bool
{
    global $data_base;

    return $data_base->delete("DELETE FROM dt_banners_sliders WHERE id = ? AND banner_id = ?", [
        $slideId,
        $bannerId
    ]);
}


/**
 * @param int $id
 * @return mixed
 */
function get_tariff(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_tariff WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_tariffs(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_tariff");
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_tariff(array $data): bool
{
    global $data_base;

    return $data_base->update("UPDATE dt_tariff SET name = ?, price = ?, price_old = ?, options = ? WHERE id = ?", [
        $data['name'],
        (int) $data['price'],
        (int) $data['price_old'],
        $data['options'],
        (int) $data['id']
    ]);
}

/**
 * Получает подключённые тарифы для специалиста,
 * где текущая дата меньше, чем end_date.
 *
 * @param int $specialist_id
 * @return array
 */
function get_connected_tariffs(int $specialist_id): array
{
    global $data_base;
    return $data_base->selectAll("
        SELECT *
        FROM dt_tariff_list
        WHERE specialist_id = ?
          AND end_date > CURDATE()
          AND moderation = 0
    ", [$specialist_id]);
}

/**
 * @param int $id
 * @return mixed
 */
function get_promotion(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_promo_placement WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_promotions(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_promo_placement");
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_promotion(array $data): bool
{
    global $data_base;

    if (!isset($data['prices']) || !is_array($data['prices'])) {
        throw new Exception('Данные для обновления цен отсутствуют или некорректны.');
    }

    foreach ($data['prices'] as $id => $price) {
        $priceField = $price['field'];
        $priceValue = $price['value'];
        $id = (int) $id;

        if ($priceValue === '' || $priceValue === null) {
            continue;
        }

        $data_base->update("UPDATE dt_service SET $priceField = ? WHERE id = ?", [
            (int) $priceValue,
            $id
        ]);
    }

    return true;
}

function filter_valid_entry(array $entry): bool
{
    if ((int)$entry['moderation'] != 4) {
        return true; // не блокируем
    }

    if (empty($entry['connected_at'])) {
        return true; // не блокируем
    }

    $connected = strtotime($entry['connected_at']);

    // documentId есть?
    $hasDocument = !empty($entry['documentId']);

    if ($hasDocument) {
        return (time() < $connected + 3600); // 1 час
    } else {
        return (time() < $connected + 900); // 10 минут
    }
}

// Функция для получения дат размещения по service_slug и sort
function get_occupied_entries($service_slug, $sort)
{
    global $data_base;
    $rows = $data_base->selectAll(
        "SELECT date_start, date_expires, moderation, connected_at, qrcId, documentId
         FROM dt_promo_list
         WHERE service_slug = ? AND sort = ? AND moderation = 0
           AND date_expires >= CURRENT_DATE",
        [$service_slug, $sort]
    );
    $valid = array_filter($rows, 'filter_valid_entry');
    return array_map(function ($e) {
        return [
            'date_start'   => $e['date_start'],
            'date_expires' => $e['date_expires'],
        ];
    }, $valid);
}

// Функция для получения дат размещения по block_id и sort
function get_occupied_entries_by_block($block_id, $sort)
{
    global $data_base;
    $rows = $data_base->selectAll(
        "SELECT date_start, date_expires, moderation, connected_at, qrcId, documentId
         FROM dt_promo_list
         WHERE block_id = ? 
           AND sort = ? 
           AND (service_slug IS NULL OR service_slug = '')
           AND moderation = 0
           AND date_expires >= CURRENT_DATE",
        [$block_id, $sort]
    );
    $valid = array_filter($rows, 'filter_valid_entry');
    return array_map(fn($e) => [
        'date_start'   => $e['date_start'],
        'date_expires' => $e['date_expires'],
    ], $valid);
}

// Функция для получения дат размещения по block_id, service_slug и sort
function get_occupied_entries_by_block_and_service($block_id, $service_slug, $sort, $specialist_id)
{
    global $data_base;

    // Сначала определяем группу текущего специалиста
    $specialist = $data_base->selectRow(
        "SELECT classification_id FROM dt_specialist WHERE id = ?",
        [$specialist_id]
    );
    if (!$specialist) return [];

    $classification = (int)$specialist['classification_id'];

    // Определяем группу специалистов для блокировки
    if (in_array($classification, [1,2,5,6,7])) {
        $group = 1;
    } elseif (in_array($classification, [3,8])) {
        $group = 2;
    } elseif ($classification === 4) {
        $group = 3;
    } else {
        $group = 1; // дефолт
    }

    // Берем все активные записи по block_id, service_slug и sort
    $rows = $data_base->selectAll(
        "SELECT p.date_start, p.date_expires, s.classification_id
         FROM dt_promo_list p
         JOIN dt_specialist s ON s.id = p.specialist_id
         WHERE p.block_id = ? AND p.service_slug = ? AND p.sort = ? AND p.moderation = 0
           AND p.date_expires >= CURRENT_DATE",
        [$block_id, $service_slug, $sort]
    );

    $valid = array_filter($rows, function($e) use ($group) {
        // проверяем валидность по датам
        if (!filter_valid_entry($e)) return false;

        // определяем группу специалиста из promo записи
        $c = (int)$e['classification_id'];
        if (in_array($c, [1,2,5,6,7])) {
            $entry_group = 1;
        } elseif (in_array($c, [3,8])) {
            $entry_group = 2;
        } elseif ($c === 4) {
            $entry_group = 3;
        } else {
            $entry_group = 1;
        }

        // только если группы совпадают, считаем запись занятой
        return $entry_group === $group;
    });

    return array_map(fn($e) => [
        'date_start'   => $e['date_start'],
        'date_expires' => $e['date_expires'],
    ], $valid);
}

// Функция для подсчёта записей по service_slug
function get_entry_count_by_service_slug($service_slug)
{
    global $data_base;
    $count = $data_base->count(
        "SELECT COUNT(*) FROM dt_promo_list WHERE service_slug = ?",
        [$service_slug]
    );
    return (int)$count;
}

// Функция для подсчёта записей по block_id
function get_entry_count_by_block($block_id)
{
    global $data_base;
    $count = $data_base->count(
        "SELECT COUNT(*) 
         FROM dt_promo_list 
         WHERE block_id = ? 
           AND (service_slug IS NULL OR service_slug = '')",
        [$block_id]
    );
    return (int)$count;
}

// Проверка конфликта по service_slug
function check_conflict_by_service_slug($service_slug, $specialist_id, $sort)
{
    global $data_base;
    $rows = $data_base->selectAll(
        "SELECT date_start, date_expires, sort, moderation, connected_at, documentId
         FROM dt_promo_list
         WHERE service_slug = ? AND specialist_id = ? AND moderation = 0
           AND date_expires >= CURRENT_DATE",
        [$service_slug, $specialist_id]
    );
    $valid = array_filter($rows, 'filter_valid_entry');
    foreach ($valid as $entry) {
        if ($entry['sort'] != $sort) {
            return true;
        }
    }
    return false;
}

// Проверка конфликта по block_id
function check_conflict_by_block($block_id, $specialist_id, $sort)
{
    global $data_base;
    $rows = $data_base->selectAll(
        "SELECT date_start, date_expires, sort, moderation, connected_at, documentId
         FROM dt_promo_list
         WHERE block_id = ? AND specialist_id = ? AND moderation = 0
           AND date_expires >= CURRENT_DATE",
        [$block_id, $specialist_id]
    );
    $valid = array_filter($rows, 'filter_valid_entry');
    foreach ($valid as $entry) {
        if ($entry['sort'] != $sort) {
            return true;
        }
    }
    return false;
}

// Получение конфликтующих записей по специалисту и service_slug + опционально sort
function get_conflicting_entries($specialist_id, $service_slug, $sort = null)
{
    global $data_base;
    $params = [$specialist_id, $service_slug];
    $sql = "SELECT date_start, date_expires, moderation, connected_at, qrcId, documentId
            FROM dt_promo_list
            WHERE specialist_id = ?
              AND service_slug = ? AND moderation = 0
              AND date_expires >= CURRENT_DATE";
    if ($sort !== null) {
        $sql .= " AND sort = ?";
        $params[] = $sort;
    }
    $rows = $data_base->selectAll($sql, $params);
    $valid = array_filter($rows, 'filter_valid_entry');
    return array_map(fn($e) => [
        'date_start'   => $e['date_start'],
        'date_expires' => $e['date_expires'],
    ], $valid);
}

// Получение конфликтующих записей по специалисту и block_id
function get_conflicting_entries_by_block($specialist_id, $block_id)
{
    global $data_base;
    $rows = $data_base->selectAll(
        "SELECT date_start, date_expires, moderation, connected_at, qrcId, documentId
         FROM dt_promo_list
         WHERE specialist_id = ? 
           AND block_id = ? 
           AND (service_slug IS NULL OR service_slug = '')
           AND moderation = 0
           AND date_expires >= CURRENT_DATE",
        [$specialist_id, $block_id]
    );
    $valid = array_filter($rows, 'filter_valid_entry');
    return array_map(fn($e) => [
        'date_start'   => $e['date_start'],
        'date_expires' => $e['date_expires'],
    ], $valid);
}

/**
 * Получает подключённые промо-услуги для специалиста,
 * где текущая дата меньше, чем end_date.
 *
 * @param int $specialist_id
 * @return array
 */
function get_connected_promo_placements(int $specialist_id): array
{
    global $data_base;
    $today = date('Y-m-d');
    return $data_base->selectAll("
        SELECT *
        FROM dt_promo_list
        WHERE specialist_id = ?
          AND date_expires > $today
          AND (
              moderation = 0
              OR (
                  moderation = 4
                  AND connected_at >= NOW() - INTERVAL 1 HOUR
                  AND documentId IS NOT NULL
                  AND documentId != ''
              )
          )
    ", [$specialist_id]);
}

function get_all_payments(?int $specialistId = null): array
{
    global $data_base;

    if ($specialistId !== null) {
        return $data_base->selectAll(
            "SELECT * 
             FROM dt_payments 
             WHERE specialist_id = ? 
             ORDER BY paymentDate DESC",
            [$specialistId]
        );
    } else {
        return $data_base->selectAll(
            "SELECT * 
             FROM dt_payments 
             ORDER BY date_start DESC"
        );
    }
}


function get_all_connected_promo_placements(int $specialist_id, string $orderBy = 'connected_at'): array
{
    global $data_base;

    // Разрешённые поля для сортировки
    $allowedFields = ['connected_at', 'date_start', 'date_expires', 'block_id'];

    // Проверка на допустимость поля сортировки
    if (!in_array($orderBy, $allowedFields, true)) {
        $orderBy = 'connected_at'; // Значение по умолчанию
    }

    return $data_base->selectAll("
        SELECT *
        FROM dt_promo_list
        WHERE specialist_id = ?
        AND moderation = 0
        ORDER BY $orderBy DESC
    ", [$specialist_id]);
}

function get_services_for_menu_with_prochie_last()
{
    global $data_base;

    // Теперь выбираем ещё два поля с ценами
    $services = $data_base->selectAll("
        SELECT
            s.id       AS id,
            s.title    AS title,
            s.p_id,
            s.slug,
            -- добавили:
            s.price_block1  AS price_block1,
            s.price_block2  AS price_block2,
            c.id       AS classification_id,
            CASE 
              WHEN s.title = 'Прочие юридические вопросы' THEN 999999
              WHEN s.title = 'Миграционные вопросы' THEN 30
              ELSE ROW_NUMBER() OVER (ORDER BY s.title)
            END AS sort_order
        FROM dt_service s
        LEFT JOIN dt_classification c ON s.classification = c.id
        WHERE s.original_id = 0
        ORDER BY sort_order, s.title
    ");

    $data = [];

    foreach ($services as $service) {
        $data[$service['classification_id']][$service['p_id']][$service['id']] = $service;
    }

    return $data;
}

/**
 * @param int $id
 * @return mixed
 */
function get_option(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_option WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_options(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_option ORDER BY id ASC");
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_option(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_option SET name = ?, price = ? WHERE id = ?", [
        $data['name'],
        (int) $data['price'],
        (int) $data['id']
    ]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function add_option(array $data): bool
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_option (name, price) VALUES (?, ?)", [
        $data['name'],
        (int) $data['price']
    ]);
}

/**
 * @param int $id
 * @return bool
 * @throws Exception
 */
function delete_option(int $id): bool
{
    global $data_base;
    $result = $data_base->delete("DELETE FROM dt_option WHERE id = ?", [$id]);

    return $result;
}

/**
 * Получает подключённые опции для специалиста,
 * где текущая дата меньше, чем end_date.
 *
 * @param int $specialist_id
 * @return array
 */
function get_connected_options(int $specialist_id): array
{
    global $data_base;
    return $data_base->selectAll("
        SELECT *
        FROM dt_options_list
        WHERE specialist_id = ?
          AND end_date > CURDATE()
          AND moderation = 0
        ORDER BY end_date DESC
    ", [$specialist_id]);
}

/**
 * Получает подключённые баннеры для специалиста,
 * где текущая дата меньше, чем end_date.
 *
 * @param int $specialist_id
 * @return array
 */
function get_connected_banners(int $specialist_id): array
{
    global $data_base;
    return $data_base->selectAll("
        SELECT *
        FROM dt_banners_list
        WHERE specialist_id = ?
          AND end_date > CURDATE()
          AND rejected = 0
        ORDER BY start_date DESC
    ", [$specialist_id]);
}

function get_all_connected_banners(int $specialist_id): array
{
    global $data_base;
    return $data_base->selectAll("
        SELECT *
        FROM dt_banners_list
        WHERE specialist_id = ?
        ORDER BY connected_at DESC
    ", [$specialist_id]);
}

function get_journals(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_journal ORDER BY id");
}

// Получаем подключённые записи журнала для специалиста (с проверкой по дате)
function get_connected_journals(int $specialist_id): array
{
    global $data_base;
    return $data_base->selectAll("
        SELECT *
        FROM dt_journal_list
        WHERE specialist_id = ?
          AND end_date > CURDATE()
          AND rejected = 0
          ORDER BY start_date DESC
    ", [$specialist_id]);
}

function get_all_connected_journals(int $specialist_id): array
{
    global $data_base;
    return $data_base->selectAll("
        SELECT *
        FROM dt_journal_list
        WHERE specialist_id = ?
        ORDER BY connected_at DESC
    ", [$specialist_id]);
}

function get_archived_banners(int $specialist_id, int $limit = null): array
{
    global $data_base;

    $sql = "
        SELECT id, banner_id AS object_id, start_date, end_date, description, rejected, connected_at, 'banner' AS type
        FROM dt_banners_list
        WHERE specialist_id = ?
          AND (end_date < CURDATE() OR rejected = 1)

        UNION ALL

        SELECT id, journal_id AS object_id, start_date, end_date, description, rejected, connected_at, 'journal' AS type
        FROM dt_journal_list
        WHERE specialist_id = ?
          AND (end_date < CURDATE() OR rejected = 1)

        ORDER BY connected_at DESC
    ";

    if ($limit !== null) {
        $sql .= " LIMIT " . intval($limit);
    }

    return $data_base->selectAll($sql, [$specialist_id, $specialist_id]);
}

function render_archive_card(array $archive): string
{
    $decodedDescription = json_decode($archive['description'], true);

    if ($archive['type'] === 'banner') {
        $title = get_banner_title_by_id((int) $archive['object_id']);
    } else {
        $title = get_journal_title_by_id((int) $archive['object_id']);
    }

    // Определяем статус
    if ($archive['rejected'] == 1) {
        $statusText = '<span class="text-danger fw-bold">Статус: Отклонен</span>';
    } else {
        $statusText = '<span class="text-success fw-bold">Статус: Опубликован</span>';
    }

    ob_start();
?>
    <div class="card my-3" id="archive-item-<?= $archive['id'] ?>">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Архивный запрос: <p><?= htmlspecialchars($title) ?></p></span>
            <button class="btn btn-dark bg-dark toggle-archive" data-archive-id="<?= $archive['id'] ?>">
                Подробнее
            </button>
        </div>
        <div class="card-body archive-details" id="archive-details-<?= $archive['id'] ?>" style="display: none;">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <strong>Дата запроса: </strong><?= date('d.m.Y', strtotime($archive['connected_at'])) ?>
                </div>
                <?= $statusText ?>
            </div>
            <p><strong>Период:</strong> с
                <?= date('d.m.Y', strtotime($archive['start_date'])) ?> по
                <?= date('d.m.Y', strtotime($archive['end_date'])) ?>
            </p>
            <p><strong>Описание:</strong></p>
            <?php if (is_array($decodedDescription)): ?>
                <?php foreach ($decodedDescription as $line): ?>
                    <p><?= htmlspecialchars($line) ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p><em>Описание отсутствует</em></p>
            <?php endif; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function get_admin_users(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_admin_users WHERE id != 1");
}

function get_admin_user(int $id): array
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_admin_users WHERE id = ?", [$id]);
}


// Вспомогательные функции для получения заголовка баннера или журнала по id
function get_banner_title_by_id(int $banner_id): string
{
    global $data_base;
    $result = $data_base->selectRow("SELECT name FROM dt_banners WHERE id = ?", [$banner_id]);
    return $result ? $result['name'] : '';
}

function get_journal_title_by_id(int $journal_id): string
{
    global $data_base;
    $result = $data_base->selectRow("SELECT name FROM dt_journal WHERE id = ?", [$journal_id]);
    return $result ? $result['name'] : '';
}

function get_forbidden_words()
{
    $data_base = new DataBase();
    $wordsArray = $data_base->selectDistinctColumn("SELECT word FROM dt_forbidden_words ORDER BY id");

    return implode(", ", $wordsArray);
}

/**
 * Число блогов.
 *
 * @return int
 */
function count_blog_posts(): int
{
    global $data_base;

    $result = $data_base->selectRow("
        SELECT COUNT(*) as count 
        FROM dt_post 
        WHERE post_type = 'blogs' AND moderation = '1'
    ");

    if (is_array($result) && isset($result['count'])) {
        return (int) $result['count'];
    }

    return 0;
}

/**
 * Число запросов на фотосъемку.
 *
 * @return int
 */
function count_pro_photo_list(): int
{
    global $data_base;

    $result = $data_base->selectRow("
        SELECT COUNT(*) as count 
        FROM dt_pro_photo_list WHERE moderation = '2'
    ");

    if (is_array($result) && isset($result['count'])) {
        return (int) $result['count'];
    }

    return 0;
}

/**
 * Число запросов баннеров и записей из журнала.
 *
 * @return int
 */
function count_banner_list(): int
{
    global $data_base;

    $result = $data_base->selectRow("
        SELECT (
            SELECT COUNT(*) 
            FROM dt_banners_list 
            WHERE moderation = 2
            AND end_date > NOW()
            AND rejected = 0
        ) +
        (
            SELECT COUNT(*) 
            FROM dt_journal_list 
            WHERE moderation = 2
            AND end_date > NOW()
            AND rejected = 0
        ) AS total_count
    ");

    if (is_array($result) && isset($result['total_count'])) {
        return (int) $result['total_count'];
    }

    return 0;
}

function truncate_text($text, $max_chars)
{
    return mb_strlen($text) > $max_chars ? mb_substr($text, 0, $max_chars) . '...' : $text;
}
