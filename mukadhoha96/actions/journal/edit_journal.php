<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = [];

if (isset($_POST['id'])) {
    $data = [];

    foreach ($_POST as $key => $value) {
        if ($key === 'content' || $key === 'title') {
            $val = is_string($value) ? stripslashes(trim($value)) : '';
            $data[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }

    $response['debug'] = [
        'received_data' => $data
    ];

    if (!isset($data['title']) || !$data['title']) {
        $response['error']['title'] = 'Укажите название';
    }

    if (empty($data['alias']) && !empty($data['title'])) {
        $cleanedTitle = strip_tags(htmlspecialchars_decode($data['title']));
        $data['alias'] = generateAlias($cleanedTitle);
    }

    if (empty($data['meta-title']) && !empty($data['title'])) {
        $data['meta-title'] = strip_tags(htmlspecialchars_decode($data['title']));
    }

    if (empty($data['meta-description']) && !empty($data['content'])) {
        $data['meta-description'] = strip_tags(htmlspecialchars_decode($data['content']));
    }

    $service_img = '';
    $service_icon = '';
    if ($data['id'] !== '') {
        $service = get_post($data['id']);
        if ($service) {
            $service_img = $service['photo'] ?? '';
            $service_icon = $service['icon'] ?? '';
        }
    }

    $is_new_upload = isset($_FILES['photo']) && $_FILES['photo']['error'] === 0;
    $is_deleted = isset($data['existing_photo']) && $data['existing_photo'] === '' && !$is_new_upload;

    if ($is_deleted) {
        if (!empty($service_img)) {
            $old_path = (strpos($service_img, '/') === 0) ? $_SERVER['DOCUMENT_ROOT'] . $service_img : $_SERVER['DOCUMENT_ROOT'] . '/uploads/articles/' . $service_img;
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }
        $data['photo'] = '';
    } elseif ($is_new_upload) {
        global $allow, $deny;

        if (!isset($allow) || !is_array($allow)) {
            $allow = ['jpeg', 'jpg', 'png', 'heic', 'jfif', 'webp'];
        }
        if (!isset($deny) || !is_array($deny)) {
            $deny = [];
        }

        $file = $_FILES['photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allow) || in_array($ext, $deny)) {
            $response['error']['photo'] = 'Допустимы только jpeg, jpg, png, heic, jfif, webp.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $response['error']['photo'] = 'Файл слишком большой (максимальный размер - до 5 МБ).';
        } else {
            $size = getimagesize($file['tmp_name']);
            if (!$size || $size[0] < 50 || $size[1] < 50) {
                $response['error']['photo'] = 'Изображение слишком маленькое (минимум 50x50px).';
            } else {
                if (!empty($service_img)) {
                    $old_path = (strpos($service_img, '/') === 0) ? $_SERVER['DOCUMENT_ROOT'] . $service_img : $_SERVER['DOCUMENT_ROOT'] . '/uploads/articles/' . $service_img;
                    if (file_exists($old_path)) {
                        @unlink($old_path);
                    }
                }

                $folder = 'articles';
                $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $folder . '/';
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }

                $name = 'art_main_' . uniqid() . '_' . date('d-m-y_H-i-s');
                $image = null;

                if (class_exists('HeicToJpg') && HeicToJpg::isHeic($file['tmp_name'])) {
                    $jpgName = $name . '.jpg';
                    $jpgPath = $path . $jpgName;

                    if (HeicToJpg::convert($file['tmp_name'])->saveAs($jpgPath)) {
                        if (function_exists('fix_image_orientation')) {
                            fix_image_orientation($jpgPath);
                        }

                        $image = load_image_webp([
                            'name' => $jpgName,
                            'tmp_name' => $jpgPath,
                            'size' => filesize($jpgPath),
                            'type' => 'image/jpeg'
                        ], $folder, $name, 5, ['width' => 50, 'height' => 50], [], 75, 1200);

                        if (file_exists($jpgPath)) {
                            unlink($jpgPath);
                        }
                    } else {
                        $response['error']['photo'] = 'Не удалось конвертировать HEIC → JPG.';
                    }
                } else {
                    if (function_exists('fix_image_orientation')) {
                        fix_image_orientation($file['tmp_name']);
                    }

                    $image = load_image_webp($file, $folder, $name, 5, ['width' => 50, 'height' => 50], [], 75, 1200);
                }

                if ($image !== null) {
                    if (isset($image['error'])) {
                        $response['error']['photo'] = $image['error'];
                    } elseif (isset($image['name'])) {
                        $data['photo'] = $image['name'];
                    }
                }
            }
        }
    } else {
        if (isset($service_img)) {
            $data['photo'] = $service_img;
        }
    }

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        if (isset($data['id']) && $data['id'] !== '') {
            update_journal($data);
            $article_id = $data['id'];
            $response['success'] = 'Сервис обновлён';
        } else {
            $article_id = add_journal($data);
            $data['id'] = $article_id;
            $response['success'] = 'Сервис добавлен';
        }

        if (isset($data['tags_title']) && !empty($data['tags_title'])) {
            $tags_title = $data['tags_title'];

            $tag = $data_base->selectRow("SELECT * FROM dt_tags WHERE title = ?", [$tags_title]);
            if (!$tag) {
                $tag_alias = generateAlias($tags_title);
                $data_base->add("INSERT INTO dt_tags (title, alias) VALUES (?, ?)", [$tags_title, $tag_alias]);
                $tag = $data_base->selectRow("SELECT * FROM dt_tags WHERE title = ?", [$tags_title]);
            }
            $tag_id = $tag['id'];

            $data_base->delete("DELETE FROM dt_post_tags WHERE article_id = ?", [$article_id]);
            $data_base->add("INSERT INTO dt_post_tags (tag_id, article_id) VALUES (?, ?)", [$tag_id, $article_id]);
        }
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}