<?php

use Actions\Database\DataBase;

require_once $_SERVER['DOCUMENT_ROOT'] . '/actions/database/DataBase.php';

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
    $sity_id = $_SESSION['city']['id'];

    return $data_base->selectAll("SELECT *, name as value 
                                        FROM dt_metro 
                                        WHERE name LIKE '%$name%' AND city_id = $sity_id");
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
    $array =  $data_base->selectAll("SELECT * FROM dt_service");

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
    $categories = $data_base->selectAll("SELECT * FROM dt_service WHERE p_id = $p_id ORDER BY title");
    $p_category = $data_base->selectRow("SELECT * FROM dt_service WHERE id = $p_id");

    $result = [];

    foreach ($categories as $category) {
        $category['p_slug'] = $p_category['slug'];
        $result[$category['id']] = $category;
    }

    return $result;
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

// Возвращает список услуг для меню
function get_services_for_menu()
{
    global $data_base;
    $services = $data_base->selectAll("SELECT s.id as id, s.title as title, s.p_id, s.slug,
                                            c.id as classification_id
                                            FROM dt_service s
                                            LEFT JOIN dt_classification c ON s.classification = c.id ORDER BY s.title");

    $data = [];

    foreach ($services as $service) {
        $data[$service['classification_id']][$service['p_id']][$service['id']] = $service;
    }

    return $data;
}


// Возвращает категорию по slug
function get_service_by_slug(string $slug)
{
    global $data_base;
    $category = $data_base->selectRow("SELECT * FROM dt_service WHERE slug = ?", [$slug]);
    if ($category['p_id']) {
        $parrent = get_service($category['p_id']);

        $category = [
            'id'                => $category['id'],
            'title'             => $category['title'],
            'description'       => $category['description'],
            'img'               => $category['img'],
            'p_id'              => $category['p_id'],
            'p_title'           => $parrent['title'],
            'p_slug'            => $parrent['slug'],
            'classification'    => $category['classification'],
            'slug'              => $category['slug']
        ];
    }

    return $category;
}

function search_services(string $search)
{
    global $data_base;
    $search = '%' . $search . '%';
    $categories = $data_base->selectAll("SELECT * FROM dt_service WHERE title LIKE ? ", [$search]);
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
    return $data_base->add("INSERT INTO dt_specialist_review (specialist_id, rating)
                                    VALUE (?, ?)", [$specialist_id, $rating]);
}

// Возвращает специалиста
function get_specialist(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT 
                                        s.*, 
                                        sc.*,
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
                                        (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) as specialization_count,
                                        (SELECT COUNT(*) + 1 FROM dt_specialist WHERE rating > s.rating) as rang,
                                        (SELECT AVG(rating) FROM dt_specialist_review WHERE specialist_id = ? ) as rating,
                                        (SELECT COUNT(rating) FROM dt_specialist_review WHERE specialist_id = ? ) as count_rating
                                    FROM dt_specialist s
                                    LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
                                    LEFT JOIN dt_classification c ON s.classification_id = c.id
                                    LEFT JOIN dt_specialist_review r ON s.id = r.specialist_id
                                    LEFT JOIN dt_city city ON s.city_id = city.id
                                    LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                    LEFT JOIN dt_specialist_description sd ON s.id = sd.specialist_id
                                    LEFT JOIN dt_company com ON s.company_id = com.id
                                    LEFT JOIN dt_metro m ON sc.metro = m.id
                                    WHERE s.id = ?", [$id, $id, $id]);
}

// Возвращает специалистов
function get_specialists_by_ids($ids, int $limit, int $start = 0)
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

    if (is_int($ids)) {
        $where = '= ?';
        $array = [$ids];
    }
    if (is_array($ids) && count($ids) > 1) {
        $where = 'IN (' . implode(',', $ids) . ')';
    } else if (is_array($ids) && count($ids) == 1) {
        $where = '= ?';
        $array = [$ids[0]];
    } else if (is_array($ids) && empty($ids)) {
        return false;
    }
    $specialists = $data_base->selectAll("SELECT
                                                s.id,
                                                s.name,
                                                s.lastname,
                                                s.photo,
                                                s.rating,
                                                c.title as classification_title,
                                                ci.name as city_name,
                                                se.title as specialization,
                                                cm.name as metro_name,
                                                co.title as company_name,
                                                (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id) as specialization_count
                                            FROM dt_specialist s
                                            LEFT JOIN dt_classification c ON s.classification_id = c.id
                                            LEFT JOIN dt_city ci ON s.city_id = ci.id
                                            LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                            LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                            LEFT JOIN dt_company co ON s.company_id = co.id
                                            WHERE s.id $where $and
                                            GROUP BY s.id, se.id, cm.name
                                            ORDER BY s.rating DESC
                                            LIMIT $start, $limit", $array);

    return $specialists;
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
                                            WHERE s.id $where $and", $array);

    return $specialists;
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
    return $data_base->selectAll("SELECT * FROM dt_specialist_review WHERE specialist_id = ?", [$specialist_id]);
}

// Возвращает темы блогов
function get_blog_filter_themes()
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_service WHERE p_id = 0 ORDER BY title");
}

// Возвращает блог специалиста
function get_specialist_blog(int $specialist_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_blog WHERE specialist_id = ?", [$specialist_id]);
}

// Возвращает блог по айди
function get_blog_by_id(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_blog WHERE id = $id");
}

// Возвращает записи общего блога
function get_main_blog_articles(int $theme_id = 0, int $limit = 0, int $start = 0)
{
    global $data_base;

    $where = '';
    if ($theme_id != 0) {
        $where = "WHERE ar.id IN (SELECT article_id FROM dt_article_themes WHERE theme_id = $theme_id OR theme_id IN (SELECT id from dt_service WHERE p_id = $theme_id))";
    }
    return $data_base->selectAll("SELECT ar.*,
                                    bl.specialist_id
                                    FROM dt_article ar
                                    LEFT JOIN dt_blog bl ON ar.blog_id = bl.id
                                    $where
                                    ORDER BY date DESC LIMIT $start, $limit");
}

// Возвращает кол-во записей общего блога
function count_main_blog_articles()
{
    global $data_base;
    return $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_article");
}

// Возвращает записи блога юзера
function get_blog_articles(int $id, int $theme_id = 0, int $limit = 0, int $start = 0)
{
    global $data_base;

    $and = '';
    if ($theme_id != 0) {
        $and = "AND ar.id IN (SELECT article_id FROM dt_article_themes WHERE theme_id = $theme_id OR theme_id IN (SELECT id from dt_service WHERE p_id = $theme_id))";
    }

    $limit_cond = '';
    if ($limit != 0 && $start != 0) {
        $and = "LIMIT $start, $limit";
    }
    
    return $data_base->selectAll("SELECT ar.*,
                                    bl.specialist_id
                                    FROM dt_article ar
                                    LEFT JOIN dt_blog bl ON ar.blog_id = bl.id
                                    WHERE blog_id = $id $and
                                    ORDER BY date DESC $limit_cond");
}

// Возвращает записи блога юзера
function count_blog_articles(int $id)
{
    global $data_base;
    return $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_article WHERE blog_id = $id");
}

// Возвращает кол-во просмотров всех записей блога юзера
function count_blog_views(int $id)
{
    global $data_base;
    return $data_base->selectAll("SELECT SUM(views) FROM dt_article WHERE blog_id = $id");
}

// Возвращает запись
function get_article(int $id)
{
    global $data_base;
    return $data_base->selectRow("SELECT ar.*, bl.specialist_id FROM dt_article ar LEFT JOIN dt_blog bl ON ar.blog_id = bl.id WHERE ar.id = $id");
}

// Возвращает темы (теги) записи
function get_article_themes(int $id)
{
    global $data_base;
    $themes = $data_base->selectAll("SELECT * FROM dt_service WHERE id IN (SELECT theme_id FROM dt_article_themes WHERE article_id = $id)");
    foreach ($themes as $theme) {
        if ($theme['p_id']) {
            $parrent = get_service($theme['p_id']);
            $theme['p_slug'] = $parrent['slug'];
        }
        $themes_res[$theme['id']] = $theme;
    }

    return $themes_res;
}

// Добавляет просмотр на запись блога
function add_article_views(int $id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_article SET views = views + 1 WHERE id = ?", [$id]);
}

// Создает запись
function create_article(array $data)
{
    global $data_base;
    return $data_base->add(
        "INSERT INTO dt_article (blog_id, title, content, keywords, moderation) VALUES (?, ?, ?, ?, 1)", [$data['blog_id'], $data['title'], $data['content'], $data['keywords']]);
}

// Обновляет запись
function update_article(int $article_id, array $data)
{
    global $data_base;
    return $data_base->update("UPDATE dt_article SET title = ?, content = ?, keywords = ? WHERE id =?", [$data['title'], $data['content'], $data['keywords'], $article_id]);
}

// Удаляет запись
function delete_article(int $article_id)
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_article` WHERE id = ?", [$article_id]);
}


// Устанавливает тему записи
function set_article_theme(int $article_id, int $theme_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_article_themes (article_id, theme_id) VALUES (?, ?)", [$article_id, $theme_id]);
}

// Удаляет тему записи
function unset_article_themes(int $article_id)
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_article_themes` WHERE article_id = ?", [$article_id]);
}

// Возвращает всех работников компании
function get_company_employees(int $company_id)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_company_employee WHERE company_id = ?", [$company_id]);
}

// Удаляет сотрудника компании
function unset_company_employee(int $employee_id)
{
    global $data_base;
    return $data_base->delete("DELETE FROM `dt_company_employee` WHERE id = ?", [$employee_id]);
}

// Удаляет сотрудника компании
function set_company_employee($employee, $company_id)
{
    global $data_base;
    return $data_base->add(
        "INSERT INTO dt_company_employee (company_id, name, lastname, post, photo) VALUE (?, ?, ?, ?, ?)",
        [(int)$company_id, $employee['name'], $employee['lastname'], $employee['post'], $employee['file']]
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

// Возвращает список специалистов по специализации
function get_specialists_by_classification($classification, int $limit, int $start = 0)
{
    global $data_base;
    $and = '';
    if (isset($_GET['company']) && !isset($_GET['specialist'])) {
        $and = 'AND s.company_id IS NOT NULL';
    }

    if (isset($_GET['specialist']) && !isset($_GET['company'])) {
        $and = 'AND s.company_id IS NULL';
    }

    if (is_int($classification)) {
        $where = '= ?';
        $array = [$classification];
    }

    if (is_array($classification)) {
        $where = 'IN (' . implode(',', $classification) . ')';
    }
    $array[] = $_SESSION['city']['id'];

    if (($classification == 4) && isset($_SESSION['metro']) && !empty($_SESSION['metro'])) {
        $and .= 'AND cm.id IN (' . $_SESSION['metro'] . ')';
    }

    $specialists = $data_base->selectAll("SELECT
                                                s.id,
                                                s.name,
                                                s.lastname,
                                                s.photo,
                                                s.rating,
                                                c.title as classification_title,
                                                ci.name as city_name,
                                                se.title as specialization,
                                                cm.name as metro_name,
                                                co.title as company_name,
                                                (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)as specialization_count
                                            FROM dt_specialist s
                                            LEFT JOIN dt_classification c ON s.classification_id = c.id
                                            LEFT JOIN dt_city ci ON s.city_id = ci.id
                                            LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                            LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                            LEFT JOIN dt_company co ON s.company_id = co.id
                                            WHERE s.classification_id $where AND s.city_id = ? $and
                                            GROUP BY s.id, se.id, cm.name
                                            ORDER BY s.rating DESC
                                            LIMIT $start, $limit", $array);

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
        $specialists = $data_base->selectAll("SELECT * FROM dt_specialist WHERE (name $condition ? AND lastname $condition ? AND active = 1) OR (name $condition ? AND lastname $condition ? AND active = 1)", [$search_name, $search_lastname, $search_lastname, $search_name]);
    } else if (!empty($search[0]) && empty($search[1])) {
        if (mb_strlen($search[0]) < 4) {
            $condition = '=';
            $search_name = $search[0];
        } else {
            $condition = 'LIKE';
            $search_name = '%' . $search[0] . '%';
        }
        $specialists = $data_base->selectAll("SELECT * FROM dt_specialist WHERE (name $condition ? OR lastname $condition ?) AND active = 1", [$search_name, $search_name]);
    } else return false;
    return $specialists;
}

// Возвращает результат поиска по специалистам (для страницы поиска) 
function full_search_specialists(string $search, int $start = 0, int $limit = 12)
{
    global $data_base;
    $and='';
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
        if (mb_strlen($search[0]) < 4 && mb_strlen($search[1]) < 4) {
            $condition = '=';
            $search_name = $search[0];
            $search_lastname = $search[1];
        } else {
            $condition = 'LIKE';
            $search_name = '%' . $search[0] . '%';
            $search_lastname = '%' . $search[1] . '%';
        }
        $specialists = $data_base->selectAll("SELECT
                                                s.id,
                                                s.name,
                                                s.lastname,
                                                s.photo,
                                                s.rating,
                                                c.title as classification_title,
                                                s.classification_id,
                                                ci.name as city_name,
                                                se.title as specialization,
                                                cm.name as metro_name,
                                                (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)as specialization_count
                                                FROM dt_specialist s
                                                LEFT JOIN dt_classification c ON s.classification_id = c.id
                                                LEFT JOIN dt_city ci ON s.city_id = ci.id
                                                LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                                LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                                WHERE (s.name $condition ? AND s.lastname $condition ? AND s.active = 1 AND s.city_id = ? AND s.company_id IS NULL $and) 
                                                OR (s.name $condition ? AND s.lastname $condition ? AND s.active = 1 AND s.city_id = ? AND s.company_id IS NULL $and)
                                                LIMIT $start, $limit", 
                                                [$search_name, $search_lastname, $search_lastname, $search_name, $_SESSION['city']['id']]);
    } else if (!empty($search[0]) && empty($search[1])) {
        if (mb_strlen($search[0]) < 4) {
            $condition = '=';
            $search_name = $search[0];
        } else {
            $condition = 'LIKE';
            $search_name = '%' . $search[0] . '%';
        }
        $specialists = $data_base->selectAll("SELECT 
                                                s.id,
                                                s.name,
                                                s.lastname,
                                                s.photo,
                                                s.rating,
                                                c.title as classification_title,
                                                s.classification_id,
                                                ci.name as city_name,
                                                se.title as specialization,
                                                cm.name as metro_name,
                                                (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)as specialization_count
                                                FROM dt_specialist s
                                                LEFT JOIN dt_classification c ON s.classification_id = c.id
                                                LEFT JOIN dt_city ci ON s.city_id = ci.id
                                                LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                                LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                                WHERE (s.name $condition ? OR s.lastname $condition ?) 
                                                AND s.active = 1
                                                AND s.company_id IS NULL
                                                AND s.city_id = ?
                                                $and
                                                LIMIT $start, $limit", 
                                                [$search_name, $search_name, $_SESSION['city']['id']]);
    } else return false;
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
                                        s.id as id_link
                                        FROM dt_company com
                                        LEFT JOIN dt_specialist s ON s.company_id = com.id
                                        WHERE com.title $condition ?", [$search]);
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
    $and='';
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
    $companies = $data_base->selectAll("SELECT 
                                        co.title as company_name,
                                        s.id,
                                        s.photo,
                                        s.rating,
                                        co.classification as classification_id,
                                        c.title as classification_title,
                                        ci.name as city_name,
                                        se.title as specialization,
                                        cm.name as metro_name
                                        FROM dt_company co
                                        LEFT JOIN dt_specialist s ON s.company_id = co.id
                                        LEFT JOIN dt_classification c ON s.classification_id = c.id
                                        LEFT JOIN dt_city ci ON s.city_id = ci.id
                                        LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                        LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                        WHERE co.title $condition ? AND s.city_id = ? $and
                                        LIMIT $start, $limit", [$search, $_SESSION['city']['id']]);
    return $companies;
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
            $specialists = $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_specialist WHERE (name $condition2 ? AND lastname $condition2 ? AND active = 1 AND company_id IS NULL $and2) OR (name $condition2 ? AND lastname $condition2 ? AND active = 1 AND company_id IS NULL $and2)", [$search_name, $search_lastname, $search_lastname, $search_name]);
        } else if (!empty($search[0]) && empty($search[1])) {
            if (mb_strlen($search[0]) < 4) {
                $condition2 = '=';
                $search_name = $search[0];
            } else {
                $condition2 = 'LIKE';
                $search_name = '%' . $search[0] . '%';
            }
            $specialists = $data_base->count("SELECT COUNT(DISTINCT id) FROM dt_specialist WHERE (name $condition2 ? AND active = 1 AND company_id IS NULL $and2) OR ( lastname $condition2 ? AND active = 1 AND company_id IS NULL $and2)", [$search_name, $search_name]);
        }
    } else return 0;
    return $companies + $specialists;
}

// Возвращает кол-во специалистов в специализации
function count_specialists_by_classification($classification)
{
    global $data_base;
    $and = '';

    if (isset($_GET['company']) && !isset($_GET['specialist'])) {
        $and = 'AND s.company_id IS NOT NULL';
    }

    if (isset($_GET['specialist']) && !isset($_GET['company'])) {
        $and = 'AND s.company_id IS NULL';
    }

    if (is_int($classification)) {
        $where = '= ?';
        $array = [$classification];
    }

    if (is_array($classification)) {
        $where = 'IN (' . implode(',', $classification) . ')';
    }

    $array[] = $_SESSION['city']['id'];

    if (($classification == 4) && isset($_SESSION['metro']) && !empty($_SESSION['metro'])) {
        $and .= 'AND cm.id IN (' . $_SESSION['metro'] . ')';
    }

    return $data_base->count("SELECT COUNT(DISTINCT s.id)
                                    FROM dt_specialist s
                                    LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
                                    LEFT JOIN dt_metro cm ON sc.metro = cm.id
                                    WHERE s.classification_id $where AND s.city_id = ? $and", $array);
}

// Возвращает количество специалистов в категории
function count_specialists_by_service(string $services_id)
{
    global $data_base;
    $and = '';
    if (isset($_GET['company']) && !isset($_GET['specialist'])) {
        $and = 'AND s.company_id IS NOT NULL';
    }
    if (isset($_GET['specialist']) && !isset($_GET['company'])) {
        $and = 'AND s.company_id IS NULL';
    }
    return $data_base->count("SELECT COUNT(DISTINCT s.id)
                                    FROM dt_specialist s
                                    LEFT JOIN dt_city ci ON s.city_id = ci.id
                                    LEFT JOIN dt_specialist_specialization ss ON s.id = ss.specialist_id
                                    WHERE ss.service_id IN ($services_id) AND s.city_id = ? $and", [$_SESSION['city']['id']]);
}

// Возвращает специалистов из категории
function get_specialists_by_service(string $services, int $limit, int $start = 0)
{
    global $data_base;
    $and = '';
    if (isset($_GET['company']) && !isset($_GET['specialist'])) {
        $and = 'AND s.company_id IS NOT NULL';
    }
    if (isset($_GET['specialist']) && !isset($_GET['company'])) {
        $and = 'AND s.company_id IS NULL';
    }

    if (!$services) $services = '-1';


    $specialists = $data_base->selectAll("SELECT
                                                s.id,
                                                s.name,
                                                s.lastname,
                                                s.photo,
                                                s.rating,
                                                c.title as classification_title,
                                                ci.name as city_name,
                                                se.title as specialization,
                                                cm.name as metro_name,
                                                co.title as company_name,
                                                (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)as specialization_count
                                            FROM dt_specialist s
                                            LEFT JOIN dt_classification c ON s.classification_id = c.id
                                            LEFT JOIN dt_city ci ON s.city_id = ci.id
                                            LEFT JOIN dt_service se ON se.id in ($services)
                                            LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                            LEFT JOIN dt_company co ON s.company_id = co.id
                                            WHERE (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id AND service_id IN ($services))
                                                AND s.city_id = ? $and
                                            GROUP BY s.id, se.id, cm.name
                                            ORDER BY s.rating DESC
                                            LIMIT $start, $limit", [$_SESSION['city']['id']]);

    return $specialists;
}

// Добавляет запись в таблицу описания юзера
function set_specialist_description(int $specialist_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_description (specialist_id) VALUE (?)", [$specialist_id]);
}
// Добавляет запись в таблицу контактов юзера
function set_specialist_contacts(int $specialist_id, string $phone)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_specialist_contacts (specialist_id, phone) VALUE (?, ?)", [$specialist_id, $phone]);
}
// Добавляет запись в таблицу блогов
function set_specialist_blog(int $specialist_id)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_blog (specialist_id, description) VALUE (?, '')", [$specialist_id]);
}

// Возвращает описание специалиста
function get_specialist_description(int $specialist_id)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist_description WHERE specialist_id = ?", [$specialist_id]);
}

// Добавляет компанию
function set_company($company, $classification)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_company (title, classification)
                                    VALUE (?, ?)", [$company, $classification]);
}

// Тестовая функция
function add_promo_list($specialist_id, $block_id, $sort, $date)
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_promo_list (specialist_id, block_id, sort, date_expires)
                                    VALUE (?, ?, ?, ?)", [$specialist_id, $block_id, $sort, $date]);
}

// Возвращает специалистов выбранного промоблока
function get_promo_list(int $block_id, int $limit = 8)
{
    global $data_base;

    return $data_base->selectAll("SELECT
                                        s.id,
                                        s.name,
                                        s.lastname,
                                        s.photo,
                                        s.rating,
                                        c.title as classification_title,
                                        ci.name as city_name,
                                        se.title as specialization,
                                        cm.name as metro_name,
                                        co.title as company_name,
                                        (SELECT COUNT(specialist_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)as specialization_count
                                    FROM `dt_promo_list` pl
                                    LEFT JOIN dt_specialist s ON pl.specialist_id = s.id
                                    LEFT JOIN dt_classification c ON s.classification_id = c.id
                                    LEFT JOIN dt_city ci ON s.city_id = ci.id
                                    LEFT JOIN dt_service se ON se.id = (SELECT MAX(service_id) FROM dt_specialist_specialization WHERE specialist_id = s.id)
                                    LEFT JOIN dt_metro cm ON cm.id = (SELECT metro FROM dt_specialist_contacts WHERE specialist_id = s.id)
                                    LEFT JOIN dt_company co ON s.company_id = co.id
                                    WHERE pl.block_id = ?
                                    GROUP BY s.id, pl.sort, se.id, cm.name, pl.date_expires
                                    ORDER BY pl.sort, pl.date_expires
                                    LIMIT $limit", [$block_id]);
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
    if (!$is_active) return false;
    else {
        return $is_active['active'] + 1;
    }
    //return $data_base->exists("SELECT EXISTS(SELECT 1 FROM dt_specialist WHERE email = ?)", [$email]);
}

// Проверяет существует ли название компании
function check_сompany(string $company)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_company WHERE title = ? LIMIT 2", [$company]);
}

// Проверяет существует ли имя пользователя
function check_user_name(string $name, string $lastname)
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_specialist WHERE name = ? AND lastname = ? AND active = ? LIMIT 5", [$name, $lastname, 1]);
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
    return $data_base->add("INSERT INTO dt_specialist (name, lastname, email, password, hash, classification_id, city_id, company_id, active)
                                VALUE (?, ?, ?, ?, ?, ?, ?, ?, 1)", [
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

// Выводит id последнего пользователя
function last_user_id()
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist ORDER BY id DESC LIMIT 1")['id'];
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
    return $data_base->update("UPDATE dt_specialist SET hash = ?, active = 1 WHERE id = ?", [$hash, $user_id]);
}

// Обновление аватара пользователя
function set_user_avatar(string $image, int $user_id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET photo = ? WHERE id = ?", [$image, $user_id]);
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

// Возвращает дочернюю страницу раздела faq
function get_faq_page($alias) {
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_faq_pages WHERE alias = ?", [$alias]);
}

// Возвращает дочерние страницы раздела faq
function faq_pages() {
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_faq_pages");
}

// Возвращает пользователя по email
function get_user_by_email(string $email)
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_specialist WHERE email = ?", [$email]);
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

// Учтанавливает новый пароль к аккаунту
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

// Записывает отзыв
function set_review(array $data)
{
    global $data_base;
    return $data_base->add(
        "INSERT INTO dt_specialist_review (specialist_id, rating, review, author, communication) VALUE (?, ?, ?, ?, ?)",
        [(int)$data['specialist'], (int)$data['rating'], $data['review'], $data['name'], $data['contact']]
    );
}

// Обновляет основную информацию о специалисте
function update_specialist(array $data)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist SET
                                                        name = ?,
                                                        lastname = ?,
                                                        classification_id = ?,
                                                        city_id = ?
                                                    WHERE
                                                        id = ?", [
        $data['name'],
        $data['lastname'],
        (int)$data['classification'],
        (int)$data['city_id'],
        (int)$_SESSION['user']['id']
    ]);
}

// Обновляет описание специалиста
function update_specialist_description(array $data)
{
    global $data_base;
    return $data_base->update("UPDATE dt_specialist_description SET
                                                                    description = ?,
                                                                    university = ?,
                                                                    foundation = ?
                                                                WHERE
                                                                    specialist_id = ?", [
        $data['description'],
        $data['university'],
        $data['foundation'],
        (int)$_SESSION['user']['id']
    ]);
}

// Обновляет информацию о компании
function update_company(string $title, int $id)
{
    global $data_base;
    return $data_base->update("UPDATE dt_company SET title = ? WHERE id = ?", [$title, $id]);
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
        (int)$_SESSION['user']['id'],
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
                                                                other = ?
                                                            WHERE
                                                                specialist_id = ?", [
        $data['metro_id'],
        $data['street'],
        $data['house'],
        $data['how_to_get'],
        $data['phone'],
        $data['other'],
        (int)$_SESSION['user']['id']
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
    $documents =  $data_base->selectRow("SELECT document FROM dt_specialist_description WHERE specialist_id = ?", [$_SESSION['user']['id']]);
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
