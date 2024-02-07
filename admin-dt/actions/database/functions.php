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
function get_specialist(int $specialist_id): mixed
{
    global $data_base;
    return $data_base->selectRow("SELECT 
                                        s.*, 
                                        sc.*,
                                        sc.metro as metro_id,
                                        m.name as metro,
                                        c.title as classification,
                                        c.type as type,
                                        city.name as city,
                                        sd.description, 
                                        sd.foundation, 
                                        sd.schedule as schedule, 
                                        sd.document,
                                        sd.university,
                                        com.title as company,
                                        (SELECT COUNT(*) + 1 FROM dt_specialist WHERE rating > s.rating) as rang
                                    FROM dt_specialist s
                                    LEFT JOIN dt_specialist_contacts sc ON s.id = sc.specialist_id
                                    LEFT JOIN dt_classification c ON s.classification_id = c.id
                                    LEFT JOIN dt_city city ON s.city_id = city.id
                                    LEFT JOIN dt_specialist_description sd ON s.id = sd.specialist_id
                                    LEFT JOIN dt_company com ON s.company_id = com.id
                                    LEFT JOIN dt_metro m ON sc.metro = m.id
                                    WHERE s.id = ?", [$specialist_id]);
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
    $result = $data_base->update("UPDATE dt_specialist SET name = ?, lastname = ?, photo = ?, classification_id =?, city_id = ?, company_id = ?, active = ? WHERE id = ?", [
        $data['name'],
        $data['lastname'],
        $data['photo'],
        (int)$data['classification_id'],
        (int)$data['city_id'],
        $data['company_id'],
        (int)$data['active'],
        (int)$data['id']
    ]);

    if ($data['password']) update_password($data);
    if ($data['phone']) update_specialist_phone($data['phone'], $data['id']);

    return $result;
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
                                    VALUE (?, ?, ?, ?, ?)", [
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
 * @param string $order
 * @return array
 */
function get_cities(string $order = 'id ASC'): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_city SORT ORDER BY $order");
}

/**
 * @param string $company
 * @param int $classification_id
 * @return int
 */
function set_company(string $company, int $classification_id): int
{
    global $data_base;
    return $data_base->add("INSERT INTO dt_company (title, classification) VALUE (?, ?)", [$company, $classification_id]);
}

/**
 * @param string $company_name
 * @param int $classification
 * @param int $company_id
 * @return bool
 * @throws Exception
 */
function update_company(string $company_name, int $classification, int $company_id): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_company SET title = ?, classification = ? WHERE id = ?", [
        $company_name,
        $classification,
        $company_id
    ]);
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
 * @return array
 */
function get_banners(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_banners");
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
 * @param int $id
 * @return array
 */
function get_slides(int $id): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_slide WHERE banner_id = ?", [$id]);
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
    return $data_base->update("UPDATE dt_tariff SET price = ?, price_old = ? WHERE id = ?", [
        (int)$data['price'],
        (int)$data['price_old'],
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
    return $data_base->selectRow("SELECT * FROM dt_promotion WHERE id = ?", [$id]);
}

/**
 * @return array
 */
function get_promotions(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_promotion");
}

/**
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_promotion(array $data): bool
{
    global $data_base;
    return $data_base->update("UPDATE dt_promotion SET price = ? WHERE id = ?", [
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
    return $data_base->update("UPDATE dt_option SET price = ? WHERE id = ?", [
        (int)$data['price'],
        (int)$data['id']
    ]);
}