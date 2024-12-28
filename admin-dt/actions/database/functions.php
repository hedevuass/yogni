<?php

use Actions\Database\DataBase;

require_once $_SERVER['DOCUMENT_ROOT'] . '/actions/database/DataBase.php';

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
                                            sd.description, 
                                            sd.foundation, 
                                            sd.schedule as schedule, 
                                            sd.document as document,
                                            sd.university,
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
                    'is_active' => (bool)$service['ot'], // Преобразование `ot` в булевое значение
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
                                    WHERE specialist_id = ?
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
    if ($last_cat && $last_cat != "") $result[$last_cat['id']] = $last_cat;

    return $result;
}

function renderSpecializations(array $specializations, array $activeSpecializations): string
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
    $html .= list_specialization($specializations, $activeSpecializations);

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



function list_specialization(array $specializations, array $activeSpecializations): string
{
    $html = '';

    foreach ($specializations as $specialization) {
        if ($specialization['p_id'] != 0) continue; // Пропускаем вложенные

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
    $categories = $data_base->selectAll("SELECT * FROM dt_service WHERE p_id = $p_id ORDER BY title");
    $p_category = $data_base->selectRow("SELECT * FROM dt_service WHERE id = $p_id");

    $result = [];

    foreach ($categories as $category) {
        $category['p_slug'] = $p_category['slug'];
        $result[$category['id']] = $category;
    }

    return $result;
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
function get_specialists_filter(string $query, string $columnName, string $columnOrder, int $row, int $rowperpage, array $param): array
{
    global $data_base;
    return $data_base->selectAll("SELECT 
                                        ds.id, ds.name, ds.lastname, ds.email, ds.active, ds.photo, ds.rating,
                                        dsc.phone,
                                        dc.title as classification,
                                        dc2.name as city
                                        FROM dt_specialist ds 
                                    LEFT JOIN dt_specialist_contacts dsc on ds.id = dsc.specialist_id
                                    LEFT JOIN dt_classification dc on ds.classification_id = dc.id 
                                    LEFT JOIN dt_city dc2 on ds.city_id = dc2.id
                                    WHERE 1 $query
                                    ORDER BY $columnName $columnOrder
                                    LIMIT $row, $rowperpage", $param);
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

/**
 * @param array $data
 * @return string
 */
function set_specialist(array $data): string
{
    global $data_base;
    $result = $data_base->add("INSERT INTO dt_specialist (name, lastname, email, password, hash, photo, classification_id, city_id, company_id, active)
                                  VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        $data['name'],
        $data['lastname'],
        $data['email'],
        $data['password'],
        $data['hash'],
        $data['photo'],
        (int)$data['classification_id'],
        (int)$data['city_id'],
        $data['company_id'],
        (int)$data['active']
    ]);

    if ($result) {
        set_specialist_description($data, $result);
        set_specialist_contacts($data, $result);
    }

    return $result;
}

/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function update_specialist($data): bool
{
    global $data_base;

    // Обновление основной информации о специалисте
    $query = "UPDATE dt_specialist SET 
        name = ?, 
        lastname = ?, 
        email = ?, 
        photo = ?, 
        classification_id = ?, 
        city_id = ?, 
        active = ? 
        WHERE id = ?";

    $params = [
        $data['name'],
        $data['lastname'],
        $data['email'],
        $data['photo'],
        (int)$data['classification_id'],
        (int)$data['city_id'],
        (int)$data['active'],
        (int)$data['id']
    ];

    // Выполнение запроса на обновление специалиста
    $result = $data_base->update($query, $params);

    // Обновление телефона, если он есть
    if ($data['phone']) {
        update_specialist_phone($data['phone'], $data['id']);
    }

    // Обновление поля 'other' в таблице dt_specialist_contacts
    if ($result) {
        $updateContactsQuery = "
            UPDATE dt_specialist_contacts 
            SET other = ? 
            WHERE specialist_id = ?
        ";

        $result = $data_base->update($updateContactsQuery, [
            $data['other'],
            $data['id']
        ]);
    }

    // Обновление расписания и документов
    if ($result) {
        $updateDescriptionQuery = "
            UPDATE dt_specialist_description
            SET schedule = ?, document = ?
            WHERE specialist_id = ?
        ";

        $result = $data_base->update($updateDescriptionQuery, [
            $data['schedule'],
            $data['documents'],
            $data['id']
        ]);
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



function update_specialist_services($specialist_id, $services): bool
{
    global $data_base;

    // Сначала удаляем все старые услуги для данного специалиста
    $deleteQuery = "DELETE FROM dt_specialist_service WHERE specialist_id = ?";
    $data_base->delete($deleteQuery, [(int)$specialist_id]);

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
                (int)$service['is_active'],
                (int)$specialist_id,
                (int)$service['id']
            ];
            $data_base->update($query, $params);
        } elseif (isset($service['name']) && isset($service['price'])) {
            // Если новая услуга, добавляем её
            $query = "
                INSERT INTO dt_specialist_service (specialist_id, name, price, ot) 
                VALUES (?, ?, ?, ?)
            ";
            $params = [
                (int)$specialist_id,
                $service['name'],
                $service['price'],
                (int)$service['is_active']
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
    return $data_base->add("INSERT INTO dt_specialist_description (specialist_id, description, university, foundation, schedule, document) 
                        VALUE (?, ?, ?, ?, ?, ?)", [
        $specialist_id,
        $data['description'] ?: null,
        $data['university'] ?: '',
        $data['foundation'] ?: null,
        $data['schedule'] ?: '',
        $data['document'] ?: null
    ]);
}

/**
 * @param array $data
 * @param int $specialist_id
 * @return string
 */
function set_specialist_contacts(array $data, int $specialist_id): string
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_contacts (specialist_id, address, metro, street, house, how_to_get, phone, other) VALUE (?, ?, ?, ?, ?, ?, ?, ?)", [
        $specialist_id,
        $data['address'] ?: null,
        $data['metro'] ?: null,
        $data['street'] ?: '',
        $data['house'] ?: null,
        $data['how_to_get'] ?: '',
        $data['phone'],
        $data['other'] ?: ''
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
        (int)$data['id']
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
        // Проверяем, есть ли сотрудник с таким id в базе
        $existingEmployee = $data_base->selectRow("SELECT id FROM dt_company_employee WHERE id = ? AND company_id = ?", [
            $employeeId,
            $company_id
        ]);

        if ($existingEmployee) {
            // Если сотрудник уже существует, обновляем его данные
            $data_base->update("UPDATE dt_company_employee SET name = ?, post = ?, photo = ? WHERE id = ? AND company_id = ?", [
                $employee['name'],
                $employee['post'],
                $employee['photo'] ?? '',  // Если фото не передано, ставим пустую строку
                $employeeId,
                $company_id
            ]);
        } else {
            // Если сотрудник новый, добавляем его в базу
            $data_base->add("INSERT INTO dt_company_employee (name, post, photo, company_id) VALUES (?, ?, ?, ?)", [
                $employee['name'],
                $employee['post'],
                $employee['photo'] ?? '',  // Если фото не передано, ставим пустую строку
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
                WHEN s.title = 'Миграционные вопросы' THEN 30
                ELSE ROW_NUMBER() OVER (ORDER BY s.title)
            END AS sort_order
        FROM dt_service s
        LEFT JOIN dt_classification c ON s.classification = c.id
        ORDER BY sort_order, s.title
    ");
}


/**
 * @param int $id
 * @return mixed
 */
function get_menu_item(int $id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_service WHERE id = ?", [$id]);
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
    return $data_base->selectAll("SELECT * FROM dt_post");
}

/**
 * @param int $id
 * @return mixed
 */
function get_post(int $post_id): mixed
{
    global $data_base;
    return $data_base->selectRow("
        SELECT dt_post.*, dt_service.title AS theme_title
        FROM dt_post
        LEFT JOIN dt_service ON dt_post.theme_id = dt_service.id
        WHERE dt_post.id = ?
    ", [$post_id]);
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
function update_post(int $post_id): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_post SET moderation = 0 WHERE id = ?", [$post_id]);
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_journal($data): bool
{
    global $data_base;

    $result = $data_base->update(
        "UPDATE dt_post 
         SET title = ?, 
             alias = ?, 
             photo = ?, 
             theme_id = ?, 
             post_type = ?,
             date = ?,
             keywords = ?, 
             site_from_link = ?, 
             content = ?, 
             moderation = 0 
         WHERE id = ?",
        [
            $data['title'],
            $data['alias'],
            $data['photo'],
            $data['theme_id'],
            $data['post_type'],
            $data['timestamp'],
            $data['keywords'],
            $data['site_from_link'],
            $data['content'],
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
function add_journal($data): bool
{
    global $data_base;
    $result = $data_base->add("INSERT INTO dt_post (title, alias, photo, theme_id, post_type, keywords, site_from_link, content) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
        $data['title'],
        $data['alias'],
        $data['photo'],
        $data['theme_id'],
        $data['post_type'],
        $data['keywords'],
        $data['site_from_link'],
        $data['content']
    ]);

    return $result;
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
        $data['description'],
        (int)$data['id']
    ]);
}

function add_faq_pro_photo(array $data): bool
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_faq_pro_photo (title, description) VALUES (?, ?)", [
        $data['title'],
        $data['description']
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
        $data['description'],
        (int)$data['id']
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
        $data['description']
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
    return $data_base->update("UPDATE dt_faq_pages SET name = ?, alias = ?, content = ? WHERE id = ?", [
        $data['name'],
        $data['alias'],
        $data['content'],
        (int)$data['id']
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
    return $data_base->add("INSERT INTO dt_faq_pages (name, alias, content) VALUES (?, ?, ?)", [
        $data['name'],
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
        (int)$data['id']
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

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_journal_banner(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_banners_journal SET title = ?, link = ?, description = ?, image = ? WHERE id = ?", [
        $data['title'],
        $data['alias'],
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
        (int)$data['id']
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
function update_menu($data): bool
{
    global $data_base;
    $result = $data_base->update("UPDATE dt_service SET title = ?, slug = ?, img = ?, icon = ?, classification = ?, p_id = ?  WHERE id = ?", [
        $data['title'],
        $data['slug'],
        $data['photo'],
        $data['icon'],
        $data['classification_id'],
        $data['p_id'],
        $data['id']
    ]);

    return $result;
}

/**
 * @param $data
 * @return bool
 * @throws Exception
 */
function add_service($data): bool
{
    global $data_base;
    $result = $data_base->add("INSERT INTO dt_service (title, slug, img, classification, p_id) VALUES (?, ?, ?, ?, ?)", [
        $data['title'],
        $data['slug'],
        $data['photo'],
        $data['classification_id'],
        $data['p_id']
    ]);

    return $result;
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

/**
 * @return array
 */
function get_pro_photo(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_pro_photo");
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
            description = ?, 
            specialist_name = ?, 
            classification = ?, 
            link = ?, 
            button_text = ?, 
            button_link = ? 
        WHERE id = ? AND banner_id = ?", [
        $slideData['title'],
        $slideData['description'],
        $slideData['specialist_name'],
        $slideData['classification'],
        $slideData['link'],
        $slideData['button_text'],
        $slideData['button_link'],
        (int)$slideData['id'],
        (int)$slideData['banner_id'],
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
        (banner_id, image, title, description, specialist_name, classification, link, button_text, button_link) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        $slideData['banner_id'],
        $slideData['image'],
        $slideData['title'],
        $slideData['description'],
        $slideData['specialist_name'],
        $slideData['classification'],
        $slideData['link'],
        $slideData['button_text'],
        $slideData['button_link']
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
        (int)$data['price'],
        (int)$data['price_old'],
        $data['options'],
        (int)$data['id']
    ]);
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
    return $data_base->update("UPDATE dt_promo_placement SET price = ? WHERE id = ?", [
        (int)$data['price'],
        (int)$data['id']
    ]);
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
    return $data_base->selectAll("SELECT * FROM dt_option");
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
        (int)$data['price'],
        (int)$data['id']
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
        (int)$data['price']
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
        return (int)$result['count'];
    }

    return 0;
}

function truncate_text($text, $max_chars) {
    return mb_strlen($text) > $max_chars ? mb_substr($text, 0, $max_chars) . '...' : $text;
}


