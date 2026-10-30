<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/S.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Cases.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/CasesHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Gender.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/BaseInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/NamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianLanguage.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/Cases.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianCasesHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/FirstNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/MiddleNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/LastNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/GeographicalNamesInflection.php';

use Actions\Database\DataBase;

if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT'])) {
    define('DOCUMENT_ROOT', '/var/www/html'); // вручную задаём путь для cron
} else {
    define('DOCUMENT_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
}

require_once DOCUMENT_ROOT . '/actions/database/DataBase.php';

$data_base = new DataBase;

// Возвращает город по id
function get_city(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_city WHERE id = ?", [$id]);
}

// Возвращает все города
function get_cities()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_city");
}

// Возвращает список городов по имени
function get_cities_by_name(string $name)
{
    global $data_base;
    return $data_base->selectAll("SELECT *, name as value FROM dt_city WHERE name LIKE '%$name%'");
}

// Возвращает список городов для popup
function get_cities_for_popup()
{
    global $data_base;
    return $data_base->selectAll("SELECT c.id as id, c.name as name 
                                        FROM dt_city_menu cm
                                        LEFT JOIN dt_city c ON cm.city_id = c.id
                                        ORDER BY cm.sort");
}

// Возвращает все метро города
function get_city_metro(int $city_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_metro WHERE city_id = ?", [$city_id]);
}

// Возвращает выбранные метро
function get_metro_by_ids(string $ids)
{
    global $data_base;
    if (!empty($ids)) {
        $metro = $data_base->selectAll("SELECT * FROM dt_metro WHERE id IN ($ids)");
        foreach ($metro as &$m) {
            $m['checked'] = 'checked';
        }
    } else {
        $city_id = $_SESSION['city']['id'];
        $metro = get_metro_in_city($city_id);
    }

    return $metro;
}

// Возвращает не выбранные метро для полноты попапа
function get_extra_metro(string $ids, int $limit)
{
    global $data_base;
    $city_id = intval($_SESSION['city']['id']);
    $metro = $data_base->selectAll("SELECT * FROM dt_metro WHERE city_id = $city_id AND id NOT IN ($ids) LIMIT $limit");
    return $metro;
}

// Возвращает все метро в городе
function get_metro_in_city(int $city_id, int $limit = 4)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_metro WHERE city_id = ? LIMIT $limit", [$city_id]);
}

// Возвращает метро по имени
function get_metro_by_name(string $name)
{
    global $data_base;
    $city_id = $_SESSION['city']['id'];

    // Используем параметризованный запрос для безопасности
    $sql = "SELECT *, name AS value 
            FROM dt_metro 
            WHERE name LIKE :name AND city_id = :city_id";

    // Подготавливаем параметр для LIKE — добавляем % с обеих сторон
    $params = [
        ':name' => '%' . $name . '%',
        ':city_id' => $city_id,
    ];

    $metro = $data_base->selectAll($sql, $params);

    // Убираем дубли по name
    $unique = [];
    $result = [];

    foreach ($metro as $m) {
        if (!isset($unique[$m['name']])) {
            $unique[$m['name']] = true;
            $result[] = $m;
        }
    }

    return $result;
}

// Добавляет метро в адрес специалиста
function set_specialist_metro(int $specialist_id, string $metro)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_contacts (specialist_id, metro)
                                    VALUE (?, ?)", [$specialist_id, $metro]);
}

// Возвращает классификацию
function get_classification(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_classification WHERE id = ?", [$id]);
}

// Возвращает все классификации
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

// Возвращает категорию
function get_service(int $service_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_service WHERE id = ?", [$service_id]);
}

/**
 * Возвращает все категории
 * @return array
 */
function get_services(): array
{
    global $data_base;
    $array = $data_base->selectAll("SELECT * FROM dt_service");

    $result = [];

    foreach ($array as $value) {
        $result[$value['id']] = $value;
    }

    return $result;
}

/**
 * Возвращает все категории
 * @return array
 */
function get_actual_services_from_db(): array
{
    global $data_base;

    $array = $data_base->selectAll("
        SELECT s.*, c.id AS classification_id
        FROM dt_service_actual s
        LEFT JOIN dt_classification c ON s.classification = c.id
    ");

    $result = [];

    foreach ($array as $value) {
        $result[$value['id']] = $value;
    }

    return $result;
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

function check_alias(string $alias, int $post_id = 0)
{
    global $data_base;
    $and = '';
    if ($post_id != 0) {
        $and = ' AND id != ' . $post_id;
    }
    $result = $data_base->selectRow("SELECT * FROM dt_post WHERE alias = ? $and", [$alias]);
    if (!is_null($result) && $result)
        return true;
    else
        return false;
}


// Устанавливает slug для категорий
function update_service_slug(int $service_id, string $slug)
{
    global $data_base;
    return $data_base->update("UPDATE dt_service SET slug = ? WHERE id = ?", [$slug, $service_id]);
}

// Возвращает категории по направлениям
function get_services_by_classification($classification, int|bool $p_id = false)
{
    global $data_base;

    $where = '';
    $array = [];

    // Handle classification
    if (is_int($classification)) {
        $classification = match ($classification) {
            1, 2, 5, 6, 7 => 1,
            3, 8 => 3,
            4 => 0
        };
        $where = 'classification = ?';
        $array[] = $classification;
    } elseif (is_array($classification)) {
        $placeholders = implode(',', array_fill(0, count($classification), '?'));
        $where = "classification IN ($placeholders)";
        $array = $classification;
    }

    // Add p_id condition only if it's not false
    if ($p_id !== false) {
        // If it's the first condition, start with where clause, otherwise use AND
        $where .= ($where ? ' AND ' : '') . 'p_id = ?';
        $array[] = (int) $p_id; // Cast p_id to integer
    }

    // Always include original_id condition
    $where .= ($where ? ' AND ' : '') . 'original_id = 0';

    // Prepare the query
    $query = "SELECT * FROM dt_service WHERE $where ORDER BY title";

    // Execute the query with the prepared array
    try {
        $categories = $data_base->selectAll($query, $array);
    } catch (PDOException $e) {
        // Handle possible errors here
        echo "Database error: " . $e->getMessage();
        return [];
    }

    $result = [];
    $last_cat = null;

    // Organize the results
    foreach ($categories as $category) {
        if (mb_stripos($category['title'], 'прочие') !== false) {
            $last_cat = $category;
            continue;
        }
        $result[$category['id']] = $category;
    }

    // Ensure the 'прочие' category is last
    if (!empty($last_cat)) {
        $result[$last_cat['id']] = $last_cat;
    }

    return $result;
}

// Возвращает список услуг для меню
function get_services_for_menu()
{
    global $data_base;

    // Модифицированный запрос с фильтрацией по original_id = 0
    $services = $data_base->selectAll("SELECT s.id as id, s.title as title, s.p_id, s.icon, s.slug,
                                            c.id as classification_id,
                                            CASE 
                                                WHEN s.id = 97 THEN 999999
                                                WHEN s.id = 81 THEN 30
                                                ELSE ROW_NUMBER() OVER (ORDER BY s.title)
                                            END as sort_order
                                       FROM dt_service s
                                       LEFT JOIN dt_classification c ON s.classification = c.id
                                       WHERE s.original_id = 0
                                       ORDER BY sort_order, s.title");

    $data = [];

    foreach ($services as $service) {
        $data[$service['classification_id']][$service['p_id']][$service['id']] = $service;
    }

    return $data;
}


function get_services_for_menu_with_prochie_last()
{
    global $data_base;

    // Модифицированный запрос с фильтрацией по original_id = 0
    $services = $data_base->selectAll("SELECT s.id as id, s.title as title, s.p_id, s.slug, s.icon, s.price_block1, s.price_block2,
                                            c.id as classification_id,
                                            CASE 
                                                WHEN s.title = 'Прочие юридические вопросы' THEN 999999  -- Ставим большое число, чтобы сортировать последним
                                                WHEN s.title = 'Миграционные вопросы' THEN 30
                                                ELSE ROW_NUMBER() OVER (ORDER BY s.title)
                                            END as sort_order
                                       FROM dt_service s
                                       LEFT JOIN dt_classification c ON s.classification = c.id
                                       WHERE s.original_id = 0 
                                       ORDER BY sort_order, s.title");

    $data = [];

    // Формируем массив услуг по классификации и родительской категории
    foreach ($services as $service) {
        $data[$service['classification_id']][$service['p_id']][$service['id']] = $service;
    }

    return $data;
}



// Возвращает тег по alias
function get_tag_by_alias(string $alias)
{
    global $data_base;
    $tag = $data_base->selectRow("SELECT * FROM dt_tags WHERE alias = ?", [$alias]);
    return $tag;
}

// Возвращает категорию по slug
function get_service_by_slug(string $slug)
{
    global $data_base;
    // Берём запись (оригинал или альтернатива) по slug
    $row = $data_base->selectRow(
        "SELECT * FROM dt_service WHERE slug = ?",
        [$slug]
    );
    if (!$row) {
        return null;
    }

    // Если это альтернатива — подгружаем оригинал
    $original = null;
    if ($row['original_id'] && $row['original_id'] != 0) {
        $original = $data_base->selectRow(
            "SELECT * FROM dt_service WHERE id = ?",
            [$row['original_id']]
        );
    }

    // Берём «рабочий» набор полей из оригинала, если он есть, иначе — из текущей
    $base = $original ?: $row;

    // Подтягиваем родителя по p_id рабочего набора
    $parrent = $base['p_id']
        ? get_service($base['p_id'])
        : null;

    // Формируем массив классификаций на основе значения classification
    $classification = intval($base['classification']);
    if ($classification === 1) {
        $classification_array = [1, 2, 5, 6, 7];
    } elseif ($classification === 3) {
        $classification_array = [3, 8];
    } else {
        $classification_array = [$classification];
    }

    return [
        'id'          => $row['id'],
        'slug'        => $row['slug'],
        'base_slug'   => $original['slug'],
        'original_id' => $row['original_id'],
        'base_id'     => $original ? $original['id'] : $row['id'],
        'title'       => $row['title'],
        'description' => $row['description'],
        'img'         => $base['img'],
        'p_id'        => $base['p_id'],
        'classification' => $classification,
        'classification_array' => $classification_array,
        'p_title'     => $parrent['title'] ?? null,
        'p_slug'      => $parrent['slug'] ?? null,
        'original'    => $original,
    ];
}

/* function mb_lcfirst($string)
{
    $firstChar = mb_substr($string, 0, 1);
    $then = mb_substr($string, 1);
    return mb_strtolower($firstChar) . $then;
} */

function get_all_service_meta($filter = null, $filter_labels = [], $cityGenitive = '', $cityPrepositional = '')
{
    global $data_base;

    $tables = ['dt_service_parent', 'dt_service'];
    $result = [];

    foreach ($tables as $table) {
        $services = $data_base->selectAll(
            "SELECT slug, meta_title, meta_description, meta_keywords FROM {$table}"
        );

        foreach ($services as $service) {
            $slug = trim($service['slug']);
            
            $title = strip_tags(html_entity_decode($service['meta_title']));
            $description = strip_tags(html_entity_decode($service['meta_description']));

            if ($cityGenitive || $cityPrepositional) {
                
                // 1) ПРАВИЛА ДЛЯ TITLE (используем разделитель "|" для идеального позиционирования)
                $titlePatterns = [
                    '/((?:Частные\s+)?Каталог\s+йога[- ]студий)/iu' => $cityGenitive,
                    '/((?:Частные\s+)?Студии\s+йоги)/iu'          => $cityGenitive,
                    // Забирает всё от "Занятия по" до знака "|" или конца строки
                    '/((?:Частные\s+)?Занятия\s+по\s+[^|]+?)(?=\s*(?:\||$))/iu' => 'в ' . $cityPrepositional
                ];

                $titleMatched = false;
                foreach ($titlePatterns as $pattern => $cityValue) {
                    if (empty($cityValue)) continue;

                    if (preg_match($pattern, $title)) {
                        $title = preg_replace($pattern, '$1 ' . $cityValue, $title, 1);
                        $titleMatched = true;
                        break; 
                    }
                }
                
                // Фолбек для TITLE: если совпадений вообще не найдено
                if (!$titleMatched && $cityGenitive) {
                    $title .= ' ' . $cityGenitive;
                }

                // 2) ПРАВИЛА ДЛЯ DESCRIPTION (обычный текст, ищем "йоге" + 1 слово стиля)
                $descPatterns = [
                    '/((?:Частные\s+)?Каталог\s+йога[- ]студий)/iu' => $cityGenitive,
                    '/((?:Частные\s+)?Студии\s+йоги)/iu'          => $cityGenitive,
                    // Захватит "Занятия по...йоге" ИЛИ "Занятия по йоге [Стиль]" (одно слово после)
                    '/((?:Частные\s+)?Занятия\s+по\s+(?:йоге\s+[\p{L}-]+|.*?йоге))/iu' => 'в ' . $cityPrepositional
                ];

                foreach ($descPatterns as $pattern => $cityValue) {
                    if (empty($cityValue)) continue;

                    if (preg_match($pattern, $description)) {
                        $description = preg_replace($pattern, '$1 ' . $cityValue, $description, 1);
                        break; 
                    }
                }
            }

            $result[$slug] = [
                'title'       => $title,
                'description' => $description,
                'keywords'    => $service['meta_keywords'],
            ];
        }
    }

    return $result;
}

function get_service_parent(string $slug)
{
    global $data_base;

    $service = $data_base->selectRow(
        "SELECT meta_title, meta_description, meta_keywords, icon
           FROM dt_service_parent 
          WHERE slug = ?",
        [$slug]
    );

    if (!$service) {
        return null;
    }

    return [
        'title' => strip_tags(html_entity_decode($service['meta_title'])),
        'description' => strip_tags(html_entity_decode($service['meta_description'])),
        'keywords' => $service['meta_keywords']
    ];
}

function get_service_parent_catalog(string $slug)
{
    global $data_base;
    $service = $data_base->selectRow("SELECT * FROM dt_service_parent WHERE slug = ? AND original_id = 0", [$slug]);
    return $service;
}

/**
 * Получает все slug из dt_service_parent
 *
 * @return array
 */
function get_catalog_slugs(): array
{
    global $data_base;

    static $slugs = null; // кешируем, чтобы не дёргать БД каждый раз
    if ($slugs === null) {
        $rows = $data_base->selectAll("SELECT slug FROM dt_service_parent");
        $slugs = array_column($rows, 'slug');
    }

    return $slugs;
}

function get_service_parent_by_slug(string $slug)
{
    global $data_base;

    // Берём запись (оригинал или альтернатива) по slug
    $row = $data_base->selectRow(
        "SELECT * FROM dt_service_parent WHERE slug = ?",
        [$slug]
    );
    if (!$row) {
        return null;
    }

    // Если это альтернатива — подгружаем оригинал
    $original = null;
    if ($row['original_id'] && $row['original_id'] != 0) {
        $original = $data_base->selectRow(
            "SELECT * FROM dt_service_parent WHERE id = ?",
            [$row['original_id']]
        );
    }

    // Рабочий набор — оригинал или сама запись
    $base = $original ?: $row;

    return [
        'id'          => $row['id'],
        'slug'        => $row['slug'],
        'base_slug'   => $base['slug'],
        'title'       => $row['title'],
        'description' => $row['description'],
        'icon'        => $row['icon'],
        // Метаданные — из оригинала (если есть)
        'meta_title'       => $base['meta_title'],
        'meta_description' => $base['meta_description'],
        'meta_keywords'    => $base['meta_keywords'],
        'original'    => $original,
    ];
}

function search_services(string $search)
{
    global $data_base;
    $search = '%' . $search . '%';
    $categories = $data_base->selectAll("SELECT * FROM dt_service WHERE title LIKE ? AND classification = 3 ", [$search]);
    return $categories;
}

// Добавляет специалиста в базу данных
function set_specialist(array $data)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist (name, lastname, photo, classification_id, city_id, company_id)
                                    VALUE (?, ?, ?, ?, ?, ?)", [$data['name'], $data['lastname'], $data['photo'], $data['classification_id'], $data['city_id'], $data['company']]);
}

// Добавляет специализацию специалиста в базу данных
function set_specialist_specialization(int $specialist_id, int $service_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_specialization (specialist_id, service_id)
                                    VALUE (?, ?)", [$specialist_id, $service_id]);
}

// Добавляет рейтинг специалиста
function set_specialist_rating(int $specialist_id, int $rating = 5)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_review_all (specialist_id, rating)
                                    VALUE (?, ?)", [$specialist_id, $rating]);
}


// Тарифы ПРО-фото
function get_pro_photo_by_id(int $id): array
{
    global $data_base;
    $result = $data_base->selectRow("SELECT * FROM dt_pro_photo WHERE id = ?", [$id]);

    return $result;
}

function get_faq_pro_photo()
{
    global $data_base;
    return $data_base->selectAll("SELECT * from dt_faq_pro_photo");
}

// Возвращает специалиста
function get_specialist(int $id)
{
    global $data_base;

    return $data_base->selectRow("
        SELECT 
            s.*,
            s.meta_title,
            s.meta_description,
            sc.street,
            sc.house,
            sc.how_to_get,
            sc.coordinates,
            sc.phone,
            sc.other,
            sc.metro AS metro_id,
            m.name AS metro,
            c.title AS classification, 
            city.name AS city, 
            se.title AS specialization,
            sd.description, 
            sd.foundation, 
            sd.schedule AS schedule, 
            sd.document,
            sd.inn,
            sd.university,
            com.title AS company,
            com.classification AS company_classification_id,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count,
            (SELECT COUNT(*) + 1 FROM dt_specialist WHERE rating > s.rating) AS rang,
            (SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = ? ) AS rating,
            (SELECT COUNT(rating) FROM dt_specialist_review WHERE specialist_id = ? ) AS count_rating
        FROM dt_specialist s
        LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city city ON s.city_id = city.id
        LEFT JOIN dt_service se 
            ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
        LEFT JOIN dt_specialist_description sd ON s.id = sd.specialist_id
        LEFT JOIN dt_company com ON s.company_id = com.id
        LEFT JOIN dt_metro m ON sc.metro = m.id
        WHERE s.id = ?
          AND (
                s.deleted = 0
                OR (
                    s.deleted = 1
                    AND s.deleted_at IS NOT NULL
                    AND s.deleted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                )
          )
    ", [$id, $id, $id]);
}

function get_specialists_by_company(int $company_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT 
                                    id, 
                                    name, 
                                    lastname,
                                    slug, 
                                    classification_id 
                               FROM dt_specialist 
                               WHERE company_id = ? AND deleted = 0", [$company_id]);
}

function get_specialist_by_slug(string $slug)
{
    global $data_base;
    return $data_base->selectRow("SELECT 
                                    s.*,    
                                    s.meta_title,
                                    s.meta_description,
                                    sc.street,
                                    sc.house,
                                    sc.how_to_get,
                                    sc.coordinates,
                                    sc.phone,
                                    sc.other,
                                    sc.metro as metro_id,
                                    m.name as metro,
                                    c.title as classification, 
                                    city.name as city, 
                                    se.title as specialization,
                                    sd.description, 
                                    sd.foundation, 
                                    sd.schedule as schedule, 
                                    sd.document,
                                    sd.university,
                                    com.title as company,
                                    com.classification AS company_classification_id,
                                    (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) as specialization_count,
                                    (SELECT COUNT(*) + 1 FROM dt_specialist WHERE rating > s.rating) as rang,
                                    (SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id ) as rating,
                                    (SELECT COUNT(rating) FROM dt_specialist_review WHERE specialist_id = s.id ) as count_rating
                                FROM dt_specialist s
                                LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
                                LEFT JOIN dt_classification c ON s.classification_id = c.id
                                LEFT JOIN dt_specialist_review r ON s.id = r.specialist_id
                                LEFT JOIN dt_city city ON s.city_id = city.id
                                LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                LEFT JOIN dt_specialist_description sd ON s.id = sd.specialist_id
                                LEFT JOIN dt_company com ON s.company_id = com.id
                                LEFT JOIN dt_metro m ON sc.metro = m.id
                                WHERE s.slug = ? AND s.deleted = 0", [$slug]);
}

// Возвращает специалистов
function get_specialists_by_ids(array $ids)
{
    global $data_base;

    if (empty($ids)) return false;

    $ids_int = array_map('intval', $ids);
    $ids_placeholders = implode(',', $ids_int);

    $specialists = $data_base->selectAll("
        SELECT s.id, s.name, s.lastname, s.photo,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id),0) AS rating,
            MAX(c.title) as classification_title,
            MAX(ci.name) as city_name,
            MAX(se.title) as specialization,
            MAX(cm.name) as metro_name,
            MAX(co.title) as company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) as specialization_count
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
        LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE s.id IN ($ids_placeholders) AND s.deleted = 0
        GROUP BY s.id
        ORDER BY FIELD(s.id, $ids_placeholders)
    ");

    return $specialists ?: false;
}


function count_specialists_by_ids($ids)
{
    global $data_base;
    $array = [];
    $and = '';
    if (isset($_GET['company']) && !isset($_GET['specialist'])) {
        $and = 'AND s.company_id IS NOT NULL';
    }

    if (isset($_GET['specialist']) && !isset($_GET['company'])) {
        $and = 'AND s.company_id IS NULL';
    }

    if (is_array($ids) && count($ids) > 1) {
        $where = 'IN (' . implode(',', $ids) . ')';
    } else if (is_array($ids) && count($ids) == 1) {
        $where = '= ?';
        $array = [$ids[0]];
    } else if (is_array($ids) && empty($ids)) {
        return false;
    }

    $specialists = $data_base->count("SELECT COUNT(DISTINCT s.id)
                                            FROM dt_specialist s
                                            LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
                                            LEFT JOIN dt_metro cm ON sc.metro = cm.id
                                            WHERE s.id $where $and AND s.deleted = 0", $array);

    return $specialists;
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

function get_all_equipment()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_equipment ORDER BY name ASC");
}

function get_specialist_equipment(int $specialist_id): array
{
    global $data_base;
    
    $equipment = $data_base->selectAll(
        "SELECT * FROM dt_specialist_equipment WHERE specialist_id = ?", 
        [$specialist_id]
    );

    $result = [];
    foreach ($equipment as $item) {
        // Индексируем по ID оборудования, чтобы удобно проверять через array_key_exists
        $result[$item['equipment_id']] = $item;
    }

    return $result;
}

function get_equipment_by_category(string $category): array
{
    global $data_base;
    return $data_base->selectAll(
        "SELECT id, name FROM dt_equipment WHERE category = ? ORDER BY name ASC", 
        [$category]
    );
}

// Возвращает все услуги специалсита
function get_specialist_service(int $specialist_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_specialist_service WHERE specialist_id = ?", [$specialist_id]);
}

// Возвращает все отзывы о специалисте
function get_specialist_reviews(int $specialist_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_specialist_review WHERE specialist_id = ? ORDER BY date DESC", [$specialist_id]);
}

// Возвращает темы блогов
function get_blog_filter_themes()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_service WHERE p_id = 0 AND original_id = 0 AND classification = 3 ORDER BY title");
}

// Возвращает теги блогов
function get_blog_filter_tags()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_tags ORDER BY title");
}

// Возвращает блог специалиста
function get_specialist_blog(int $specialist_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_blog WHERE specialist_id = ?", [$specialist_id]);
}

function add_blog(int $specialist_id): bool
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_blog (specialist_id) VALUES (?)", [$specialist_id]);
}

// Возвращает блог по айди
function get_blog_by_id(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_blog WHERE id = $id");
}

// Возвращает актуальные записи для журнала
function get_actual_posts(
    string $post_type = '',
    int    $post_theme = 0,
    int    $post_tag   = 0,
    int    $limit      = 5,
    int    $start      = 0,
    int    $blog_id    = 0,
    string $max_date   = '',
    int    $exclude_blog_id = 0
) {
    global $data_base;

    // Формируем условия WHERE
    $where_post_type = $post_type !== '' ? " AND ar.post_type = '{$post_type}'" : '';
    $where_post_theme = $post_theme !== 0 ? " AND ar.theme_id = {$post_theme}" : '';
    $where_post_tag = $post_tag !== 0 ? " AND ar.id IN (SELECT article_id FROM dt_post_tags WHERE tag_id = {$post_tag})" : '';
    $where_blog = $blog_id > 0 ? " AND ar.blog_id = {$blog_id}" : '';
    $where_exclude_blog = $exclude_blog_id > 0 ? " AND (ar.blog_id IS NULL OR ar.blog_id != {$exclude_blog_id})" : '';
    $where_date = $max_date !== '' ? " AND ar.date <= '{$max_date}'" : '';

    // Находим глобальную минимальную дату для всех постов
    $min_date_query = $data_base->selectRow("
        SELECT MIN(ar.date) AS min_date
        FROM dt_post ar
        LEFT JOIN dt_specialist s ON ar.author_id = s.id
        WHERE ar.moderation = 0
          AND (s.deleted = 0 OR s.id IS NULL)
          {$where_exclude_blog}
    ");

    $global_min_date = !empty($min_date_query['min_date']) ? $min_date_query['min_date'] : null;

    // Если нет постов, возвращаем пустой массив
    if (!$global_min_date && !$max_date && !$blog_id && !$post_type && !$post_theme && !$post_tag) {
        return [];
    }

    // Выбираем посты
    $posts = $data_base->selectAll("
        SELECT ar.*,
               se.id    AS service_id,
               se.title AS service_title,
               se.slug  AS service_slug
        FROM dt_post ar
        LEFT JOIN dt_blog bl ON ar.blog_id = bl.id
        LEFT JOIN dt_service se ON se.id = ar.theme_id
        LEFT JOIN dt_specialist s ON ar.author_id = s.id
        WHERE ar.moderation = 0
          AND (s.deleted = 0 OR s.id IS NULL)
          {$where_post_type}
          {$where_post_theme}
          {$where_post_tag}
          {$where_blog}
          {$where_exclude_blog}
          {$where_date}
        ORDER BY ar.date DESC
        LIMIT {$start}, {$limit}
    ");

    // Проверяем, достигнут ли самый старый пост
    if (!empty($posts) && $global_min_date) {
        $last_post_date = end($posts)['date'];
        if ($last_post_date <= $global_min_date && count($posts) < $limit) {
            return $posts;
        } elseif ($last_post_date <= $global_min_date) {
            $filtered_posts = [];
            foreach ($posts as $post) {
                if ($post['date'] >= $global_min_date) {
                    $filtered_posts[] = $post;
                }
            }
            return $filtered_posts;
        }
    }

    return $posts;
}


function get_actual_posts_main()
{
    global $data_base;
    return $data_base->selectAll("SELECT ar.*,
                                    se.id as service_id,
                                    se.title as service_title,
                                    se.slug as service_slug
                                    FROM dt_post ar
                                    LEFT JOIN dt_blog bl ON ar.blog_id = bl.id
                                    LEFT JOIN dt_service se ON se.id = ar.theme_id
                                    WHERE ar.moderation=0 AND ar.post_type='articles'
                                    ORDER BY date DESC LIMIT 2");
}

function get_banner(int $id): mixed
{
    global $data_base;

    // Получаем дефолтные слайды из таблицы dt_banners_sliders
    $default_slides = $data_base->selectAll(
        "SELECT * FROM dt_banners_sliders WHERE banner_id = ?",
        [$id]
    );

    // Ищем активную запись в таблице dt_banners_list для данного баннера
    $active_banner = $data_base->selectRow(
        "SELECT description, specialist_id 
           FROM dt_banners_list 
          WHERE banner_id = ? 
            AND moderation = 0 
            AND CURDATE() BETWEEN start_date AND end_date
            AND rejected = 0",
        [$id]
    );

    if ($active_banner && count($default_slides) > 0) {
        $active_desc = json_decode($active_banner['description'], true);
        $new_description = [
            'line1' => $active_desc['ad_line1'] ?? '',
            'line2' => $active_desc['ad_line2'] ?? '',
            'line3' => $active_desc['ad_line3'] ?? '',
        ];
        $default_slides[0]['description'] = json_encode($new_description);

        $specialist_name = '';
        $specialist_classification = '';
        $metro_name = '';

        if (!empty($active_banner['specialist_id'])) {
            $specialist_id = $active_banner['specialist_id'];

            // Получаем данные специалиста
            $specialist = $data_base->selectRow(
                "SELECT company_id, name, lastname, slug, classification_id 
                   FROM dt_specialist 
                  WHERE id = ?",
                [$specialist_id]
            );

            if ($specialist) {
                if (empty($specialist['company_id'])) {
                    $specialist_name = trim($specialist['name'] . ' ' . $specialist['lastname']);
                } else {
                    $company = $data_base->selectRow(
                        "SELECT title 
                           FROM dt_company 
                          WHERE id = ?",
                        [$specialist['company_id']]
                    );
                    if ($company) {
                        $specialist_name = $company['title'];
                    }
                }

                if (!empty($specialist['classification_id'])) {
                    $classification = $data_base->selectRow(
                        "SELECT title 
                           FROM dt_classification 
                          WHERE id = ?",
                        [$specialist['classification_id']]
                    );
                    if ($classification) {
                        $specialist_classification = $classification['title'];
                    }
                }
            }

            // Получаем метро специалиста из contacts
            $contact = $data_base->selectRow(
                "SELECT metro 
                   FROM dt_specialist_contacts 
                  WHERE specialist_id = ?",
                [$specialist_id]
            );
            if ($contact && !empty($contact['metro'])) {
                $metroRow = $data_base->selectRow(
                    "SELECT name 
                       FROM dt_metro 
                      WHERE id = ?",
                    [$contact['metro']]
                );
                if ($metroRow) {
                    $metro_name = $metroRow['name'];
                }
            }

            $default_slides[0]['specialist_id'] = $specialist_id;
        }

        if ($specialist_name) {
            $default_slides[0]['specialist_title'] = $specialist_name;
        }
        if ($specialist_classification) {
            $default_slides[0]['specialist_classification'] = $specialist_classification;
        }
        if ($metro_name) {
            $default_slides[0]['metro'] = $metro_name;
        }
    }

    return $default_slides;
}

function get_active_journal_ad(int $journal_id): ?array
{
    global $data_base;

    // Выбираем объявление вместе с данными специалиста и компании
    $row = $data_base->selectRow(
        "SELECT jl.description,
                jl.specialist_id,
                s.name,
                s.lastname,
                s.company_id,
                c.title AS company_name
           FROM dt_journal_list jl
           LEFT JOIN dt_specialist s ON jl.specialist_id = s.id
           LEFT JOIN dt_company c ON s.company_id = c.id
          WHERE jl.journal_id = ?
            AND jl.moderation = 0
            AND jl.rejected = 0
            AND CURDATE() BETWEEN jl.start_date AND jl.end_date
          LIMIT 1",
        [$journal_id]
    );

    if (!$row) {
        return null;
    }

    $desc_json = json_decode($row['description'], true) ?: [];

    // Собираем displayName: компания или специалист
    if (!empty($row['company_id']) && $row['company_name']) {
        $displayName = $row['company_name'];
    } else {
        $displayName = trim(($row['name'] ?? '') . ' ' . ($row['lastname'] ?? ''));
    }

    return [
        'ad_line1' => $desc_json['ad_line1'] ?? '',
        'ad_line2' => $desc_json['ad_line2'] ?? '',
        'specialist_id' => (int) $row['specialist_id'],
        'display_name' => $displayName,
    ];
}

// Возвращает актуальные записи для блога специалиста
function get_blog_posts(int $blog_id, int $limit = 5, int $start = 0)
{
    global $data_base;

    $limit = (int)$limit;
    $start = (int)$start;

    return $data_base->selectAll("
        SELECT ar.*,
               se.id AS service_id,
               se.title AS service_title,
               se.slug AS service_slug,
               ar.date
        FROM dt_post ar
        LEFT JOIN dt_blog bl ON ar.blog_id = bl.id
        LEFT JOIN dt_service se ON se.id = ar.theme_id
        LEFT JOIN dt_specialist s ON ar.author_id = s.id
        WHERE ar.moderation = 0
          AND ar.blog_id = ?
          AND s.deleted = 0
        ORDER BY ar.date DESC
        LIMIT $start, $limit
    ", [$blog_id]);
}

// Возвращает записи общего блога
function get_main_blog_articles(int $theme_id = 0, int $limit = 0, int $start = 0)
{
    global $data_base;

    $and = '';
    if ($theme_id != 0) {
        $and .= " AND ar.theme_id = $theme_id ";
        //$and .= " AND ar.id IN (SELECT article_id FROM dt_post_themes WHERE theme_id = $theme_id OR theme_id IN (SELECT id from dt_service WHERE p_id = $theme_id))";
    }
    return $data_base->selectAll("SELECT ar.*,
                                    bl.specialist_id,
                                    se.id as service_id,
                                    se.title as service_title,
                                    se.slug as service_slug
                                    FROM dt_post ar
                                    LEFT JOIN dt_blog bl ON ar.blog_id = bl.id
                                    LEFT JOIN dt_service se ON se.id = ar.theme_id
                                    WHERE moderation=0 $and
                                    ORDER BY date DESC LIMIT $start, $limit");
}

// Возвращает кол-во записей общего блога
function count_main_blog_articles()
{
    global $data_base;
    return $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_post");
}

// Возвращает записи блога юзера
function get_blog_articles(int $id, int $theme_id = 0, int $limit = 0, int $start = 0, $moderation = 0)
{
    global $data_base;

    $and = '';
    if ($theme_id != 0) {
        $and .= " AND ar.theme_id = $theme_id ";
    }

    // Обработка moderation как массива или числа
    if (is_array($moderation)) {
        $mod_values = implode(',', array_map('intval', $moderation));
        $and .= " AND ar.moderation IN ($mod_values) ";
    } elseif ($moderation === 0 || $moderation === 1 || $moderation === 2) {
        $and .= " AND ar.moderation = $moderation ";
    }

    return $data_base->selectAll("SELECT ar.*,
                                    bl.specialist_id,
                                    se.id as service_id,
                                    se.title as service_title,
                                    se.slug as service_slug
                                    FROM dt_post ar
                                    LEFT JOIN dt_blog bl ON ar.blog_id = bl.id
                                    LEFT JOIN dt_service se ON se.id = ar.theme_id
                                    WHERE ar.blog_id = $id $and
                                    ORDER BY date DESC LIMIT $start, $limit");
}

// Возвращает кол-во записей блога юзера
function count_blog_articles(int $id, $moderation = null)
{
    global $data_base;

    $and = '';
    
    // Обработка moderation: если передан массив или число
    if (is_array($moderation)) {
        $mod_values = implode(',', array_map('intval', $moderation));
        $and .= " AND moderation IN ($mod_values) ";
    } elseif ($moderation !== null) {
        $and .= " AND moderation = " . intval($moderation);
    }
    // Если $moderation === null, условие по статусу вообще не добавится (посчитает все)

    // Метод ->count() обычно сразу возвращает число, поэтому оставляем обертку вашего фреймворка
    return $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_post WHERE blog_id = $id $and");
}

// Возвращает кол-во просмотров всех записей блога юзера
function count_blog_views(int $id)
{
    global $data_base;
    return $data_base->selectAll("SELECT SUM(views) FROM dt_post WHERE blog_id = $id AND moderation = 0");
}

// Возвращает запись
function get_article(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT ar.*, 
                                bl.specialist_id,
                                se.id as service_id,
                                se.title as service_title,
                                    se.slug as service_slug,
                                    date
                                FROM dt_post ar 
                                LEFT JOIN dt_blog bl ON ar.blog_id = bl.id 
                                LEFT JOIN dt_service se ON se.id = ar.theme_id
                                WHERE ar.id = $id AND moderation = 0");
}
// Возвращает запись (даже если на модерации)
function get_article_cabinet(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT ar.*, 
                                bl.specialist_id,
                                se.id as service_id,
                                se.title as service_title,
                                    se.slug as service_slug,
                                    date
                                FROM dt_post ar 
                                LEFT JOIN dt_blog bl ON ar.blog_id = bl.id 
                                LEFT JOIN dt_service se ON se.id = ar.theme_id
                                WHERE ar.id = $id");
}
// Возвращает запись
function get_article_by_alias(string $alias)
{
    global $data_base;
    return $data_base->selectRow("SELECT ar.*, 
                                bl.specialist_id,
                                se.id as service_id,
                                se.title as service_title,
                                se.slug as service_slug,
                                date
                                FROM dt_post ar 
                                LEFT JOIN dt_blog bl ON ar.blog_id = bl.id 
                                LEFT JOIN dt_service se ON se.id = ar.theme_id
                                WHERE ar.alias = ? AND moderation = 0", [$alias]);
}

// Возвращает теги записи
function get_article_tags(int $id)
{
    global $data_base;
    $themes = $data_base->selectAll("SELECT * FROM dt_tags WHERE id IN (SELECT tag_id FROM dt_post_tags WHERE article_id = $id)");
    return $themes;
}

// Добавляет просмотр на запись блога
function add_article_views(int $id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_post SET views = views + 1 WHERE id = ?", [$id]);
}

// Создает запись
// Создает запись
function create_article(array $data)
{
    global $data_base;
    $alias = generateAlias($data['title']);
    
    $article_id = $data_base->add(
        "INSERT INTO dt_post (blog_id, author_id, theme_id, post_type, title, alias, content, meta_keywords, photo, moderation) VALUES (?, ?, ?, 'blogs', ?, ?, ?, ?, ?, 1)",
        [
            $data['blog_id'], 
            $data['author_id'], 
            $data['theme_id'], 
            $data['title'], 
            $alias, 
            $data['content'], 
            $data['keywords'], 
            $data['photo']
        ]
    );
    
    if (check_alias($alias)) {
        $new_alias = $alias . '-' . $article_id;
        $data_base->update("UPDATE dt_post SET alias = ? WHERE id = $article_id", [$new_alias]);
    }
    return $article_id;
}

// Обновляет запись
function update_article(int $article_id, array $data)
{
    global $data_base;
    $theme_id = intval($data['theme_id']);
    $alias = generateAlias($data['title']);
    
    if (check_alias($alias, $article_id)) {
        $alias = $alias . $article_id;
    }
    
    return $data_base->update(
        "UPDATE dt_post SET title = ?, alias = ?, theme_id = $theme_id, content = ?, meta_keywords = ?, photo = ?, moderation = 1 WHERE id = ?", 
        [
            $data['title'], 
            $alias, 
            $data['content'], 
            $data['keywords'], 
            $data['photo'],
            $article_id
        ]
    );
}

// Удаляет запись
function delete_article(int $article_id)
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_post` WHERE id = ?", [$article_id]);
}


// Устанавливает тему записи
function set_article_theme(int $article_id, int $theme_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_post_themes (article_id, theme_id) VALUES (?, ?)", [$article_id, $theme_id]);
}

// Удаляет тему записи
function unset_article_themes(int $article_id)
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_post_themes` WHERE article_id = ?", [$article_id]);
}

function get_schedule_lessons(int $specialist_id, string $from, string $to, DateTimeImmutable $now): array
{
    global $data_base;

    $rows = $data_base->selectAll(
        "SELECT l.id,
                DATE_FORMAT(l.lesson_date, '%Y-%m-%d') AS date,
                TIME_FORMAT(l.start_time, '%H:%i')     AS time,
                l.duration_min AS duration,
                CASE WHEN l.end_at <= ? THEN 'done' ELSE l.status END AS status,
                l.service_id, l.custom_title, l.employee_id, l.room_idx, l.room_name, l.level,
                s.title AS service_title
           FROM dt_schedule_lesson l
           LEFT JOIN dt_service s ON s.id = l.service_id
          WHERE l.specialist_id = ? AND l.lesson_date BETWEEN ? AND ? AND l.deleted_at IS NULL
          ORDER BY l.lesson_date, l.start_time",
        [$now->format('Y-m-d H:i:s'), $specialist_id, $from, $to]
    );
    $by_date = [];
    foreach ($rows as $r) {
        $by_date[$r['date']][] = $r;
    }
    return $by_date;
}

/** Строка БД в данные строки занятия */
function schedule_lesson_view(array $r, array $teachers): array
{
    return [
        'id'           => $r['id'],
        'time'         => $r['time'],
        'duration'     => $r['duration'],
        'status'       => $r['status'],
        'title'        => $r['custom_title'] ?: ($r['service_title'] ?? ''),
        'trainer'      => $teachers[$r['employee_id']] ?? '',
        'direction'    => $r['service_id'] ?: 'custom',
        'custom_title' => $r['custom_title'] ?? '',
        'teacher'      => $r['employee_id'],
        'hall'         => $r['room_idx'],
        'level'        => $r['level'],
    ];
}

/** HTML блока дня: список занятий или заглушка */
function render_schedule_day(string $ymd, array $lessons, bool $hidden = true): string
{
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $labels = ['done' => 'Проведено', 'closed' => 'Запись закрыта', 'open' => 'Запись идёт'];

    $rows = implode('', array_map(function ($lesson) use ($e, $labels) {
        // ← сюда перенесите тело вашего $render_lessons (одна строка занятия с меню),
        //    заменив $status_labels на $labels
    }, $lessons));

    return '<div class="schedule-day-content" data-date="' . $e($ymd) . '"' . ($hidden ? ' hidden' : '') . '>'
        . ($rows !== ''
            ? '<div class="schedule-lessons">' . $rows . '</div>'
            : '<div class="schedule-empty">Расписание на день не заполнено.</div>')
        . '</div>';
}

/** Одно занятие специалиста (null, если нет или чужое) */
function get_schedule_lesson(int $id, int $specialist_id)
{
    global $data_base;

    return $data_base->selectRow(
        "SELECT * FROM dt_schedule_lesson WHERE id = ? AND specialist_id = ? AND deleted_at IS NULL",
        [$id, $specialist_id]
    );
}

/** Занятие уже проведено? (время окончания прошло) */
function schedule_lesson_is_done(array $lesson, DateTimeImmutable $now): bool
{
    $end = new DateTimeImmutable($lesson['lesson_date'] . ' ' . $lesson['start_time'], $now->getTimezone());
    $end = $end->modify('+' . (int)$lesson['duration_min'] . ' minutes');
    return $end <= $now;
}

/** Все занятия специалиста за дату (включая прошедшие) */
function get_schedule_lessons_by_date(int $specialist_id, string $date): array
{
    global $data_base;

    return $data_base->selectAll( 
        "SELECT * FROM dt_schedule_lesson
         WHERE specialist_id = ? AND lesson_date = ? AND deleted_at IS NULL
         ORDER BY start_time",
        [$specialist_id, $date]
    ) ?: [];
}

/** Поля из проверенных данных попапа в колонки таблицы */
function schedule_lesson_fields(array $d): array
{
    return [
        'lesson_date'   => $d['date'],
        'start_time'    => $d['time'] . ':00',
        'duration_min'  => (int)$d['duration'],
        'status'        => $d['status'],
        'service_id'    => $d['service_id'],
        'custom_title'  => $d['custom_title'],
        'employee_id'   => $d['employee_id'],
        'room_idx'      => $d['room_idx'],
        'room_name'     => $d['room_name'],
        'level'         => (int)$d['level'],
    ];
}

function add_schedule_lesson(int $specialist_id, array $d)
{
    global $data_base;

    $f = schedule_lesson_fields($d);

    $ok = $data_base->update(
        "INSERT INTO dt_schedule_lesson
            (specialist_id, lesson_date, start_time, duration_min, status,
             service_id, custom_title, employee_id, room_idx, room_name, level)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $specialist_id, $f['lesson_date'], $f['start_time'], $f['duration_min'], $f['status'],
            $f['service_id'], $f['custom_title'], $f['employee_id'],
            $f['room_idx'], $f['room_name'], $f['level'],
        ]
    );
    if (!$ok) return false;

    // id только что вставленной записи
    $row = $data_base->selectRow("SELECT LAST_INSERT_ID() AS id");
    return $row ? (int)$row['id'] : false;
}

/** Изменить занятие (только своё и только не проведённое) */
function update_schedule_lesson(int $id, int $specialist_id, array $d, DateTimeImmutable $now): bool
{
    global $data_base;

    $lesson = get_schedule_lesson($id, $specialist_id);
    if (!$lesson || schedule_lesson_is_done($lesson, $now)) return false;

    $f = schedule_lesson_fields($d);

    return $data_base->update(
        "UPDATE dt_schedule_lesson
            SET lesson_date = ?, start_time = ?, duration_min = ?, status = ?,
                service_id = ?, custom_title = ?, employee_id = ?,
                room_idx = ?, room_name = ?, level = ?
          WHERE id = ? AND specialist_id = ? AND deleted_at IS NULL",
        [
            $f['lesson_date'], $f['start_time'], $f['duration_min'], $f['status'],
            $f['service_id'], $f['custom_title'], $f['employee_id'],
            $f['room_idx'], $f['room_name'], $f['level'],
            $id, $specialist_id,
        ]
    );
}

/** Удалить занятие (мягко; только своё и не проведённое) */
function delete_schedule_lesson(int $id, int $specialist_id, DateTimeImmutable $now): bool
{
    global $data_base;

    $lesson = get_schedule_lesson($id, $specialist_id);
    if (!$lesson || schedule_lesson_is_done($lesson, $now)) return false;

    return $data_base->update(
        "UPDATE dt_schedule_lesson SET deleted_at = ? WHERE id = ? AND specialist_id = ? AND deleted_at IS NULL",
        [$now->format('Y-m-d H:i:s'), $id, $specialist_id]
    );
}

/** Справочники расписания специалиста: направления, преподаватели, залы */
function get_schedule_dicts(array $specialist): array
{
    // направления: все конечные из общего меню сайта
    $menu = get_services_for_menu();
    $all = []; $parents = [];
    foreach ($menu as $by_parent) {
        foreach ($by_parent as $services) {
            foreach ($services as $s) {
                $all[$s['id']] = $s;
                if ((int)$s['p_id'] > 0) $parents[(int)$s['p_id']] = true;
            }
        }
    }
    $leaf = array_filter($all, fn($s) => !isset($parents[(int)$s['id']]));
    usort($leaf, fn($a, $b) => [$a['sort_order'], $a['title']] <=> [$b['sort_order'], $b['title']]);
    $directions = [];
    foreach ($leaf as $s) $directions[$s['id']] = $s['title'];

    // преподаватели: команда студии
    $company_id = (int)($specialist['company_id'] ?? 0);
    $teachers = [];
    foreach ($company_id ? get_company_employees($company_id) : [] as $emp) {
        $name = trim(($emp['name'] ?? '') . ' ' . ($emp['lastname'] ?? ''));
        if ($name !== '') $teachers[$emp['id']] = $name;
    }

    // залы студии
    $halls = [];
    foreach (json_decode($specialist['rooms'] ?? '[]', true) ?: [] as $i => $room) {
        $name = trim($room['name'] ?? '');
        if ($name !== '') $halls[$i] = $name;
    }

    return ['directions' => $directions, 'teachers' => $teachers, 'halls' => $halls];
}

/** Проверка данных занятия. Возвращает [очищенные данные, ошибки] */
function validate_lesson(array $in, array $dicts, DateTimeImmutable $now): array
{
    $err = []; $out = [];

    $out['status'] = in_array($in['status'] ?? '', ['open', 'closed'], true) ? $in['status'] : null;
    if (!$out['status']) $err['status'] = 'Выберите статус';

    $date = (string)($in['date'] ?? '');
    $date_ok = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date);
    if (!$date_ok) $err['date'] = 'Некорректная дата';
    $out['date'] = $date;

    $time = (string)($in['time'] ?? '');
    $time_ok = preg_match('/^([01]\d|2[0-3]):(00|15|30|45)$/', $time);
    if (!$time_ok) $err['time'] = 'Выберите начало занятия';
    $out['time'] = $time;

    $dur = (int)($in['duration'] ?? 0);
    $dur_ok = $dur >= 15 && $dur <= 180 && $dur % 15 === 0;
    if (!$dur_ok) $err['duration'] = 'Выберите длительность занятия';
    $out['duration'] = $dur;

    if (!$time_ok && !$dur_ok) { 
        $err['time'] = 'Выберите начало и длительность занятия';
        $err['duration'] = '';
    }
    // занятие должно заканчиваться в будущем, иначе оно сразу было бы «Проведено»
    if ($date_ok && $time_ok && $dur_ok) {
        $end = (new DateTimeImmutable("$date $time", $now->getTimezone()))->modify("+$dur minutes");
        if ($end <= $now) $err['time'] = 'Прошедшие занятия редактировать нельзя. Проверьте время начала и продолжительность занятия.';
    }

    // направление или своё название
    $dir = (string)($in['direction'] ?? '');
    if ($dir === 'custom') {
        $raw  = trim((string)($in['custom_title'] ?? ''));
        $norm = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // как в блоге: ловим &lt;script&gt;
    
        if ($raw === '') {
            $err['custom_title'] = 'Введите наименование';
        } elseif (mb_strlen($norm) > 30) {
            $err['custom_title'] = 'Превышено допустимое количество символов';
        } elseif (preg_match('/<[^>]+>/', $norm)) {
            $err['custom_title'] = 'Название не должно содержать HTML-теги';
        } elseif (preg_match('/https?:\/\/[^\s"\'<>]+/i', $norm)) {
            $err['custom_title'] = 'Название не должно содержать ссылки';
        } else {
            $low = mb_strtolower($norm, 'UTF-8');
            foreach (getForbiddenWords() as $word) {
                $w = trim((string)$word);
                if ($w === '' || in_array($w, ['<', '>', '&lt;', '&gt;'], true)) continue;
    
                $quoted = preg_quote(mb_strtolower($w, 'UTF-8'), '/');
                if (preg_match('/(?<!\p{L})' . $quoted . '(?!\p{L})/iu', $low)) {
                    $err['custom_title'] = 'Название содержит запрещенное слово: "' . htmlspecialchars($w) . '"';
                    break;
                }
            }
        }
    
        $out['service_id']   = null;
        $out['custom_title'] = $raw;
    } elseif (isset($dicts['directions'][$dir])) {
        $out['service_id'] = (int)$dir;
        $out['custom_title'] = null;
    } else {
        $err['direction'] = 'Выберите направление занятия';
    }

    $teacher = (string)($in['teacher'] ?? '');
    if (isset($dicts['teachers'][$teacher])) $out['employee_id'] = (int)$teacher;
    else $err['teacher'] = 'Выберите преподавателя';

    $hall = (string)($in['hall'] ?? '');
    if (isset($dicts['halls'][$hall])) { $out['room_idx'] = (int)$hall; $out['room_name'] = $dicts['halls'][$hall]; }
    else $err['hall'] = 'Выберите зал для занятий';

    $lvl = (int)($in['level'] ?? 0);
    if ($lvl < 1 || $lvl > 4) $err['level'] = 'Выберите уровень подготовки';
    $out['level'] = $lvl;

    return [$out, $err];
}

// Возвращает всех работников компании
function get_company_employees(int $company_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_company_employee WHERE company_id = ?", [$company_id]);
}

// Удаляет сотрудника компании
function unset_company_employee(int $employee_id, int $company_id)
{
    global $data_base;
    return $data_base->delete(
        "DELETE FROM `dt_company_employee` WHERE id = ? AND company_id = ?",
        [$employee_id, $company_id]
    );
}

// Обновляет сотрудника компании (id сохраняется)
function update_company_employee(int $employee_id, int $company_id, array $employee)
{
    global $data_base;
    return $data_base->update(
        "UPDATE dt_company_employee SET name = ?, lastname = ?, post = ?, photo = ? WHERE id = ? AND company_id = ?",
        [$employee['name'], $employee['lastname'], $employee['post'], $employee['file'], $employee_id, $company_id]
    );
}

// Удаляет сотрудника компании
function set_company_employee($employee, $company_id)
{
    global $data_base;
    return $data_base->add(
        "INSERT INTO dt_company_employee (company_id, name, lastname, post, photo) VALUE (?, ?, ?, ?, ?)",
        [(int) $company_id, $employee['name'], $employee['lastname'], $employee['post'], $employee['file']]
    );
}

function add_user_request(int $specialist_id, string $name, string $phone)
{
    global $data_base;

    return $data_base->add(
        "INSERT INTO dt_user_request (specialist_id, name, phone) 
         VALUES (?, ?, ?)",
        [
            $specialist_id,
            $name,
            $phone
        ]
    );
}

// Возвращает количество специалистов
function get_count_specialists()
{
    global $data_base;
    return $data_base->count("SELECT COUNT(*) FROM dt_specialist");
}

// Возвращает список топ специалистов
function get_top_specialists()
{
    global $data_base;
    $specialists = $data_base->selectAll("SELECT *, s.id as id, s.name as name,
                                                s.rating as rating,
                                                c.id as classification_id, c.title as classification_title, 
                                                ci.id as city_id, ci.name as city_name, 
                                                se.title as specialization, se.id as specialization_id
                                                FROM dt_specialist s
                                                LEFT JOIN dt_specialist_review sr ON s.id = sr.specialist_id
                                                LEFT JOIN dt_classification c ON s.classification_id = c.id
                                                LEFT JOIN dt_city ci ON s.city_id = ci.id
                                                LEFT JOIN dt_specialist_specialization ss ON s.id = ss.specialist_id
                                                LEFT JOIN dt_service se ON ss.service_id = se.id
                                                GROUP BY s.id, sr.specialist_id
                                                ORDER BY s.rating DESC
                                                LIMIT 8");


    return $specialists;
}

function get_specialist_metro_id(int $specialist_id)
{
    global $data_base;

    $row = $data_base->selectRow('SELECT metro FROM dt_specialist_contacts WHERE specialist_id = ?', [$specialist_id]);

    return $row ? $row['metro'] : null;
}

// Возвращает список специалистов по специализации
function get_specialists_by_classification($classification, int $limit, int $start = 0, int $metro_id = null)
{
    global $data_base;
    $uri_arr = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

    // Определяем фильтр: "private" или "company"
    $filter = end($uri_arr);
    $and = '';

    if ($filter === 'company') {
        $and = 'AND s.company_id IS NOT NULL'; // Только компании
    } elseif ($filter === 'private') {
        $and = 'AND s.company_id IS NULL'; // Только частные специалисты
    }

    if (is_int($classification)) {
        $where = '= ?';
        $array = [$classification];
    }

    if (is_array($classification)) {
        $where = 'IN (' . implode(',', $classification) . ')';
    }
    $array[] = $_SESSION['city']['id'];

    if ((is_array($classification) && in_array(4, $classification)) || $classification === 4) {
        if ($metro_id) {
            $and .= ' AND (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id) = ?';
            $array[] = $metro_id;
        }
    }

    $specialists = $data_base->selectAll("
        SELECT
            s.id,
            s.name,
            s.lastname,
            s.photo,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating, -- Средний рейтинг из отзывов
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ') FROM dt_service se WHERE se.id IN (SELECT service_id FROM dt_specialist_specialization WHERE specialist_id = s.id)) AS specialization, -- Все услуги в одну строку
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ') FROM dt_metro cm WHERE cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)) AS metro_name, -- Метро в одну строку
            co.title AS company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE s.classification_id $where AND s.deleted = 0 AND s.city_id = ? $and
        GROUP BY s.id
        ORDER BY rating DESC
        LIMIT $start, $limit
    ", $array);

    return $specialists;
}


function get_random_recomendations_by_classification($classification, int $limit, int $start = 0, int $metro_id = null)
{
    global $data_base;
    $uri_arr = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

    // Определяем фильтр: "private" или "company"
    $filter = end($uri_arr);
    $and = '';

    if ($filter === 'company') {
        $and = 'AND s.company_id IS NOT NULL'; // Только компании
    } elseif ($filter === 'private') {
        $and = 'AND s.company_id IS NULL'; // Только частные специалисты
    }

    // Инициализируем массив строго в том порядке, в каком плейсхолдеры идут в SQL
    $array = [];

    if (is_int($classification)) {
        $where = '= ?';
        $array[] = $classification; // Первый ? — это classification_id (если это число)
    }

    if (is_array($classification)) {
        // Если массив, мы вставляем значения прямо в IN, плейсхолдер тут не нужен
        $where = 'IN (' . implode(',', array_map('intval', $classification)) . ')';
    }

    // Второй ? — это всегда city_id
    $array[] = $_SESSION['city']['id'];

    // ТЕПЕРЬ РАБОТАЕТ ДЛЯ ВСЕХ: если передали metro_id, добавляем фильтр
    if ($metro_id) {
        $and .= ' AND (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id) = ?';
        $array[] = $metro_id; // Третий ? — это metro_id
    }

    $specialists = $data_base->selectAll("
        SELECT
            s.id,
            s.name,
            s.lastname,
            s.photo,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating,
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ') FROM dt_service se WHERE se.id IN (SELECT service_id FROM dt_specialist_specialization WHERE specialist_id = s.id)) AS specialization,
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ') FROM dt_metro cm WHERE cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)) AS metro_name,
            co.title AS company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE s.classification_id $where AND s.deleted = 0 AND s.city_id = ? $and
        GROUP BY s.id
        ORDER BY RAND()
        LIMIT $start, $limit
    ", $array);

    return $specialists;
}
function get_specialists_by_classification_n_block($classification, int $limit, int $start = 0)
{
    global $data_base;

    $and = '';
    if (isset($_GET['company']) && !isset($_GET['specialist'])) {
        $and = 'AND s.company_id IS NOT NULL';
    } elseif (isset($_GET['specialist']) && !isset($_GET['company'])) {
        $and = 'AND s.company_id IS NULL';
    }

    if (is_int($classification)) {
        $where = '= ?';
        $array = [$classification, $_SESSION['city']['id']];
    } elseif (is_array($classification)) {
        $where = 'IN (' . implode(',', array_map('intval', $classification)) . ')';
        $array = [$_SESSION['city']['id']]; // Только city_id как параметр
    }

    if (($classification == 4 || (is_array($classification) && in_array(4, $classification))) && !empty($_SESSION['metro'])) {
        $and .= ' AND cm.id IN (' . $_SESSION['metro'] . ')';
    }

    // Промо специалисты
    $promo_query = "
        SELECT
            s.id,
            s.name,
            s.slug,
            s.lastname,
            s.photo,
            s.gallery,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id),0) AS rating,
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ')
             FROM dt_service se
             WHERE se.id IN (SELECT service_id FROM dt_specialist_specialization WHERE specialist_id = s.id)
            ) AS specialization,
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ')
             FROM dt_metro cm
             WHERE cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
            ) AS metro_name,
            co.title AS company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count,
            1 AS has_block_specialist_positions,
            (SELECT MIN(sort)
             FROM dt_promo_list pl
             WHERE pl.specialist_id = s.id
               AND pl.service_slug = 'block_specialist_positions'
               AND CURRENT_DATE BETWEEN DATE(pl.date_start) AND DATE(pl.date_expires)
            ) AS sort
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE s.classification_id $where
          AND s.city_id = ?
          AND s.deleted = 0
          $and
          AND EXISTS (
              SELECT 1 FROM dt_promo_list pl
              WHERE pl.specialist_id = s.id
                AND pl.service_slug = 'block_specialist_positions'
                AND CURRENT_DATE BETWEEN DATE(pl.date_start) AND DATE(pl.date_expires)
          )
        GROUP BY s.id
        ORDER BY sort ASC, s.id ASC
        LIMIT $limit
    ";

    $promo = $data_base->selectAll($promo_query, $array);

    // Не-промо специалисты
    $non_promo_query = "
        SELECT
            s.id,
            s.name,
            s.slug,
            s.lastname,
            s.photo,
            s.gallery,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id),0) AS rating,
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ')
             FROM dt_service se
             WHERE se.id IN (SELECT service_id FROM dt_specialist_specialization WHERE specialist_id = s.id)
            ) AS specialization,
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ')
             FROM dt_metro cm
             WHERE cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
            ) AS metro_name,
            co.title AS company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count,
            0 AS has_block_specialist_positions,
            NULL AS sort
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE s.classification_id $where
          AND s.city_id = ?
          AND s.deleted = 0
          $and
          AND NOT EXISTS (
              SELECT 1 FROM dt_promo_list pl
              WHERE pl.specialist_id = s.id
                AND pl.service_slug = 'block_specialist_positions'
                AND CURRENT_DATE BETWEEN DATE(pl.date_start) AND DATE(pl.date_expires)
          )
        GROUP BY s.id
        ORDER BY RAND()
        LIMIT $limit
    ";

    $non_promo = $data_base->selectAll($non_promo_query, $array);

    // Объединяем и берём первые $limit элементов
    $all = array_merge($promo, $non_promo);
    return array_slice($all, 0, $limit);
}

function getCachedData(string $baseKey, int $ttl, callable $callback, ?string $version = null)
{
    $tmpDir = __DIR__ . '/tmp';
    if (!is_dir($tmpDir)) mkdir($tmpDir, 0775, true);

    $hash = md5($baseKey);
    $suffix = $version !== null ? '_' . md5((string)$version) : '';
    $cacheFile = $tmpDir . '/cache_' . $hash . $suffix . '.cache';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        $raw = file_get_contents($cacheFile);
        $data = @unserialize($raw);
        if ($raw === serialize(false) || $data !== false) {
            return $data;
        }
    }

    // убираем прошлые версии этого же базового ключа
    foreach (glob($tmpDir . '/cache_' . $hash . '_*.cache') as $oldFile) {
        if ($oldFile !== $cacheFile) {
            @unlink($oldFile);
        }
    }

    $data = $callback();

    $tmpFile = $cacheFile . '.' . uniqid('', true) . '.tmp';
    file_put_contents($tmpFile, serialize($data), LOCK_EX);
    rename($tmpFile, $cacheFile);

    return $data;
}

function clearCache(string $baseKey)
{
    $tmpDir = __DIR__ . '/tmp';
    $hash = md5($baseKey);
    // удаляем ВСЕ файлы этого базового ключа, независимо от версии в имени
    foreach (glob($tmpDir . '/cache_' . $hash . '*') as $file) {
        @unlink($file);
    }
}

function get_random_specialists_by_classification(array $classArr, int $limit, array $excludeIds = []): array
{
    global $data_base;

    // Формируем SQL-IN и параметры
    $inClasses    = implode(',', array_fill(0, count($classArr), '?'));
    $params       = array_merge($classArr, [$_SESSION['city']['id'], 1.6]);
    $excludeClause = '';

    if (!empty($excludeIds)) {
        $inExclude   = implode(',', array_fill(0, count($excludeIds), '?'));
        $excludeClause = "AND s.id NOT IN ($inExclude)";
        $params = array_merge($params, $excludeIds);
    }

    $sql = "
        SELECT
            s.id, s.name, s.lastname, s.photo,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id),0) AS rating,
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ')
             FROM dt_service se
             WHERE se.id IN (
               SELECT service_id FROM dt_specialist_specialization WHERE specialist_id = s.id
             )
            ) AS specialization,
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ')
             FROM dt_metro cm
             WHERE cm.id = (
               SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id
             )
            ) AS metro_name,
            co.title AS company_name
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE s.classification_id IN ($inClasses)
          AND s.city_id = ?
          AND s.deleted = 0
          AND IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id),0) >= ?
          $excludeClause
        ORDER BY RAND()
        LIMIT $limit
    ";

    return $data_base->selectAll($sql, $params);
}

function get_specialists_by_classification_with_tariffs(
    $classification,
    int $limit,
    int $start = 0,
    $filter = null,
    array $metro_ids = [],
    bool $ignore_metro = false,
    array $selected_amenities = [],
    array $selected_equipment = [],
    array $selected_price = []
) {
    global $data_base;

    // Определяем фильтр по компании/частному лицу
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $last_segment = basename($uri);
    if ($filter === null && in_array($last_segment, ['company', 'private'], true)) {
        $filter = $last_segment;
    }

    $andFilter = '';
    if ($filter === 'company') {
        $andFilter .= ' AND s.company_id IS NOT NULL';
    } elseif ($filter === 'private') {
        $andFilter .= ' AND s.company_id IS NULL';
    }

    // Подготовка параметров
    $params = [];
    // Клаузула для classification: может быть int, array или string
    if (is_int($classification) || ctype_digit((string)$classification)) {
        $whereClause = 's.classification_id = ?';
        $params[] = (int) $classification;
    } elseif (is_array($classification)) {
        $placeholders = implode(',', array_fill(0, count($classification), '?'));
        $whereClause = 's.classification_id IN (' . $placeholders . ')';
        foreach ($classification as $c) {
            $params[] = (int) $c;
        }
    } else {
        // Прочие — приводим к int
        $whereClause = 's.classification_id = ?';
        $params[] = (int) $classification;
    }

    // Фильтр по городу из сессии
    $whereClause .= ' AND s.city_id = ?';
    $params[] = $_SESSION['city']['id'];

    // Подготовка массива метро из сессии, если не передано через POST
    $session_metro = $_SESSION['metro'] ?? [];
    if (!is_array($session_metro)) {
        $session_metro = $session_metro !== '' ? explode(',', $session_metro) : [];
    }
    $session_metro = array_map('intval', $session_metro);

    // Фильтр по метро: POST имеет приоритет, затем сессия
    if (!$ignore_metro) {
        $effective_metro = !empty($metro_ids) ? $metro_ids : $session_metro;
        if (!empty($effective_metro)) {
            $placeholdersMetro = implode(',', array_fill(0, count($effective_metro), '?'));
            $andFilter .= ' AND cm.id IN (' . $placeholdersMetro . ')';
            foreach ($effective_metro as $m) {
                $params[] = (int) $m;
            }
        }
    }

    // Фильтр по amenities (удобства) — специалист должен иметь ХОТЯ БЫ ОДНО из выбранных
    if (!empty($selected_amenities)) {
        $amenity_ids_str = implode(',', array_map('intval', $selected_amenities));
        $andFilter .= " AND EXISTS (
            SELECT 1 FROM dt_specialist_equipment se_am
            WHERE se_am.specialist_id = s.id
              AND se_am.equipment_id IN ($amenity_ids_str)
        )";
    }

    // Фильтр по equipment (оборудование) — специалист должен иметь ХОТЯ БЫ ОДНО из выбранных
    if (!empty($selected_equipment)) {
        $equipment_ids_str = implode(',', array_map('intval', $selected_equipment));
        $andFilter .= " AND EXISTS (
            SELECT 1 FROM dt_specialist_equipment se_eq
            WHERE se_eq.specialist_id = s.id
              AND se_eq.equipment_id IN ($equipment_ids_str)
        )";
    }

    // Фильтр по price (диапазоны)
    if (!empty($selected_price)) {
        $price_clauses = [];
        $price_expr = "(SELECT MIN(price) FROM dt_specialist_service WHERE specialist_id = s.id)";

        foreach ($selected_price as $range) {
            if ($range === 'under_1000') {
                $price_clauses[] = "($price_expr <= 1000)";
            } elseif ($range === '1000_5000') {
                $price_clauses[] = "($price_expr >= 1000 AND $price_expr <= 5000)";
            } elseif ($range === 'over_5000') {
                $price_clauses[] = "($price_expr >= 5000)";
            }
        }
        if (!empty($price_clauses)) {
            $andFilter .= ' AND (' . implode(' OR ', $price_clauses) . ')';
        }
    }

    // Основной SQL-запрос
    $sql = "
SELECT *
FROM (
    SELECT
        s.id,
        s.name,
        s.lastname,
        s.slug,
        s.photo,
        s.gallery,
        IFNULL(r.rating, 0) AS rating,
        c.title AS classification_title,
        ci.name AS city_name,
        se.title AS specialization,
        cm.name AS metro_name,
        co.title AS company_name,
        IFNULL(ss.specialization_count, 0) AS specialization_count,
        IFNULL(tl.max_tariff, 0) AS max_tariff,
        CASE WHEN ol.specialist_id IS NOT NULL THEN 1 ELSE 0 END AS has_option,
        CASE WHEN pl.specialist_id IS NOT NULL THEN 1 ELSE 0 END AS has_promo,
        pl.promo_sort,
        pl.promo_record_id,
        (SELECT COUNT(*) FROM dt_post WHERE author_id = s.id) AS blog_posts_count,
        LEAST(
            2.0 * IFNULL(r.rating, 0)
          + 0.1 * (
                SELECT COUNT(*)
                FROM dt_post
                WHERE author_id = s.id
                  AND moderation = 0
            ),
            99
        ) AS computed_rating
    FROM dt_specialist s
    LEFT JOIN dt_classification c ON s.classification_id = c.id
    LEFT JOIN dt_city ci ON s.city_id = ci.id
    LEFT JOIN (
        SELECT specialist_id, AVG(rating) AS rating
        FROM dt_specialist_review
        GROUP BY specialist_id
    ) AS r ON s.id = r.specialist_id
    LEFT JOIN (
        SELECT specialist_id, COUNT(*) AS specialization_count, MAX(service_id) AS max_service_id
        FROM dt_specialist_specialization
        GROUP BY specialist_id
    ) AS ss ON s.id = ss.specialist_id
    LEFT JOIN dt_service se ON ss.max_service_id = se.id
    LEFT JOIN (
        SELECT specialist_id, metro
        FROM dt_specialist_contacts
    ) AS sc ON s.id = sc.specialist_id
    LEFT JOIN dt_metro cm ON sc.metro = cm.id
    LEFT JOIN dt_company co ON s.company_id = co.id
    LEFT JOIN (
        SELECT specialist_id, MAX(tariff_id) AS max_tariff
        FROM dt_tariff_list
        WHERE tariff_id IN (1, 2, 3)
          AND moderation = 0
        GROUP BY specialist_id
    ) AS tl ON s.id = tl.specialist_id
    LEFT JOIN (
        SELECT DISTINCT specialist_id
        FROM dt_options_list
        WHERE moderation = 0
    ) AS ol ON s.id = ol.specialist_id
    LEFT JOIN (
        SELECT 
            specialist_id, 
            MIN(sort) AS promo_sort, 
            MAX(id) AS promo_record_id
        FROM dt_promo_list
        WHERE (service_slug IS NULL OR service_slug = '')
          AND block_id IN (1,2,3,4,5)
          AND NOW() >= date_start
          AND DATE(date_expires) >= CURRENT_DATE
          AND moderation = 0
        GROUP BY specialist_id
    ) AS pl ON s.id = pl.specialist_id
    WHERE {$whereClause}{$andFilter} AND s.deleted = 0
    GROUP BY s.id
) AS tmp
ORDER BY
    CASE
        WHEN tmp.promo_sort = 1 THEN 0
        WHEN tmp.promo_sort = 2 THEN 1
        ELSE 2
    END,
    tmp.promo_record_id DESC,
    max_tariff DESC,
    tmp.computed_rating DESC,
    tmp.id ASC
LIMIT {$start}, {$limit};
";


    return $data_base->selectAll($sql, $params);
}

function get_specialists_coordinates_by_ids(array $ids): array
{
    global $data_base;

    if (empty($ids)) return [];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    return $data_base->selectAll("
        SELECT
            s.id,
            s.name,
            s.lastname,
            s.slug,
            c.title AS classification,
            co.title AS company,
            sc.coordinates,
            sc.street,
            sc.house,
            m.name AS metro,
            city.name AS city,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating,
            (SELECT COUNT(*) FROM dt_specialist_review WHERE specialist_id = s.id) AS count_rating
        FROM dt_specialist s
        LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        LEFT JOIN dt_metro m ON sc.metro = m.id
        LEFT JOIN dt_city city ON s.city_id = city.id
        WHERE s.id IN ($placeholders)
          AND s.deleted = 0
          AND sc.coordinates IS NOT NULL
          AND sc.coordinates != ''
    ", $ids);
}



/**
 * Подгружает список специализаций для специалистов.
 * Используется для вывода случайной специализации без изменения исходного поля specialization.
 *
 * @param array $specialists Массив специалистов
 * @return array Массив специалистов с полем specialization_list
 */
function loadSpecializationList(array $specialists): array
{
    global $data_base;

    foreach ($specialists as &$spec) {
        $spec['specialization_list'] = $data_base->selectAll(
            "SELECT s.title
             FROM dt_specialist_specialization ss
             JOIN dt_service s ON ss.service_id = s.id
             WHERE ss.specialist_id = ?",
            [$spec['id']]
        );
    }

    return $specialists;
}

// Возвращает результат поиска по специалистам (для окна поиска)
function search_specialists(string $search)
{
    global $data_base;
    $search = explode(' ', $search, 2);
    if (!empty($search[0]) && !empty($search[1])) {
        if (mb_strlen($search[0]) < 4 && mb_strlen($search[1]) < 4) {
            $condition = '=';
            $search_name = $search[0];
            $search_lastname = $search[1];
        } else {
            $condition = 'LIKE';
            $search_name = '%' . $search[0] . '%';
            $search_lastname = '%' . $search[1] . '%';
        }
        $specialists = $data_base->selectAll("SELECT * FROM dt_specialist WHERE (name $condition ? AND deleted = 0 AND lastname $condition ?) OR (name $condition ? AND deleted = 0 AND lastname $condition ?)", [$search_name, $search_lastname, $search_lastname, $search_name]);
    } else if (!empty($search[0]) && empty($search[1])) {
        if (mb_strlen($search[0]) < 4) {
            $condition = '=';
            $search_name = $search[0];
        } else {
            $condition = 'LIKE';
            $search_name = '%' . $search[0] . '%';
        }
        $specialists = $data_base->selectAll("SELECT * FROM dt_specialist WHERE (name $condition ? OR lastname $condition ?) AND deleted = 0", [$search_name, $search_name]);
    } else
        return false;
    return $specialists;
}

// Возвращает результат поиска по специалистам (для страницы поиска) 
function full_search_specialists(string $search, int $start = 0, int $limit = 12)
{
    global $data_base;
    $and = '';
    if (isset($_GET['filter'])) {
        $filter_req = htmlentities(strip_tags($_GET['filter']));
        if ($filter_req == 'lawyers') {
            $and = 'AND s.classification_id IN (1, 2)';
        } else if ($filter_req == 'experts') {
            $and = 'AND s.classification_id = 3';
        } else if ($filter_req == 'notaries') {
            $and = 'AND s.classification_id = 4';
        }
    }

    $search = explode(' ', $search, 2);
    if (!empty($search[0]) && !empty($search[1])) {
        $condition = (mb_strlen($search[0]) < 4 && mb_strlen($search[1]) < 4) ? '=' : 'LIKE';
        $search_name = ($condition === '=') ? $search[0] : '%' . $search[0] . '%';
        $search_lastname = ($condition === '=') ? $search[1] : '%' . $search[1] . '%';

        $specialists = $data_base->selectAll(
            "SELECT
                                                s.id,
                                                s.name,
                                                s.lastname,
                                                s.photo,
                                                IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating, -- Средний рейтинг
                                                c.title AS classification_title,
                                                s.classification_id,
                                                ci.name AS city_name,
                                                se.title AS specialization,
                                                cm.name AS metro_name,
                                                (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count,
                                                EXISTS (
                                                    SELECT 1 
                                                    FROM dt_tariff_list 
                                                    WHERE specialist_id = s.id AND tariff_id IN (2, 3)
                                                ) AS has_tariff,
                                                EXISTS (
                                                    SELECT 1 
                                                    FROM dt_options_list 
                                                    WHERE specialist_id = s.id AND option_id = 5
                                                ) AS has_option
                                            FROM dt_specialist s
                                            LEFT JOIN dt_classification c ON s.classification_id = c.id
                                            LEFT JOIN dt_city ci ON s.city_id = ci.id
                                            LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                            LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                            WHERE (s.name $condition ? AND s.deleted = 0 AND s.lastname $condition ? AND s.city_id = ? AND s.company_id IS NULL $and) 
                                                OR (s.name $condition ? AND s.deleted = 0 AND s.lastname $condition ? AND s.city_id = ? AND s.company_id IS NULL $and)
                                            ORDER BY has_tariff DESC, has_option DESC, rating DESC
                                            LIMIT $start, $limit",
            [$search_name, $search_lastname, $_SESSION['city']['id'], $search_lastname, $search_name, $_SESSION['city']['id']]
        );
    } else if (!empty($search[0]) && empty($search[1])) {
        $condition = (mb_strlen($search[0]) < 4) ? '=' : 'LIKE';
        $search_name = ($condition === '=') ? $search[0] : '%' . $search[0] . '%';

        $specialists = $data_base->selectAll(
            "SELECT
                                                s.id,
                                                s.name,
                                                s.lastname,
                                                s.photo,
                                                IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating, -- Средний рейтинг
                                                c.title AS classification_title,
                                                s.classification_id,
                                                ci.name AS city_name,
                                                se.title AS specialization,
                                                cm.name AS metro_name,
                                                (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count,
                                                EXISTS (
                                                    SELECT 1 
                                                    FROM dt_tariff_list 
                                                    WHERE specialist_id = s.id AND tariff_id IN (2, 3)
                                                ) AS has_tariff,
                                                EXISTS (
                                                    SELECT 1 
                                                    FROM dt_options_list 
                                                    WHERE specialist_id = s.id AND option_id = 5
                                                ) AS has_option
                                            FROM dt_specialist s
                                            LEFT JOIN dt_classification c ON s.classification_id = c.id
                                            LEFT JOIN dt_city ci ON s.city_id = ci.id
                                            LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                            LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                            WHERE (s.name $condition ? OR s.lastname $condition ?) 
                                                AND s.deleted = 0 AND s.city_id = ? $and
                                            ORDER BY has_tariff DESC, has_option DESC, rating DESC
                                            LIMIT $start, $limit",
            [$search_name, $search_name, $_SESSION['city']['id']]
        );
    } else
        return false;

    return $specialists;
}

// Возвращает результат поиска по компаниям (для окна поиска) 
function search_companies(string $search)
{
    global $data_base;
    if (mb_strlen($search) < 4) {
        $condition = '=';
    } else {
        $condition = 'LIKE';
        $search = '%' . $search . '%';
    }
    $companies = $data_base->selectAll("SELECT com.*,
                                        s.id as id_link,
                                        s.slug as slug_link
                                        FROM dt_company com
                                        LEFT JOIN dt_specialist s ON s.company_id = com.id
                                        WHERE com.title $condition ? AND s.deleted = 0", [$search]);
    return $companies;
}

// Возвращает результат поиска по компаниям (для страницы поиска)  
function full_search_companies(string $search, int $start = 0, int $limit = 12)
{
    global $data_base;
    if (mb_strlen($search) < 4) {
        $condition = '=';
    } else {
        $condition = 'LIKE';
        $search = '%' . $search . '%';
    }
    $and = '';
    if (isset($_GET['filter'])) {
        $filter_req = htmlentities(strip_tags($_GET['filter']));
        if ($filter_req == 'lawyers') {
            $and = 'AND co.classification IN (5, 6)';
        } else if ($filter_req == 'experts') {
            $and = 'AND co.classification = 7';
        } else if ($filter_req == 'notaries') {
            $and = 'AND co.classification = 8';
        }
    }

    $companies = $data_base->selectAll("
    SELECT 
        s.id,
        co.title AS company_name,
        s.photo,
        IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating,  -- Средний рейтинг
        co.classification AS classification_id,
        c.title AS classification_title,
        ci.name AS city_name,
        -- Одна специализация компании
        (SELECT se.title 
         FROM dt_service se 
         WHERE se.id = (
             SELECT MAX(ss.service_id) 
             FROM dt_specialist_specialization ss
             WHERE ss.specialist_id = s.id
         )
        ) AS specialization,
        -- Количество специализаций компании
        (SELECT COUNT(ss.service_id) 
         FROM dt_specialist_specialization ss
         WHERE ss.specialist_id = s.id
        ) AS specialization_count,
        cm.name AS metro_name,
        -- Проверка на наличие тарифов с ID 2 или 3
        EXISTS (
            SELECT 1
            FROM dt_tariff_list t
            WHERE t.specialist_id = s.id AND t.tariff_id IN (2, 3)
        ) AS has_tariff,
        -- Проверка на наличие опции с ID 5
        EXISTS (
            SELECT 1
            FROM dt_options_list o
            WHERE o.specialist_id = s.id AND o.option_id = 5
        ) AS has_option
    FROM dt_company co
    LEFT JOIN dt_specialist s ON s.company_id = co.id
    LEFT JOIN dt_classification c ON s.classification_id = c.id
    LEFT JOIN dt_city ci ON s.city_id = ci.id
    LEFT JOIN dt_metro cm ON cm.id = (
        SELECT metro 
        FROM dt_specialist_contacts 
        WHERE specialist_id = s.id
    )
    WHERE co.title $condition ? AND s.deleted = 0 AND s.city_id = ? $and
    ORDER BY has_tariff DESC, has_option DESC, rating DESC -- Сортировка: тариф 3 -> тариф 2 -> наличие опции -> рейтинг
    LIMIT $start, $limit
", [$search, $_SESSION['city']['id']]);


    return $companies;
}

function count_full_search_companies(string $search): int
{
    global $data_base;

    if (mb_strlen($search) < 4) {
        $condition = '=';
    } else {
        $condition = 'LIKE';
        $search = '%' . $search . '%';
    }

    $and = '';
    if (isset($_GET['filter'])) {
        $filter_req = htmlentities(strip_tags($_GET['filter']));
        if ($filter_req == 'lawyers') {
            $and = 'AND co.classification IN (5, 6)';
        } else if ($filter_req == 'experts') {
            $and = 'AND co.classification = 7';
        } else if ($filter_req == 'notaries') {
            $and = 'AND co.classification = 8';
        }
    }

    // Учитываем город — как в full_search_companies: компании считаются только если у них есть специалисты в нужном городе
    $count = $data_base->count("
        SELECT COUNT(DISTINCT co.id)
        FROM dt_company co
        LEFT JOIN dt_specialist s ON s.company_id = co.id
        WHERE co.title $condition ? AND s.deleted = 0 AND s.city_id = ? $and
    ", [$search, $_SESSION['city']['id']]);

    return intval($count);
}


function count_full_search_specialists(string $search): int
{
    global $data_base;

    $and = '';
    if (isset($_GET['filter'])) {
        $filter_req = htmlentities(strip_tags($_GET['filter']));
        // Используем те же id классификаций, что и в full_search_specialists
        if ($filter_req == 'lawyers') {
            $and = 'AND s.classification_id IN (1, 2)';
        } else if ($filter_req == 'experts') {
            $and = 'AND s.classification_id = 3';
        } else if ($filter_req == 'notaries') {
            $and = 'AND s.classification_id = 4';
        }
    }

    // Повторяем ту же логику разбиения запроса, что и в full_search_specialists
    $parts = explode(' ', $search, 2);

    if (!empty($parts[0]) && !empty($parts[1])) {
        $condition = (mb_strlen($parts[0]) < 4 && mb_strlen($parts[1]) < 4) ? '=' : 'LIKE';
        $search_name = ($condition === '=') ? $parts[0] : '%' . $parts[0] . '%';
        $search_lastname = ($condition === '=') ? $parts[1] : '%' . $parts[1] . '%';

        $count = $data_base->count("
            SELECT COUNT(DISTINCT s.id)
            FROM dt_specialist s
            WHERE (
                (s.name $condition ? AND s.lastname $condition ? AND s.city_id = ? AND s.company_id IS NULL $and)
                OR
                (s.name $condition ? AND s.lastname $condition ? AND s.city_id = ? AND s.company_id IS NULL $and)
            )
        ", [$search_name, $search_lastname, $_SESSION['city']['id'], $search_lastname, $search_name, $_SESSION['city']['id']]);
    } else {
        $condition = (mb_strlen($parts[0]) < 4) ? '=' : 'LIKE';
        $search_pattern = ($condition === '=') ? $parts[0] : '%' . $parts[0] . '%';

        $count = $data_base->count("
            SELECT COUNT(DISTINCT s.id)
            FROM dt_specialist s
            WHERE (s.name $condition ? OR s.lastname $condition ?)
              AND s.city_id = ?
              AND s.deleted = 0
              AND s.company_id IS NULL
              $and
        ", [$search_pattern, $search_pattern, $_SESSION['city']['id']]);
    }

    return intval($count);
}


// Возвращает кол-во результатов поиска
function count_full_search(string $search)
{
    global $data_base;
    if (!empty($search)) {

        if (mb_strlen($search) < 4) {
            $condition1 = '=';
            $search1 = $search;
        } else {
            $condition1 = 'LIKE';
            $search1 = '%' . $search . '%';
        }
        $and1 = $and2 = '';
        if (isset($_GET['filter'])) {
            $filter_req = htmlentities(strip_tags($_GET['filter']));
            if ($filter_req == 'lawyers') {
                $and1 = 'AND co.classification IN (5, 6)';
                $and2 = 'AND classification_id IN (1, 2)';
            } else if ($filter_req == 'experts') {
                $and1 = 'AND co.classification = 7';
                $and2 = 'AND classification_id = 3';
            } else if ($filter_req == 'notaries') {
                $and1 = 'AND co.classification = 8';
                $and2 = 'AND classification_id = 4';
            }
        }
        $companies = $data_base->count("SELECT COUNT(DISTINCT s.id)
                                            FROM dt_company co
                                            LEFT JOIN dt_specialist s ON s.company_id = co.id
                                            WHERE co.title $condition1 ? $and1", [$search1]);
        $search = explode(' ', $search, 2);
        if (!empty($search[0]) && !empty($search[1])) {
            if (mb_strlen($search[0]) < 4 && mb_strlen($search[1]) < 4) {
                $condition2 = '=';
                $search_name = $search[0];
                $search_lastname = $search[1];
            } else {
                $condition2 = 'LIKE';
                $search_name = '%' . $search[0] . '%';
                $search_lastname = '%' . $search[1] . '%';
            }
            $specialists = $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_specialist WHERE (name $condition2 ? AND lastname $condition2 ? AND company_id IS NULL $and2) OR (name $condition2 ? AND lastname $condition2 ? AND company_id IS NULL $and2)", [$search_name, $search_lastname, $search_lastname, $search_name]);
        } else if (!empty($search[0]) && empty($search[1])) {
            if (mb_strlen($search[0]) < 4) {
                $condition2 = '=';
                $search_name = $search[0];
            } else {
                $condition2 = 'LIKE';
                $search_name = '%' . $search[0] . '%';
            }
            $specialists = $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_specialist WHERE (name $condition2 ? AND company_id IS NULL $and2) OR ( lastname $condition2 ? AND company_id IS NULL $and2)", [$search_name, $search_name]);
        }
    } else
        return 0;
    return $companies + $specialists;
}

// Возвращает кол-во специалистов в специализации
function count_specialists_by_classification($classification)
{
    global $data_base;

    // Определяем фильтр из URI (company/private)
    $filter = null;
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_arr = explode('/', trim($uri, '/'));
    $last_segment = end($uri_arr);
    if (in_array($last_segment, ['company', 'private'], true)) {
        $filter = $last_segment;
    }

    $andFilter = '';
    if ($filter === 'company') {
        $andFilter .= ' AND s.company_id IS NOT NULL';
    } elseif ($filter === 'private') {
        $andFilter .= ' AND s.company_id IS NULL';
    }

    // Подготовка условий классификации
    $params = [];
    if (is_int($classification)) {
        $whereClause = 's.classification_id = ?';
        $params[] = $classification;
    } else {
        $placeholders = implode(',', array_fill(0, count($classification), '?'));
        $whereClause = "s.classification_id IN ({$placeholders})";
        foreach ($classification as $c) {
            $params[] = $c;
        }
    }

    // Фильтр по городу
    $whereClause .= ' AND s.city_id = ?';
    $params[] = $_SESSION['city']['id'];

    // Метро-фильтр (когда classification содержит 4)
    if (
        is_array($classification)
        && in_array(4, $classification, true)
        && !empty($_SESSION['metro'])
    ) {
        // Поддержка как массива, так и строки с ID через запятую
        $metroIds = is_array($_SESSION['metro'])
            ? implode(',', array_map('intval', $_SESSION['metro']))
            : $_SESSION['metro'];
        $andFilter .= " AND cm.id IN ({$metroIds})";
    }

    // SQL для подсчёта
    $sql = "
        SELECT COUNT(DISTINCT s.id) AS total
        FROM dt_specialist s
        LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
        LEFT JOIN dt_metro cm ON sc.metro = cm.id
        WHERE {$whereClause}{$andFilter} AND s.deleted = 0
    ";

    return $data_base->count($sql, $params);
}

// Возвращает количество специалистов в категории
function count_specialists_by_service(array $classification, string $services_id)
{
    global $data_base;

    // Определяем фильтр из URI (company/private)
    $filter = null;
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_arr = explode('/', trim($uri, '/'));
    $last_segment = end($uri_arr);
    if (in_array($last_segment, ['company', 'private'], true)) {
        $filter = $last_segment;
    }

    $andFilter = '';
    if ($filter === 'company') {
        $andFilter .= ' AND s.company_id IS NOT NULL';
    } elseif ($filter === 'private') {
        $andFilter .= ' AND s.company_id IS NULL';
    }

    // Handle classification (always an array)
    $params = [];
    if (empty($classification)) {
        $whereClause = 's.classification_id = ?';
        $params[] = -1; // Fallback for empty array to return no results
    } else {
        $placeholders = implode(',', array_fill(0, count($classification), '?'));
        $whereClause = "s.classification_id IN ({$placeholders})";
        foreach ($classification as $c) {
            $params[] = intval($c); // Ensure each value is an integer
        }
    }

    // Add city_id to params
    $params[] = $_SESSION['city']['id'];

    // Add metro filter if classification includes ID 4 and metro session exists
    if (in_array(4, $classification, true) && !empty($_SESSION['metro'])) {
        $metroIds = is_array($_SESSION['metro'])
            ? implode(',', array_map('intval', $_SESSION['metro']))
            : $_SESSION['metro'];
        $andFilter .= " AND cm.id IN ({$metroIds})";
    }

    // SQL для подсчёта по услугам
    $sql = "
        SELECT COUNT(DISTINCT s.id) AS total
        FROM dt_specialist s
        LEFT JOIN dt_specialist_specialization ss ON s.id = ss.specialist_id
        LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
        LEFT JOIN dt_metro cm ON sc.metro = cm.id
        WHERE ss.service_id IN ({$services_id})
          AND {$whereClause}
          AND s.city_id = ?
          AND s.deleted = 0
          {$andFilter}
    ";

    return $data_base->count($sql, $params);
}

// Возвращает специалистов из категории
function get_specialists_by_service(string $services, int $limit, int $start = 0)
{
    global $data_base;
    $uri_arr = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

    // Определяем фильтр: "private" или "company"
    $filter = end($uri_arr);
    $and = '';

    if ($filter === 'company') {
        $and = 'AND s.company_id IS NOT NULL'; // Только компании
    } elseif ($filter === 'private') {
        $and = 'AND s.company_id IS NULL'; // Только частные специалисты
    }

    if (!$services)
        $services = '-1';

    $specialists = $data_base->selectAll("
        SELECT
            s.id,
            s.name,
            s.lastname,
            s.photo,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating, -- Средний рейтинг из отзывов
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ') FROM dt_service se WHERE se.id IN ($services)) AS specialization, -- Все услуги в одну строку
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ') FROM dt_metro cm WHERE cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)) AS metro_name, -- Метро в одну строку
            co.title AS company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE EXISTS (
            SELECT 1
            FROM dt_specialist_specialization
            WHERE specialist_id = s.id AND service_id IN ($services)
        )
          AND s.city_id = ? $and
          AND s.deleted = 0
        GROUP BY s.id
        ORDER BY rating DESC
        LIMIT $start, $limit
    ", [$_SESSION['city']['id']]);

    return $specialists;
}

// Возвращает случайных специалистов из категории
function get_random_specialists_by_service(string $services, int $limit, int $start = 0)
{
    global $data_base;
    $uri_arr = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

    // Определяем фильтр: "private" или "company"
    $filter = end($uri_arr);
    $and = '';

    if ($filter === 'company') {
        $and = 'AND s.company_id IS NOT NULL'; // Только компании
    } elseif ($filter === 'private') {
        $and = 'AND s.company_id IS NULL'; // Только частные специалисты
    }

    if (!$services)
        $services = '-1';

    $specialists = $data_base->selectAll("
        SELECT
            s.id,
            s.name,
            s.lastname,
            s.photo,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating, -- Средний рейтинг из отзывов
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ') FROM dt_service se WHERE se.id IN ($services)) AS specialization, -- Все услуги в одну строку
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ') FROM dt_metro cm WHERE cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)) AS metro_name, -- Метро в одну строку
            co.title AS company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE EXISTS (
            SELECT 1
            FROM dt_specialist_specialization
            WHERE specialist_id = s.id AND service_id IN ($services)
        )
          AND s.city_id = ? $and
          AND s.deleted = 0
        GROUP BY s.id
        ORDER BY RAND()
        LIMIT $start, $limit
    ", [$_SESSION['city']['id']]);

    return $specialists;
}

function get_specialists_by_service_with_tariffs(
    array $classification,
    string $services,
    int $limit,
    int $start = 0,
    $filter = null,
    bool $ignore_metro = false,
    array $selected_amenities = [],
    array $selected_equipment = [],
    array $selected_price = []
) {
    global $data_base;

    // === Original code for obtaining service_ids_str ===
    $service_data = $data_base->selectRow("SELECT original_id FROM dt_service WHERE id = ?", [$services]);
    $original_id = ($service_data && intval($service_data['original_id']) > 0)
        ? intval($service_data['original_id'])
        : intval($services);

    $service_ids_result = $data_base->selectAll(
        "SELECT id FROM dt_service WHERE original_id = ? OR id = ?",
        [$original_id, $original_id]
    );

    $service_ids = (!$service_ids_result || !is_array($service_ids_result))
        ? [-1]
        : array_column($service_ids_result, 'id');

    $service_ids_str = implode(',', $service_ids);

    // Если фильтр не пришёл из POST — берём его из URL
    if ($filter === null) {
        $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri_arr = explode('/', trim($uri_path, '/'));
        $last = end($uri_arr);
        if (in_array($last, ['company', 'private'], true)) {
            $filter = $last;
        }
    }

    // Строим условие по фильтру
    $andFilter = '';
    if ($filter === 'company') {
        $andFilter .= ' AND s.company_id IS NOT NULL';
    } elseif ($filter === 'private') {
        $andFilter .= ' AND s.company_id IS NULL';
    }

    // Handle classification (always an array)
    $params = [];
    if (empty($classification)) {
        $whereClause = 's.classification_id = ?';
        $params[] = -1; // Fallback for empty array to return no results
    } else {
        $placeholders = implode(',', array_fill(0, count($classification), '?'));
        $whereClause = 's.classification_id IN (' . $placeholders . ')';
        foreach ($classification as $c) {
            $params[] = intval($c); // Ensure each value is an integer
        }
    }

    // Add city_id to params
    $params[] = $_SESSION['city']['id'];

    // Add metro filter if classification includes ID 4 and metro session exists
    if (!$ignore_metro && in_array(4, $classification, true) && !empty($_SESSION['metro'])) {
        $metroIds = is_array($_SESSION['metro'])
            ? implode(',', array_map('intval', $_SESSION['metro']))
            : $_SESSION['metro'];
        $andFilter .= ' AND cm.id IN (' . $metroIds . ')';
    }

    // Фильтр по amenities (удобства) — специалист должен иметь ХОТЯ БЫ ОДНО из выбранных
    if (!empty($selected_amenities)) {
        $amenity_ids_str = implode(',', array_map('intval', $selected_amenities));
        $andFilter .= " AND EXISTS (
            SELECT 1 FROM dt_specialist_equipment se_am
            WHERE se_am.specialist_id = s.id
              AND se_am.equipment_id IN ($amenity_ids_str)
        )";
    }

    // Фильтр по equipment (оборудование) — специалист должен иметь ХОТЯ БЫ ОДНО из выбранных
    if (!empty($selected_equipment)) {
        $equipment_ids_str = implode(',', array_map('intval', $selected_equipment));
        $andFilter .= " AND EXISTS (
            SELECT 1 FROM dt_specialist_equipment se_eq
            WHERE se_eq.specialist_id = s.id
              AND se_eq.equipment_id IN ($equipment_ids_str)
        )";
    }

    // Фильтр по price (диапазоны — как в старом проекте)
    if (!empty($selected_price)) {
        $price_clauses = [];
        $price_expr = "(SELECT MIN(price) FROM dt_specialist_service WHERE specialist_id = s.id)";

        foreach ($selected_price as $range) {
            if ($range === 'under_1000') {
                $price_clauses[] = "($price_expr <= 1000)";
            } elseif ($range === '1000_5000') {
                $price_clauses[] = "($price_expr >= 1000 AND $price_expr <= 5000)";
            } elseif ($range === 'over_5000') {
                $price_clauses[] = "($price_expr >= 5000)";
            }
        }
        if (!empty($price_clauses)) {
            $andFilter .= ' AND (' . implode(' OR ', $price_clauses) . ')';
        }
    }

    $sql = "
    SELECT
        s.id,
        s.name,
        s.lastname,
        s.slug,
        s.photo,
        s.gallery,
        IFNULL(
           (SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id),
           0
        ) AS rating,
        LEAST(
            2.0 * IFNULL(
                (SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id),
                0
            )
            + 0.1 * (SELECT COUNT(*) FROM dt_post WHERE author_id = s.id AND moderation = 0),
            99
        ) AS computed_rating,
        c.title AS classification_title,
        ci.name AS city_name,
        (SELECT GROUP_CONCAT(se.title SEPARATOR ', ')
           FROM dt_service se
          WHERE se.id IN ($service_ids_str)
        ) AS specialization,
        (SELECT GROUP_CONCAT(se_all.title SEPARATOR ', ')
           FROM dt_service se_all
          WHERE se_all.id IN (
              SELECT service_id FROM dt_specialist_specialization WHERE specialist_id = s.id
          )
        ) AS all_specializations,
        (SELECT GROUP_CONCAT(cm2.name SEPARATOR ', ')
           FROM dt_metro cm2
          WHERE cm2.id = (
              SELECT metro
                FROM dt_specialist_contacts
               WHERE specialist_id = s.id
          )
        ) AS metro_name,
        co.title AS company_name,
        (SELECT COUNT(*)
           FROM dt_specialist_specialization
          WHERE specialist_id = s.id
        ) AS specialization_count,
        COALESCE(
           (SELECT MAX(tariff_id)
              FROM dt_tariff_list
             WHERE specialist_id = s.id
               AND tariff_id IN (1,2,3)
               AND moderation = 0
           ), 0
        ) AS max_tariff,
        EXISTS(
           SELECT 1
             FROM dt_options_list
            WHERE specialist_id = s.id
              AND moderation = 0
        ) AS has_option,
        (
        SELECT MIN(pl.sort)
        FROM dt_promo_list pl
        LEFT JOIN dt_service se2 ON pl.service_slug = se2.slug
        WHERE pl.specialist_id = s.id
          AND CURRENT_TIMESTAMP >= pl.date_start
          AND DATE(pl.date_expires) >= CURRENT_DATE
          AND moderation = 0
          AND (se2.id IN ($service_ids_str) AND pl.service_slug IS NOT NULL AND pl.service_slug <> '')
        ) AS promo_sort,
    
        (
        SELECT pl.id
        FROM dt_promo_list pl
        LEFT JOIN dt_service se2 ON pl.service_slug = se2.slug
        WHERE pl.specialist_id = s.id
          AND CURRENT_TIMESTAMP >= pl.date_start
          AND DATE(pl.date_expires) >= CURRENT_DATE
          AND moderation = 0
          AND (se2.id IN ($service_ids_str) AND pl.service_slug IS NOT NULL AND pl.service_slug <> '')
        ORDER BY pl.date_start DESC, pl.id DESC
        LIMIT 1
        ) AS promo_record_id,
        
        (SELECT COUNT(*) FROM dt_post WHERE author_id = s.id) AS blog_posts_count
    FROM dt_specialist s
    LEFT JOIN dt_classification c ON s.classification_id = c.id
    LEFT JOIN dt_city ci           ON s.city_id = ci.id
    LEFT JOIN dt_company co        ON s.company_id = co.id
    LEFT JOIN dt_metro cm          ON cm.id = (
        SELECT metro 
          FROM dt_specialist_contacts 
         WHERE specialist_id = s.id
    )
    WHERE (
        (SELECT MAX(service_id)
           FROM dt_specialist_specialization
          WHERE specialist_id = s.id
            AND service_id IN ($service_ids_str)
        )
        OR EXISTS(
           SELECT 1
             FROM dt_promo_list pl2
             LEFT JOIN dt_service se3 ON pl2.service_slug = se3.slug
            WHERE pl2.specialist_id = s.id
              AND CURRENT_TIMESTAMP >= pl2.date_start
              AND DATE(pl2.date_expires) >= CURRENT_DATE
              AND moderation = 0
              AND (se3.id IN ($service_ids_str))
        )
    )
      AND {$whereClause}
      AND s.city_id = ?
      AND s.deleted = 0
      {$andFilter}
    GROUP BY s.id
    ORDER BY
        CASE 
            WHEN promo_sort = 1 THEN 0
            WHEN promo_sort = 2 THEN 1
            ELSE 2
        END,
        promo_record_id DESC,
        promo_record_id IS NULL,
        max_tariff DESC,
        computed_rating DESC,
        s.id ASC
    LIMIT $start, $limit
    ";

    return $data_base->selectAll($sql, $params);
}


// Добавляет запись в таблицу блогов
function set_specialist_email(int $specialist_id, string $email)
{
    global $data_base;
    return $data_base->add("UPDATE dt_specialist SET email= ? WHERE id=$specialist_id", [$email]);
}

// Добавляет запись в таблицу описания юзера
function set_specialist_description(int $specialist_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_description (specialist_id) VALUES (?)", [$specialist_id]);
}
// Добавляет запись в таблицу контактов юзера
function set_specialist_contacts(int $specialist_id, string $phone)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_contacts (specialist_id, phone) VALUES (?, ?)", [$specialist_id, $phone]);
}
// Добавляет запись в таблицу блогов
function set_specialist_blog(int $specialist_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_blog (specialist_id) VALUES (?)", [$specialist_id]);
}

// Возвращает описание специалиста
function get_specialist_description(int $specialist_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist_description WHERE specialist_id = ?", [$specialist_id]);
}

// Возвращает данные о тарифе
function get_tariff_data(int $tariff_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT id, name, price, price_old, options FROM dt_tariff WHERE id = ?", [$tariff_id]);
}

function add_tariff_to_specialist(int $specialist_id, int $tariff_id, string $end_date, int $duration, ?string $orderId)
{
    global $data_base;

    $data_base->add(
        "INSERT INTO dt_tariff_list (specialist_id, tariff_id, end_date, duration, moderation, orderId)
         VALUES (?, ?, ?, ?, 3, ?)",
        [$specialist_id, $tariff_id, $end_date, $duration, $orderId]
    );
}


// Функция для получения данных о подключенных тарифах
function get_connected_tariffs(int $specialist_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT tariff_id, end_date, duration FROM dt_tariff_list WHERE specialist_id = ? AND end_date > CURDATE() AND moderation = 0", [$specialist_id]);
}

// Получение подключенных фотосессий
function get_pro_photo_records(int $specialist_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT moderation FROM dt_pro_photo_list WHERE specialist_id = ?", [$specialist_id]);
}

// Функция для получения имени тарифа по ID
function get_tariff_name(int $tariff_id)
{
    global $data_base;
    $result = $data_base->selectRow("SELECT name FROM dt_tariff WHERE id = ?", [$tariff_id]);
    return $result['name'] ?? '';
}

// Функция для получения всех опций
function get_all_options()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_option");
}

// Функция для получения данных о подключенных поциях
function get_connected_options(int $specialist_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT option_id, end_date FROM dt_options_list WHERE specialist_id = ? AND end_date > CURDATE() AND moderation = 0", [$specialist_id]);
}

// Функция для добавления записи в таблицу dt_option_list
function add_options_to_specialist(int $specialist_id, int $option_id, string $end_date, $orderId)
{
    global $data_base;
    $data_base->add("INSERT INTO dt_options_list (specialist_id, option_id, end_date, orderId) VALUES (?, ?, ?, ?)", [$specialist_id, $option_id, $end_date, $orderId]);
}

// Функция для получения всех баннеров
function get_all_banners()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_banners");
}

// Функция для получения данных о подключенных баннерах
function get_connected_banners(int $specialist_id)
{
    global $data_base;

    $currentDate = date('Y-m-d');

    return $data_base->selectAll("
        SELECT banner_id, start_date, end_date
        FROM dt_banners_list
        WHERE specialist_id = ?
        AND end_date >= ?
    ", [$specialist_id, $currentDate]);
}


// Функция для получения всех подключенных баннеров
function get_all_connected_banners()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_banners_list");
}

// Функция для добавления записи в таблицу dt_banners_list
function add_banners_to_specialist($specialist_id, $banner_id, string $start_date, string $end_date, string $description)
{
    global $data_base;
    $data_base->add("INSERT INTO dt_banners_list (specialist_id, banner_id, start_date, end_date, description) VALUES (?, ?, ?, ?, ?)", [$specialist_id, $banner_id, $start_date, $end_date, $description]);
}

// Функция для получения всех подключений в журнале
function get_all_journals()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_journal");
}

// Функция для получения всех подключений в журнале
function get_all_connected_journals()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_journal_list");
}

// Функция для получения данных в журнал
function get_connected_journals(int $specialist_id)
{
    global $data_base;

    $currentDate = date('Y-m-d');

    return $data_base->selectAll("
        SELECT journal_id, start_date, end_date
        FROM dt_journal_list
        WHERE specialist_id = ?
        AND end_date >= ?
    ", [$specialist_id, $currentDate]);
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
        return true;
    } else {
        return (time() < $connected + 900); // 15 минут
    }
}

// Функция для получения дат размещения по service_slug и sort
function get_occupied_entries($service_slug, $sort)
{
    global $data_base;
    $rows = $data_base->selectAll(
        "SELECT date_start, date_expires, moderation, connected_at, qrcId, documentId
         FROM dt_promo_list
         WHERE service_slug = ? AND sort = ? AND moderation IN (0,4)
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
           AND moderation IN (0,4)
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
    if (in_array($classification, [1, 2, 5, 6, 7])) {
        $group = 1;
    } elseif (in_array($classification, [3, 8])) {
        $group = 2;
    } elseif ($classification === 4) {
        $group = 3;
    } else {
        $group = 1;
    }

    // Берем все активные записи по block_id и service_slug (включая moderation = 4)
    $rows = $data_base->selectAll(
        "SELECT p.date_start, p.date_expires, p.moderation, p.connected_at, p.qrcId, p.documentId,
                s.classification_id, p.specialist_id, p.sort
         FROM dt_promo_list p
         JOIN dt_specialist s ON s.id = p.specialist_id
         WHERE p.block_id = ? AND p.service_slug = ?
           AND p.moderation IN (0,4)
           AND p.date_expires >= CURRENT_DATE",
        [$block_id, $service_slug]
    );

    // Фильтруем по группе специалистов и логике sort
    $valid = array_filter($rows, function ($e) use ($group, $sort, $specialist_id) {
        if (!filter_valid_entry($e)) return false;

        $c = (int)$e['classification_id'];
        if (in_array($c, [1, 2, 5, 6, 7])) {
            $entry_group = 1;
        } elseif (in_array($c, [3, 8])) {
            $entry_group = 2;
        } elseif ($c === 4) {
            $entry_group = 3;
        } else {
            $entry_group = 1;
        }

        // группы должны совпадать
        if ($entry_group !== $group) return false;

        // Если запись того же специалиста → блокируем всегда
        if ($e['specialist_id'] == $specialist_id) return true;

        // Иначе проверяем sort
        return $e['sort'] == $sort;
    });

    return array_map(fn($e) => [
        'date_start'   => $e['date_start'],
        'date_expires' => $e['date_expires'],
        'specialist_id' => $e['specialist_id'],
        'sort'         => $e['sort'],
    ], $valid);
}

// Функция для подсчёта записей по service_slug
function get_entry_count_by_service_slug($service_slug)
{
    global $data_base;
    $count = $data_base->count(
        "SELECT COUNT(*) FROM dt_promo_list WHERE service_slug = ? AND moderation IN (0,4)",
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
           AND (service_slug IS NULL OR service_slug = '') AND moderation IN (0,4)",
        [$block_id]
    );
    return (int)$count;
}

// Проверка конфликта по service_slug
function check_conflict_by_service_slug($service_slug, $specialist_id, $sort)
{
    global $data_base;
    $rows = $data_base->selectAll(
        "SELECT date_start, date_expires, sort, moderation, connected_at, documentId, qrcId
         FROM dt_promo_list
         WHERE service_slug = ? AND specialist_id = ? AND moderation IN (0,4)
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
        "SELECT date_start, date_expires, sort, moderation, connected_at, documentId, qrcId
         FROM dt_promo_list
         WHERE block_id = ? AND specialist_id = ? AND moderation IN (0,4)
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
              AND service_slug = ? AND moderation IN (0,4)
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
           AND moderation IN (0,4)
           AND date_expires >= CURRENT_DATE",
        [$specialist_id, $block_id]
    );
    $valid = array_filter($rows, 'filter_valid_entry');
    return array_map(fn($e) => [
        'date_start'   => $e['date_start'],
        'date_expires' => $e['date_expires'],
    ], $valid);
}

// Функция для добавления записи в таблицу dt_journal_list
function add_journal_to_specialist($specialist_id, $journal_id, string $start_date, string $end_date, string $description)
{
    global $data_base;
    $data_base->add("INSERT INTO dt_journal_list (specialist_id, journal_id, start_date, end_date, description) VALUES (?, ?, ?, ?, ?)", [$specialist_id, $journal_id, $start_date, $end_date, $description]);
}

// Функция для добавления записи в таблицу dt_pro_photo_list
function add_pro_photo($specialist_id, $specialist_phone, $pro_photo_id, $price, $emp_quantity)
{
    global $data_base;
    $data_base->add(
        "INSERT INTO dt_pro_photo_list (specialist_id, specialist_phone, pro_photo_id, price, emp_quantity) VALUES (?, ?, ?, ?, ?)",
        [$specialist_id, $specialist_phone, $pro_photo_id, $price, $emp_quantity]
    );
}

// Функция для получения promo
function get_all_promo()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_promo_placement");
}

// Функция для получения promo
function get_all_promo_blocks()
{
    global $data_base;

    // Получаем все данные из таблицы dt_promo_block
    return $data_base->selectAll("SELECT id, title, price_block1, price_block2 FROM dt_promo_block ORDER BY id");
}

// Функция для добавления записи в таблицу dt_promo_list
function add_promo_list($specialist_id, $block_id, $service_id, $service_name, $sort, $start_date, $end_date, $orderId, $price)
{
    global $data_base;

    if (in_array($service_id, [''])) {
        $service_id = null;
    }

    $formatted_start_date = DateTime::createFromFormat('d.m.Y', $start_date)->format('Y-m-d');
    $formatted_end_date = DateTime::createFromFormat('d.m.Y', $end_date)->format('Y-m-d');

    // Выполняем SQL-запрос с добавлением поля orderId
    $data_base->add(
        "INSERT INTO dt_promo_list (specialist_id, block_id, sort, service_slug, service_name, date_start, date_expires, orderId, amount)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$specialist_id, $block_id, $sort, $service_id, $service_name, $formatted_start_date, $formatted_end_date, $orderId, $price]
    );
}

function add_promo_placement($specialist_id, $promo_id, $service_id, string $start_date, string $end_date, string $price, string $service_name)
{
    global $data_base;
    $data_base->add("INSERT INTO dt_promo_placement_list (specialist_id, promo_id, service_id, start_date, end_date, price, service_name) VALUES (?, ?, ?, ?, ?, ?, ?)", [$specialist_id, $promo_id, $service_id, $start_date, $end_date, $price, $service_name]);
}

// Возвращает все записи из таблицы dt_banners_journal
function get_journal_banners()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_banners_journal ORDER BY id");
}

// Функция для получения данных из промо
function get_connected_promo(int $specialist_id)
{
    global $data_base;

    $currentDate = date('Y-m-d');

    return $data_base->selectAll("
        SELECT 
            pl.id,
            pl.specialist_id,
            pl.sort as promo_id,
            pl.service_slug as service_id,
            DATE(pl.date_start) as start_date,
            DATE(pl.date_expires) as end_date,
            NULL as price, 
            pl.service_name
        FROM dt_promo_list pl
        WHERE pl.specialist_id = ?
        AND pl.date_expires >= ?
        AND moderation = 0
        ORDER BY pl.date_expires DESC
    ", [$specialist_id, $currentDate]);
}

// Функция для получения деталей промо по promo_id
function get_promo_details(int $promo_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT title, url FROM dt_promo_placement WHERE id = ?", [$promo_id]);
}

// Добавляет компанию
function set_company($company, $classification)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_company (title, classification)
                                    VALUE (?, ?)", [$company, $classification]);
}

// Тестовая функция
/* function add_promo_list($specialist_id, $block_id, $sort, $date)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_promo_list (specialist_id, block_id, sort, date_expires)
                                    VALUE (?, ?, ?, ?)", [$specialist_id, $block_id, $sort, $date]);
} */

function get_promo_list(string $service_slug, int $limit = 8)
{
    global $data_base;

    // Получаем alias из URL
    $alias = get_alias_from_url();

    // Основной SQL запрос с фильтрацией по service_slug
    $query = "SELECT
                    s.id,
                    s.name,
                    s.slug,
                    s.lastname,
                    s.photo,
                    s.gallery,
                    s.rating,
                    pl.id AS promo_id,
                    c.title as classification_title,
                    ci.name as city_name,
                    se.title as specialization,
                    cm.name as metro_name,
                    co.title as company_name,
                    (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) as specialization_count,
                    (SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id) AS rating,
                    (SELECT COUNT(rating) FROM dt_specialist_review WHERE specialist_id = s.id) AS count_rating
                FROM dt_promo_list pl
                LEFT JOIN dt_specialist s ON pl.specialist_id = s.id
                LEFT JOIN dt_classification c ON s.classification_id = c.id
                LEFT JOIN dt_city ci ON s.city_id = ci.id
                LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                LEFT JOIN dt_company co ON s.company_id = co.id
                WHERE pl.service_slug = ? AND s.city_id = 365 AND s.deleted = 0
                AND CURRENT_TIMESTAMP >= pl.date_start
                AND DATE(pl.date_expires) >= CURRENT_DATE";

    if ($alias) {
        $query .= " AND pl.service_slug = ?";
        $params = [$service_slug, $alias];
    } else {
        $params = [$service_slug];
    }

    $query .= " GROUP BY s.id, pl.id, pl.sort, se.id, cm.name, pl.date_expires
                ORDER BY pl.sort ASC, promo_id DESC
                LIMIT $limit";

    $specialists = $data_base->selectAll($query, $params);

    if ($service_slug === 'block_top_specialists') {
        $specialists = get_top_specialists_with_promo($specialists, $limit);
    }

    return $specialists;
}

// New function to handle top specialists with promo priority
function get_top_specialists_with_promo(array $promo_specialists, int $limit = 8)
{
    global $data_base;

    // If no promo specialists, fetch default random specialists
    if (empty($promo_specialists)) {
        return get_default_top_specialists($limit);
    }

    // Define classifications
    $lawyer_classifications = [1, 2, 5, 6, 7];
    $expert_classifications = [3, 8];
    $notary_classifications = [4];

    $used_ids = array_column($promo_specialists, 'id');
    $remaining_slots = $limit - count($promo_specialists);

    // If no remaining slots, return promo specialists
    if ($remaining_slots <= 0) {
        return array_slice($promo_specialists, 0, $limit);
    }

    // Count promo specialists by classification
    $promo_counts = [
        'lawyers' => 0,
        'experts' => 0,
        'notaries' => 0
    ];
    foreach ($promo_specialists as $specialist) {
        if (in_array($specialist['classification_id'], $lawyer_classifications)) {
            $promo_counts['lawyers']++;
        } elseif (in_array($specialist['classification_id'], $expert_classifications)) {
            $promo_counts['experts']++;
        } elseif (in_array($specialist['classification_id'], $notary_classifications)) {
            $promo_counts['notaries']++;
        }
    }

    // Calculate remaining specialists needed (target: 4 lawyers, 3 experts, 1 notary)
    $needed_lawyers = max(0, 0 - $promo_counts['lawyers']);
    $needed_experts = max(0, 8 - $promo_counts['experts']);
    $needed_notaries = max(0, 0 - $promo_counts['notaries']);

    $default_specialists = [];

    // Fetch additional lawyers if needed
    if ($needed_lawyers > 0) {
        $lawyers = fetch_specialists_by_classification($lawyer_classifications, $needed_lawyers, $used_ids);
        $default_specialists = array_merge($default_specialists, $lawyers);
        $used_ids = array_merge($used_ids, array_column($lawyers, 'id'));
    }

    // Fetch additional experts if needed
    if ($needed_experts > 0) {
        $experts = fetch_specialists_by_classification($expert_classifications, $needed_experts, $used_ids);
        $default_specialists = array_merge($default_specialists, $experts);
        $used_ids = array_merge($used_ids, array_column($experts, 'id'));
    }

    // Fetch additional notaries if needed
    if ($needed_notaries > 0) {
        $notaries = fetch_specialists_by_classification($notary_classifications, $needed_notaries, $used_ids);
        $default_specialists = array_merge($default_specialists, $notaries);
    }

    // Shuffle default specialists to mix them
    shuffle($default_specialists);

    // Combine promo specialists (first) with default specialists
    $result = array_merge($promo_specialists, $default_specialists);

    // Trim to limit if necessary
    return array_slice($result, 0, $limit);
}

// Helper function to fetch default random top specialists (4 lawyers, 3 experts, 1 notary)
function get_default_top_specialists(int $limit = 8)
{
    global $data_base;

    // Define classifications
    $lawyer_classifications = [1, 2, 5, 6, 7];
    $expert_classifications = [3, 8];
    $notary_classifications = [4];

    $specialists = [];
    $used_ids = [];

    // Fetch 0 lawyers
    $lawyers = fetch_specialists_by_classification($lawyer_classifications, 0, $used_ids);
    $specialists = array_merge($specialists, $lawyers);
    $used_ids = array_merge($used_ids, array_column($lawyers, 'id'));

    // Fetch 8 experts
    $experts = fetch_specialists_by_classification($expert_classifications, 8, $used_ids);
    $specialists = array_merge($specialists, $experts);
    $used_ids = array_merge($used_ids, array_column($experts, 'id'));

    // Fetch 0 notary
    $notaries = fetch_specialists_by_classification($notary_classifications, 0, $used_ids);
    $specialists = array_merge($specialists, $notaries);

    // Shuffle specialists to mix them
    shuffle($specialists);

    // Trim to limit if necessary
    return array_slice($specialists, 0, $limit);
}

// Helper function to fetch specialists by classification with rating >= 4.6
function fetch_specialists_by_classification(array $classifications, int $count, array $exclude_ids = [])
{
    global $data_base;

    $where = 'IN (' . implode(',', $classifications) . ')';
    $params = [$_SESSION['city']['id']];
    $exclude_clause = !empty($exclude_ids) ? 'AND s.id NOT IN (' . implode(',', $exclude_ids) . ')' : '';

    $query = "
        SELECT
            s.id,
            s.name,
            s.lastname,
            s.slug,
            s.photo,
            s.gallery,
            IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) AS rating,
            c.title AS classification_title,
            ci.name AS city_name,
            (SELECT GROUP_CONCAT(se.title SEPARATOR ', ') FROM dt_service se WHERE se.id IN (SELECT service_id FROM dt_specialist_specialization WHERE specialist_id = s.id)) AS specialization,
            (SELECT GROUP_CONCAT(cm.name SEPARATOR ', ') FROM dt_metro cm WHERE cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)) AS metro_name,
            co.title AS company_name,
            (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) AS specialization_count,
            s.classification_id
        FROM dt_specialist s
        LEFT JOIN dt_classification c ON s.classification_id = c.id
        LEFT JOIN dt_city ci ON s.city_id = ci.id
        LEFT JOIN dt_company co ON s.company_id = co.id
        WHERE s.classification_id $where
        AND s.city_id = ?
        AND s.deleted = 0
        $exclude_clause
        AND IFNULL((SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = s.id), 0) >= 0
        GROUP BY s.id
        ORDER BY RAND()
        LIMIT $count
    ";

    return $data_base->selectAll($query, $params);
}

function get_alias_from_url()
{
    $uri = $_SERVER['REQUEST_URI'];

    // Убираем параметры запроса, если они есть, оставляя только путь
    $uri = parse_url($uri, PHP_URL_PATH);

    // Убираем возможный слэш в конце
    $uri = rtrim($uri, '/');

    // Разбиваем URL на части
    $uri_arr = explode('/', $uri);

    // Получаем последний сегмент URL
    $last_segment = end($uri_arr);

    // Массив допустимых окончаний
    $valid_endings = ['yoga-studii'];

    // Если последний элемент в URL соответствует одному из указанных значений и это не alias,
    // добавляем слэш в конец
    if (in_array($last_segment, $valid_endings)) {
        $uri .= '/';
        return null;
    }

    // Возвращаем alias
    return $last_segment;
}




// Возвращает способы связи
function get_contacts()
{
    global $data_base;
    $contacts = $data_base->selectAll("SELECT * FROM dt_contact");

    $data = [];

    foreach ($contacts as $contact) {
        $data[$contact['id']] = $contact;
    }

    return $data;
}

// Возвращает алиасы всех активных страниц из dt_pages
function get_all_active_pages()
{
    global $data_base;
    $pages = $data_base->selectAll("SELECT url FROM dt_pages WHERE active = 1");
    return array_column($pages, 'url');
}

// Возвращает алиасы всех страниц кабинета
function get_all_cabinet_pages()
{
    global $data_base;
    $pages = $data_base->selectAll("SELECT alias FROM dt_cabinet_pages WHERE active = 1");
    $aliases = [];
    foreach ($pages as $page) {
        $aliases[] = $page['alias'];
    }
    return $aliases;
}

function get_journal_post_meta($alias)
{
    global $data_base;

    // 1) Обработка случаев вида id<N>
    if (preg_match('/^id(\d+)$/u', $alias, $m)) {
        $blogId = (int)$m[1];
        $blog   = $data_base->selectRow(
            "SELECT specialist_id FROM dt_blog WHERE id = ?",
            [$blogId]
        );
        if ($blog && !empty($blog['specialist_id'])) {
            $specId = (int)$blog['specialist_id'];
            $spec   = $data_base->selectRow(
                "SELECT name, lastname, company_id, classification_id 
                 FROM dt_specialist 
                 WHERE id = ?",
                [$specId]
            );
            if ($spec) {
                // Изначальный classification_id специалиста
                $specClassId = (int)($spec['classification_id'] ?? 0);

                // Попытаемся получить данные компании, если есть
                $company    = null;
                $companyClassId = null;
                if (!empty($spec['company_id'])) {
                    $company = $data_base->selectRow(
                        "SELECT title, classification 
                         FROM dt_company 
                         WHERE id = ?",
                        [(int)$spec['company_id']]
                    );
                    if ($company) {
                        $companyClassId = (int)$company['classification'];
                    }
                }

                // Карта фраз по classification
                $classificationMap = [
                    1 => 'юриста',
                    2 => 'адвоката',
                    3 => 'эксперта',
                    4 => 'нотариуса',
                    5 => 'юридической компании',
                    6 => 'коллегии адвокатов',
                    7 => 'адвокатского бюро',
                    8 => 'студии йоги',
                ];

                if (isset($classificationMap[$specClassId])) {
                    $phrase = $classificationMap[$specClassId];
                } else {
                    $phrase = 'специалиста';
                }

                // Определяем имя владельца
                if ($company) {
                    $ownerName = $company['title'];
                    $title = "Блог {$phrase} «{$ownerName}» на ЙОГУРТ-ЖУРНАЛ | ЙОГУРТ.РУ";
                    $description = "Авторский блог {$phrase} «{$ownerName}»: разбор тренировок и медитаций, события, достижения студии и экспертные мнения преподавателей.";
                } else {
                    $ownerName = morphos\Russian\inflectName($spec['lastname'] . ' ' . $spec['name'], 'родительный');
                    $ownerName = implode(' ', array_reverse(explode(' ', $ownerName)));
                    $title = "Блог {$phrase} {$ownerName} на ЙОГУРТ-ЖУРНАЛ | ЙОГУРТ.РУ";
                    $description = "Авторский блог {$phrase} {$ownerName}: разбор тренировок и медитаций, события, достижения студии и экспертные мнения преподавателей.";
                }

                $keywords = 'авторский блог, экспертное мнение, студии йоги, йога, медитации, тренировки';

                return [
                    'title'       => $title,
                    'description' => $description,
                    'keywords'    => $keywords,
                ];
            }
        }
    }

    // 2) Обычный поиск в dt_post
    $post = $data_base->selectRow(
        "SELECT title, content, meta_keywords, meta_title, meta_description
         FROM dt_post WHERE alias = ?",
        [$alias]
    );
    if ($post) {
        return [
            'title'       => !empty($post['meta_title'])
                ? strip_tags(html_entity_decode($post['meta_title']))
                : strip_tags(html_entity_decode($post['title'])),
            'description' => !empty($post['meta_description'])
                ? strip_tags(html_entity_decode($post['meta_description']))
                : strip_tags($post['content']),
            'keywords'    => $post['meta_keywords'] ?? '',
        ];
    }

    // 3) Поиск в dt_service
    $service = $data_base->selectRow(
        "SELECT title, meta_title_journal, meta_description_journal, meta_keywords_journal
         FROM dt_service WHERE slug = ?",
        [$alias]
    );
    if ($service) {
        $hasMeta = !empty($service['meta_title_journal']) || !empty($service['meta_description_journal']);
        if ($hasMeta) {
            return [
                'title'       => !empty($service['meta_title_journal'])
                    ? strip_tags(html_entity_decode($service['meta_title_journal']))
                    : '',
                'description' => !empty($service['meta_description_journal'])
                    ? strip_tags(html_entity_decode($service['meta_description_journal']))
                    : '',
                'keywords'    => !empty($service['meta_keywords_journal'])
                    ? strip_tags(html_entity_decode($service['meta_keywords_journal']))
                    : '',
            ];
        }
    }

    // 4) Поиск в dt_tags
    $tag = $data_base->selectRow(
        "SELECT meta_title, meta_description, meta_keywords 
         FROM dt_tags WHERE alias = ?",
        [$alias]
    );
    if ($tag) {
        return [
            'title'       => !empty($tag['meta_title'])
                ? strip_tags(html_entity_decode($tag['meta_title']))
                : '',
            'description' => !empty($tag['meta_description'])
                ? strip_tags(html_entity_decode($tag['meta_description']))
                : '',
            'keywords'    => $tag['meta_keywords'] ?? '',
        ];
    }

    // 5) Фолбэк 404
    return [
        'title'       => 'Статья не найдена',
        'description' => 'Ошибка 404. Страница не существует.',
        'keywords'    => '',
    ];
}

function get_faq_meta($alias)
{
    global $data_base;

    return $data_base->selectRow("SELECT meta_title, meta_description FROM dt_faq_pages WHERE alias = ?", [$alias]);
}

function get_specialist_meta($id)
{
    global $data_base;

    $specialist = $data_base->selectRow("SELECT meta_title, meta_description FROM dt_specialist WHERE id = ?", [$id]);

    if ($specialist) {
        return [
            'title' => $specialist['meta_title'],
            'description' => $specialist['meta_description'],
            'keywords' => ''
        ];
    }

    return null;
}

function get_page_meta($url)
{
    global $data_base;

    $queryParams = $_GET;

    // Если это страница action — проверяем параметры
    if ($url === 'action') {
        if (isset($queryParams['confirmation'])) {
            return [
                'title' => 'Завершение подтверждения страницы на сайте | ЙОГУРТ.РУ',
                'description' => 'Подтверждение страницы на портале студий йоги. Студии с отзывами и рейтингом.',
                'keywords' => 'йога, йога-студии, студии йоги, записаться на йогу'
            ];
        }
        if (isset($queryParams['recovery'])) {
            return [
                'title' => 'Завершение восстановления пароля на сайте | ЙОГУРТ.РУ',
                'description' => 'Восстановление пароля на портале студий йоги. Студии с отзывами и рейтингом.',
                'keywords' => 'йога, йога-студии, студии йоги, записаться на йогу'
            ];
        }
    }

    // Поиск в dt_pages
    $page = $data_base->selectRow("SELECT title, description, keywords FROM dt_pages WHERE url = ?", [$url]);

    if ($page) {
        return [
            'title' => strip_tags(html_entity_decode($page['title'])),
            'description' => strip_tags(html_entity_decode($page['description'])),
            'keywords' => $page['keywords']
        ];
    }

    // Поиск в dt_service по slug
    $service = $data_base->selectRow("SELECT title, meta_title, meta_description, meta_keywords FROM dt_service WHERE slug = ?", [$url]);

    if ($service) {
        $has_meta = !empty($service['meta_title']) || !empty($service['meta_description']);

        if ($has_meta) {
            return [
                'title' => !empty($service['meta_title']) ? strip_tags(html_entity_decode($service['meta_title'])) : strip_tags(html_entity_decode($service['title'])),
                'description' => !empty($service['meta_description']) ? strip_tags(html_entity_decode($service['meta_description'])) : '',
                'keywords' => $service['meta_keywords'] ?? ''
            ];
        }
    }

    return ['title' => 'Страница не найдена', 'description' => 'Ошибка 404. Страница не существует.', 'keywords' => ''];
}

function get_cabinet_page_meta($alias)
{
    global $data_base;
    $page = $data_base->selectRow("SELECT title, description, keywords FROM dt_cabinet_pages WHERE alias = ? AND active = 1", [$alias]);
    return $page ?: ['title' => 'Страница не найдена', 'description' => 'Ошибка 404. Страница не существует.', 'keywords' => ''];
}


// Функция для поиска родительского URL (если это альтернативная страница)
function get_real_page($alias)
{
    global $data_base;
    return $data_base->selectRow("SELECT parent_id FROM dt_pages WHERE url = ?", [$alias]);
}

function get_real_cabinet_page($alias)
{
    global $data_base;
    return $data_base->selectRow("SELECT id, parent_id FROM dt_cabinet_pages WHERE alias = ? AND active = 1", [$alias]);
}

// Возвращает страницу кабинета по алиасу
function get_cabinet_page($alias)
{
    global $data_base;
    $page = $data_base->selectRow("SELECT * FROM dt_cabinet_pages WHERE alias = ?", [$alias]);
    return $page;
}

// Возвращает страницы кабинета для левого меню 
function get_cabinet_menu()
{
    global $data_base;
    $pages = $data_base->selectAll("SELECT * FROM dt_cabinet_pages WHERE in_menu = 1");
    return $pages;
}

// Возвращает страницы кабинета для верхнего меню 
function get_cabinet_top_menu()
{
    global $data_base;
    $pages = $data_base->selectAll("SELECT * FROM dt_cabinet_pages WHERE alias LIKE 'redaktirovat-%'");
    return $pages;
}

// Возвращает страницы кабинета для верхнего меню 
function get_cabinet_blog_top_menu()
{
    global $data_base;
    $pages = $data_base->selectAll("SELECT * FROM dt_cabinet_pages WHERE id=13 OR id=14");
    return $pages;
}

// Проверяет email пользователя
function check_email(string $email)
{
    global $data_base;
    $is_active = $data_base->selectRow("SELECT active FROM dt_specialist WHERE email = ?", [$email]);
    if (!$is_active)
        return false;
    else {
        return $is_active['active'] + 1;
    }
    //return $data_base->exists("SELECT EXISTS(SELECT 1 FROM dt_specialist WHERE email = ?)", [$email]);
}

// Проверяет есть ли email у пользователя
function account_has_email(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT id FROM dt_specialist WHERE id = $id");
}

// Проверяет существует ли название компании
function check_company_registration(string $company)
{
    global $data_base;

    // Проверяем саму компанию в dt_company
    $company_row = $data_base->selectAll("SELECT * FROM dt_company WHERE title = ? LIMIT 1", [$company]);

    if (!$company_row) {
        return [];
    }

    $company_id = $company_row[0]['id'] ?? null;

    if (!$company_id) {
        return [];
    }

    // Проверяем, есть ли специалисты с этой company_id и не удалённые
    $specialists = $data_base->selectAll(
        "SELECT * FROM dt_specialist WHERE company_id = ? AND deleted = 0 LIMIT 5",
        [$company_id]
    );

    if (!$specialists) {
        return [];
    }

    return $company_row;
}

// Проверяет существует ли имя пользователя
function check_user_name(string $name, string $lastname)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_specialist WHERE name = ? AND lastname = ? AND active = ? AND deleted = 0 AND company_id IS NULL  LIMIT 5", [$name, $lastname, 1]);
}

// Проверяет, проходит ли регистрация пользователя
function check_registration(array $data)
{
    global $data_base;
    return $data_base->check("INSERT INTO dt_specialist (name, lastname, email, password, hash, classification_id, city_id, company_id)
                                VALUE (?, ?, ?, ?, ?, ?, ?, ?)", [
        $data['name'],
        $data['lastname'],
        $data['email'],
        $data['password'],
        $data['hash'],
        $data['classification'],
        $_SESSION['city']['id'],
        $data['company_id']
    ]);
}

// Регистрирует пользователя
function registration(array $data)
{
    global $data_base;

    // Если в $data передали city_id — используем его, иначе берём из сессии
    $city_id = !empty($data['city_id'])
        ? (int) $data['city_id']
        : (int) $_SESSION['city']['id'];

    // Сначала добавляем специалиста (без slug)
    $specialist_id = $data_base->add(
        "INSERT INTO dt_specialist 
            (name, lastname, email, password, hash, classification_id, city_id, company_id, active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)",
        [
            $data['name'],
            $data['lastname'],
            $data['email'],
            $data['password'],
            $data['hash'],
            $data['classification'],
            $city_id,
            $data['company_id']
        ]
    );

    // Определяем основу slug
    if (!empty($data['company_id'])) {
        $company = $data_base->selectRow("SELECT title FROM dt_company WHERE id = ?", [$data['company_id']]);
        $base_slug = generateAlias($company['title']);
    } else {
        $full_name = trim($data['name'] . ' ' . $data['lastname']);
        $base_slug = generateAlias($full_name);
    }

    // Добавляем ID в slug
    $slug = $base_slug . '-' . $specialist_id;

    // Обновляем запись с готовым slug
    $data_base->update("UPDATE dt_specialist SET slug = ? WHERE id = ?", [$slug, $specialist_id]);

    return $specialist_id;
}

/**
 * Очищает email у пользователя, если он помечен как удалённый (deleted = 1)
 * и совпадает с указанным email.
 * Возвращает true, если что-то было изменено.
 */
function clear_deleted_user_email_by_email(string $email): bool
{
    global $data_base;

    $user = $data_base->selectRow(
        "SELECT id, deleted_at 
         FROM dt_specialist 
         WHERE email = ? 
           AND deleted = 1 
           AND deleted_at IS NOT NULL
         LIMIT 1",
        [$email]
    );

    if (!$user) {
        return false;
    }

    $deletedTs = strtotime($user['deleted_at']);

    // если прошло 30 суток или больше
    if (time() - $deletedTs < 30 * 24 * 60 * 60) {
        return false;
    }

    $data_base->update(
        "UPDATE dt_specialist 
         SET email = '' 
         WHERE id = ?",
        [$user['id']]
    );

    return true;
}

// Выводит id последнего пользователя
function last_user_id()
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist WHERE s.deleted = 0 ORDER BY id DESC LIMIT 1")['id'];
}

// Проверяет hash пользователя
function check_hash(string $hash, int $user_id)
{
    global $data_base;
    return $data_base->exists("SELECT EXISTS(SELECT 1 FROM dt_specialist WHERE hash = ? AND id = ?)", [$hash, $user_id]);
}

// Активирует пользователя
function activate_user(string $hash, int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET hash = ?, active = 1  WHERE id = ?", [$hash, $user_id]);
}

// Обновление аватара пользователя
function set_user_avatar(string $image, int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET photo = ? WHERE id = ?", [$image, $user_id]);
}
// Обновление JSON-галереи пользователя
function set_user_gallery(string $gallery_json, int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET gallery = ? WHERE id = ?", [$gallery_json, $user_id]);
}
// Обновление JSON-залов пользователя
function set_user_rooms(string $rooms_json, int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET rooms = ? WHERE id = ?", [$rooms_json, $user_id]);
}

// Смена пароля
function set_new_password(string $new_pass, int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET password = ? WHERE id = ?", [$new_pass, $user_id]);
}

// Ставит юзера на удаление при нажатии на "удалить аккаунт" по истечению 30 дней без захода в аккаунт
function delete_user(int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET deleted = 1, deleted_at = NOW() WHERE id = ?", [$user_id]);
}

// Восстановление юзера при входе до истечения 30 дней после удаления
function recover_user(int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET deleted = 0, deleted_at = NULL WHERE id = ?", [$user_id]);
}

function add_recovery_token(int $user_id): string
{
    global $data_base;

    // Генерируем случайный токен
    $token = bin2hex(random_bytes(16)); // 32-символьная строка

    // Записываем токен в поле recovery_token пользователя
    $data_base->update(
        "UPDATE dt_specialist SET recovery_token = ? WHERE id = ?",
        [$token, $user_id]
    );

    return $token;
}

function clear_recovery_token(int $user_id)
{
    global $data_base;
    $data_base->update(
        "UPDATE dt_specialist SET recovery_token = NULL WHERE id = ?",
        [$user_id]
    );
}

// Возвращает дочернюю страницу раздела faq
function get_faq_page($alias)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_faq_pages WHERE alias = ?", [$alias]);
}

// Возвращает дочерние страницы раздела faq
function faq_pages()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_faq_pages");
}

function get_faq_page_by_id($id)
{
    global $data_base;
    return $data_base->selectRow("SELECT alias FROM dt_faq_pages WHERE id = ?", [$id])['alias'] ?? null;
}

// Возвращает ЧаВо для ПРОМО
function faq_promo()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_faq_promo");
}

// Возвращает пользователя по email
function get_user_by_email(string $email)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist WHERE email = ?", [$email]);
}

// Возвращает пользователя по email
function get_user_by_email_reg(string $email)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist WHERE email = ? AND deleted = 0", [$email]);
}

// Возвращает пользователя по id
function get_user_by_id(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist WHERE id = $id");
}

// Возвращает email пользователя по его id
function get_email_by_id(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT email FROM dt_specialist WHERE id = $id")['email'];
}

// Устанавливает новый пароль к аккаунту
function update_password(array $data)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET 
                                                        password = ?, 
                                                        hash = ? 
                                                    WHERE 
                                                        id = ? 
                                                    AND 
                                                        hash = ?", [
        $data['password'],
        $data['new_hash'],
        $data['specialist_id'],
        $data['hash']
    ]);
}

// Функция для подтверждения пользовательских прав
function confirmation_user(array $data)
{
    global $data_base;
    $user = $data['specialist_id'];
    if (!$user) {
        return false;
    }
    $result = $data_base->update("UPDATE dt_specialist SET 
                                    email = ?, 
                                    password = ?, 
                                    hash = ?,
                                    active = '1'
                                WHERE 
                                    id = ?", [
        $data['email'],
        $data['password'],
        $data['new_hash'],
        $data['specialist_id']
    ]);
    return $result;
}

// Записывает отзыв
function set_review(array $data)
{
    global $data_base;

    // Получаем user_id
    if (isset($data['user_id']) && !empty($data['user_id'])) {
        $user_id = (int) $data['user_id'];
    } else {
        if (isset($_COOKIE['guest_id'])) {
            $user_id = $_COOKIE['guest_id'];
        } else {
            $user_id = uniqid('guest_', true);
            setcookie('guest_id', $user_id, time() + 60 * 60 * 24 * 365, "/");
        }
    }

    $existing_review = $data_base->selectRow(
        "SELECT id FROM dt_specialist_review WHERE specialist_id = ? AND user_id = ?",
        [(int) $data['specialist'], $user_id]
    );

    if ($existing_review) {
        return "duplicate";
    }

    return $data_base->add(
        "INSERT INTO dt_specialist_review_all (specialist_id, user_id, rating, review, author, communication, verification_code, is_active) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            (int) $data['specialist'], 
            $user_id, 
            (int) $data['rating'], 
            $data['review'], 
            $data['name'], 
            $data['contact'],
            $data['v_code'], 
            0 
        ]
    );
}

function check_review_code(int $review_id, string $code)
{
    global $data_base;
    return $data_base->exists("SELECT EXISTS(SELECT 1 FROM dt_specialist_review_all WHERE verification_code = ? AND id = ?)", [$code, $review_id]);
}

function activate_review(int $review_id) 
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist_review_all SET is_active = 1 WHERE id = ?", [$review_id]);
}

// Обновляет основную информацию о специалисте
function update_specialist(array $data)
{
    global $data_base;
    $specialistId = (int) $_SESSION['user']['id'];

    // Если пользователь — компания, слаг строится только из названия компании
    // (это уже сделала update_company()), тут его трогать нельзя
    $isCompany = !empty($data['company_id']);

    // 1. Получаем текущие имя, фамилию и слаг из базы для проверки
    $current = $data_base->selectRow("SELECT name, lastname, slug FROM dt_specialist WHERE id = ?", [$specialistId]);

    // 2. Если изменилось имя/фамилия ИЛИ слага в базе вообще нет — обновляем со слагом
    // (только для частных специалистов, не для компаний)
    if (!$isCompany && $current && ($data['name'] !== $current['name'] || $data['lastname'] !== $current['lastname'] || empty($current['slug']))) {
        
        $slugBase = trim($data['name'] . ' ' . $data['lastname']);
        $finalSlug = generateAlias($slugBase) . '-' . $specialistId;

        return $data_base->update("UPDATE dt_specialist SET
                                        name = ?,
                                        lastname = ?,
                                        classification_id = ?,
                                        city_id = ?,
                                        slug = ?
                                    WHERE id = ?", [
            $data['name'],
            $data['lastname'],
            (int) $data['classification'],
            (int) $data['city_id'],
            $finalSlug,
            $specialistId
        ]);
    }

    // 3. Если ничего не менялось, обновляем только стандартные поля (слаг не трогаем)
    return $data_base->update("UPDATE dt_specialist SET
                                    name = ?,
                                    lastname = ?,
                                    classification_id = ?,
                                    city_id = ?
                                WHERE id = ?", [
        $data['name'],
        $data['lastname'],
        (int) $data['classification'],
        (int) $data['city_id'],
        $specialistId
    ]);
}

// Обновляет описание специалиста
function update_specialist_description(array $data)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist_description SET
                                                                    description = ?,
                                                                    university = ?,
                                                                    foundation = ?,
                                                                    inn = ?
                                                                WHERE
                                                                    specialist_id = ?", [
        $data['description'],$data['university'],
        $data['foundation'],$data['inn'],
        (int) $_SESSION['user']['id']
    ]);
}

// Обновляет информацию о компании
function update_company(string $title, int $id)
{
    global $data_base;

    // 1. Получаем текущее название компании и данные специалиста для проверки
    $currentCompany = $data_base->selectRow("SELECT title FROM dt_company WHERE id = ?", [$id]);
    $specialist = $data_base->selectRow("SELECT id, slug FROM dt_specialist WHERE company_id = ?", [$id]);

    // 2. Обновляем название компании в её родной таблице
    $result = $data_base->update("UPDATE dt_company SET title = ? WHERE id = ?", [$title, $id]);

    // 3. Если название изменилось ИЛИ у связанного специалиста нет слага — обновляем слаг в dt_specialist
    if ($specialist && ($currentCompany && $title !== $currentCompany['title'] || empty($specialist['slug']))) {
        
        $specialistId = (int) $specialist['id'];
        $finalSlug = generateAlias($title) . '-' . $specialistId;
        
        $data_base->update("UPDATE dt_specialist SET slug = ? WHERE id = ?", [$finalSlug, $specialistId]);
    }

    return $result;
}
// Проверяет что компания принадлежит пользователю
function check_company(int $company_id)
{
    global $data_base;
    return $data_base->exists("SELECT EXISTS(SELECT 1 FROM dt_specialist s
                                                LEFT JOIN dt_company c 
                                                ON s.company_id = c.id 
                                                WHERE 
                                                    s.id = ? 
                                                AND c.id = ?)", [
        (int) $_SESSION['user']['id'],
        $company_id
    ]);
}


// Обновляет контакты специалиста
function update_contacts(array $data)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist_contacts SET
                                                                metro = ?,
                                                                street = ?,
                                                                house = ?,
                                                                how_to_get = ?,
                                                                phone = ?,
                                                                coordinates = ?,
                                                                other = ?
                                                            WHERE
                                                                specialist_id = ?", [
        $data['metro_id'],
        $data['street'],
        $data['house'],
        $data['how_to_get'],
        $data['phone'],
        $data['coordinates'],
        $data['other'],
        (int) $_SESSION['user']['id']
    ]);
}

// Обновляет город
function update_city(int $city_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET
                                                        city_id = ?
                                                    WHERE
                                                        id = ?", [
        $city_id,
        $_SESSION['user']['id']
    ]);
}

// Обновляет график работы
function update_schedule(string $schedule)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist_description SET
                                                                    schedule = ?
                                                                WHERE
                                                                    specialist_id = ?", [
        $schedule,
        $_SESSION['user']['id']
    ]);
}

// Удаляет все специализации специалиста
function unset_specialist_specializations()
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_specialist_specialization` WHERE specialist_id = ?", [$_SESSION['user']['id']]);
}

/**
 * Добавляет удобство или оборудование специалиста в базу данных
 * 
 * @param int $specialist_id
 * @param int $equipment_id
 * @return mixed
 */
function set_specialist_equipment(int $specialist_id, int $equipment_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_equipment (specialist_id, equipment_id)
                                    VALUE (?, ?)", [$specialist_id, $equipment_id]);
}

/**
 * Удаляет все выбранные удобства и оборудование специалиста
 * 
 * @return mixed
 */
function unset_specialist_equipment()
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_specialist_equipment` WHERE specialist_id = ?", [$_SESSION['user']['id']]);
}

// Удалет все услуги специалиста
function unset_specialist_services()
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_specialist_service` WHERE specialist_id = ?", [$_SESSION['user']['id']]);
}

// Добавляет услуги специалиста
function set_specialist_service(string $name, int $price, int $of)
{
    global $data_base;
    $user_id = intval($_SESSION['user']['id']);
    return $data_base->add("INSERT INTO dt_specialist_service (specialist_id, name, price, ot) VALUE ($user_id, ?, $price, $of)", [
        $name,
    ]);
}

// Возвращает документы специалиста
function get_specialist_documents()
{
    global $data_base;
    $documents = $data_base->selectRow("SELECT document FROM dt_specialist_description WHERE specialist_id = ?", [$_SESSION['user']['id']]);
    return $documents ? (is_null($documents['document']) ? [] : json_decode($documents['document'], true)) : [];
}

// Обновляет документы специалиста
function update_specialist_documents(string $documents)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist_description SET
                                                                    document = ?
                                                                WHERE
                                                                    specialist_id = ?", [
        $documents,
        $_SESSION['user']['id']
    ]);
}

function get_request_data(): array
{
    global $data_base;
 
    $rows = $data_base->selectAll("SELECT * FROM dt_requests ORDER BY id LIMIT 1");
 
    return $rows[0] ?? [];
}

function request_statuses(): array
{
    return [
        'new'          => ['Новая заявка',   'text-danger'],    // красный
        'sent'         => ['Отправлена',     'text-dark'],      // чёрный
        'spam'         => ['Спам',           'text-secondary'], // серый
        'invoice_sent' => ['Отправлен счет', 'text-primary'],   // синий
        'paid'         => ['Оплачена',       'text-success'],   // зелёный
        'unpaid'       => ['Не оплачена',    'text-warning'],   // жёлтый
    ];
}
function request_status_codes(): array
{
    return [
        'sent'         => 1,
        'spam'         => 2,
        'new'          => 3,
        'invoice_sent' => 4,
        'paid'         => 5,
        'unpaid'       => 6,
    ];
}

function request_status_key(array $r): string
{
    $key = array_search((int)($r['moderation'] ?? 0), request_status_codes(), true);
    return $key === false ? 'new' : $key;
}
/**
 * Заявки одного специалиста
 *
 * @param int $specialist_id
 * @return array
 */
function get_specialist_requests(int $specialist_id): array
{
    global $data_base;

    return $data_base->selectAll("
        SELECT 
            ur.*, 
            COALESCE(
                c.title,                         
                CONCAT(s.name, ' ', s.lastname),
                '-'                              
            ) AS specialist_name,
             s.id AS specialist_id
        FROM dt_user_request ur
        LEFT JOIN dt_specialist s ON ur.specialist_id = s.id
        LEFT JOIN dt_company c ON s.company_id = c.id
        WHERE ur.specialist_id = " . (int)$specialist_id . "
        ORDER BY ur.created_at DESC
    ");
}
function get_specialist_inn(int $specialistId): string
{
    global $data_base;
    $row = $data_base->selectRow(
        "SELECT inn FROM dt_specialist_description WHERE specialist_id = ? LIMIT 1",
        [$specialistId]
    );
    return trim((string)($row['inn'] ?? ''));
}

function parse_request_price(string $raw): float
{
    $text = html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (!preg_match('/\d[\d\s\x{00A0}]*(?:[.,]\d+)?/u', $text, $m)) return 0.0;
    $num = preg_replace('/[\s\x{00A0}]+/u', '', $m[0]);
    return (float)str_replace(',', '.', $num);
}

function get_request_price(): float
{
    global $data_base;
    $row = $data_base->selectRow("SELECT price FROM dt_requests ORDER BY id LIMIT 1", []);
    return parse_request_price((string)($row['price'] ?? ''));
}

// Возвращает все тарифы
function get_tariffs()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_tariff");
}

// Возвращает все тарифы продвижения
function get_promotions()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_promotion");
}

// Возвращает все опции
function get_options()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_option");
}

/**
 * @return array
 */
function get_blocks(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_blocks");
}

//Получаем новости

function fillNewsList()
{
    global $data_base;
    return $data_base->selectAll("SELECT alias, title, post_type FROM dt_post WHERE post_type='news' ORDER BY id DESC LIMIT 4");
}

/**
 * @return array
 */
function get_all_social(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_social_network");
}

function getForbiddenWords()
{
    global $data_base;
    $rows = $data_base->selectAll("SELECT word FROM dt_forbidden_words");
    return array_column($rows, 'word');
}

function get_last_payment(int $specialist_id): ?array
{
    global $data_base;

    $row = $data_base->selectRow(
        "SELECT tableType, orderId
         FROM dt_payments
         WHERE specialist_id = ?
         ORDER BY createdAt DESC
         LIMIT 1",
        [$specialist_id]
    );

    return $row ?: null; // если false, вернуть null
}

/**
 * Получает дату последнего изменения сайта.
 */
function get_last_modified(): ?array
{
    global $data_base;

    $row = $data_base->selectRow(
        "SELECT last_modified 
         FROM dt_last_update 
         WHERE id = 1"
    );

    return $row ?: null; // если записи нет — вернёт null
}

/**
 * Обновляет дату последнего изменения сайта (last_modified).
 */
function update_last_modified(): bool
{
    global $data_base;

    // Текущее время в формате SQL DATETIME
    $now = date('Y-m-d H:i:s');

    // Обновляем строку с id = 1
    return $data_base->update(
        "UPDATE dt_last_update SET last_modified = ? WHERE id = 1",
        [$now]
    );
}
