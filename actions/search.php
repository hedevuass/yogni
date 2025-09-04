<?php
//обработка запроса поиска для вывода в окно
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';

if (isset($_POST['search'])) {
    // если строка поиска пустая
    if ($_POST['search'] == "") {
        ?>
        <div class="search-categories">
            <?php
            //получение и вывод категорий (кругляшей)
            $categories = get_blog_filter_themes();
            foreach ($categories as $category) {
                // не выводим специалистов
                if ($category['classification'] == 3) {
                    continue;
                }
                if (mb_stripos($category['title'], 'прочие') !== false) {
                    //раздел "прочие" в конец
                    $last_category = $category;
                } else {
                    echo '<a href="/yuristy-i-advokaty/' . $category['slug'] . '" class="category">' . $category['title'] . '</a>';
                }
            }
            //раздел "прочие"
            if (isset($last_category) && $last_category != '') {
                echo '<a href="/yuristy-i-advokaty/' . $last_category['slug'] . '" class="category">' . $last_category['title'] . '</a>';
            } ?>
        </div>
        <?php die();
    }
    //если строка поиска не пустая
    $cats = [];
    $search_str = trim(htmlspecialchars($_POST['search'], ENT_NOQUOTES));

    // получаем рузельтаты
    $services = search_services($search_str);
    $specialists = search_specialists($search_str);
    $companies = search_companies($search_str);

    //составляем блок "Найдено в категориях"
    $found_in_categories = [];
    $found_in_categories['title'] = "Найдено в категориях";
    //если найдены категории
    if ($services) {
        foreach ($services as $service) {
            $service['text1'] = $service['title'];
            // получаем алиас классификации
            $class_link = get_classification_link($service['classification']);
            $service['link1'] = '/' . $class_link . '/' . $service['slug'];
            if ($service['classification'] == 1 && count($found_in_categories) < 3) {
                if ($service['p_id'] == 0) {
                    $service['bottom_text'] = 'В категории "Все юристы и адвокаты"';
                } else {
                    $p_service = get_service($service['p_id']);
                    $service['bottom_text'] = 'В категории "' . $p_service['title'] . '"';
                }
                $found_in_categories[] = $service;
            } else if ($service['classification'] == 3 && count($found_in_categories) < 3) {
                $service['bottom_text'] = 'В категории "' . 'Все эксперты"';
                $found_in_categories[] = $service;
            }
        }
    }
    // флаги для того, чтобы выводить только один результат из одной категории
    $lawyers = false;
    $experts = false;
    $notaries = false;

    // выводим специалистов
    if ($specialists) {
        foreach ($specialists as $specialist) {
            $full_name = $specialist['name'] . ' ' . $specialist['lastname'];

            if (mb_stripos($full_name, $search_str) !== false) {
                $specialist['text1'] = $full_name;
                $specialist['link1'] = '/specialist/id' . $specialist['id'];

                $lawyer_classifications = [1, 2];
                $expert_classifications = [3, 8];
                $notary_classifications = [4];

                if (in_array($specialist['classification_id'], $lawyer_classifications) && !$lawyers) {
                    $specialist['bottom_text'] = 'В категории "Юристы и адвокаты"';
                    /* $specialist['link1'] .= '&filter=lawyers'; */
                    $found_in_categories[] = $specialist;
                    $lawyers = true;
                } else if (in_array($specialist['classification_id'], $expert_classifications) && !$experts) {
                    $specialist['bottom_text'] = 'В категории "Эксперты"';
                    /* $specialist['link1'] .= '&filter=expert'; */
                    $found_in_categories[] = $specialist;
                    $experts = true;
                } else if (in_array($specialist['classification_id'], $notary_classifications) && !$notaries) {
                    $specialist['bottom_text'] = 'В категории "Нотариусы"';
                    /* $specialist['link1'] .= '&filter=notaries'; */
                    $found_in_categories[] = $specialist;
                    $notaries = true;
                }
            }
        }
    }
    // выводим компании
    if ($companies) {
        foreach ($companies as $company) {
            $company['text1'] = $company['title'];
            // ссылка на результаты поиска
            $company['link1'] = '/specialist/id' . $company['id_link'];
            if (($company['classification'] == 5 || $company['classification'] == 6 || $company['classification'] == 7) && !$lawyers) {
                $company['bottom_text'] = 'В категории "' . 'Юристы и адвокаты"';
                /* $company['link1'] .= '&filter=lawyers'; */
                $found_in_categories[] = $company;
                $lawyers = true;
            } else if ($company['classification'] == 8 && !$experts) {
                $company['bottom_text'] = 'В категории "' . 'Эксперты"';
                /* $company['link1'] .= '&filter=experts'; */
                $found_in_categories[] = $company;
                $experts = true;
            }
        }
    }

    //если были найдены компании/категории/специалисты, то засовываем их в выводимый массив
    if ($services || $companies || $specialists) {
        $cats[] = $found_in_categories;
    }

    // получаем все категории
    $categories = get_blog_filter_themes(); ?>
    <div
        class="search-categories <?= (!empty($services) && empty($specialists) && empty($companies)) ? 'disable-btn' : ''; ?>">
        <?php if (!empty($services)) {
            //выводим кругляши по рез-там поиска если что-то найдено
            ?>
            <?php
            // смотрим тип первого результата (категория или подкатегория) и выводим его по нужной логике в зависимости от типа
            if ($services[0]['p_id'] == 0) {
                // если подкатегория
                echo '<a href="/yuristy-i-advokaty/' . $services[0]['slug'] . '" class="category">' . $services[0]['title'] . '</a>';
                $s_children = get_services_by_parrent_id($services[0]['id']);
                if (!empty($s_children)) {
                    foreach ($s_children as $child) {
                        echo '<a href="/yuristy-i-advokaty/' . $child['slug'] . '" class="category">' . $child['title'] . '</a>';
                    }
                }
            } else {
                // если категория
                echo '<a href="/yuristy-i-advokaty/' . $services[0]['slug'] . '" class="category">' . $services[0]['title'] . '</a>';
                $s_parrent = get_service($services[0]['p_id']);
                $s_sibl = get_services_by_parrent_id($services[0]['p_id']);
                echo '<a href="/yuristy-i-advokaty/' . $s_parrent['slug'] . '" class="category">' . $s_parrent['title'] . '</a>';
                if (!empty($s_sibl)) {
                    foreach ($s_sibl as $sibling) {
                        echo '<a href="/yuristy-i-advokaty/' . $sibling['slug'] . '" class="category">' . $sibling['title'] . '</a>';
                    }
                }
            }
            // выводим остальные категории
            foreach ($categories as $category) {
                if ($category['classification'] == 3 || $services[0]['id'] == $category['id'] || $services[0]['p_id'] == $category['id']) {
                    continue;
                }
                if (mb_stripos($category['title'], 'прочие') !== false) {
                    //раздел "прочие" в конец
                    $last_category = $category;
                } else {
                    echo '<a href="/yuristy-i-advokaty/' . $category['slug'] . '" class="category">' . $category['title'] . '</a>';
                }
            }
            if (isset($last_category) && $last_category != '') {
                echo '<a href="/yuristy-i-advokaty/' . $last_category['slug'] . '" class="category">' . $last_category['title'] . '</a>';
            }
        } else {
            //выводим кругляши если ничего не найдено в категориях
            foreach ($categories as $category) {
                if ($category['classification'] == 3) {
                    continue;
                }
                if (mb_stripos($category['title'], 'прочие') !== false) {
                    //раздел "прочие" в конец
                    $last_category = $category;
                } else {
                    echo '<a href="/yuristy-i-advokaty/' . $category['slug'] . '" class="category">' . $category['title'] . '</a>';
                }
            }
            if (isset($last_category) && $last_category != '') {
                echo '<a href="/yuristy-i-advokaty/' . $last_category['slug'] . '" class="category">' . $last_category['title'] . '</a>';
            }
        } ?>
    </div>
    <div class="search__result">

        <?php if ($cats) {
            //выводим массив результатов поиска
            foreach ($cats as $cat) {
                if ($cat) { ?>
                    <div class="cat">
                        <div class="cat__title"><?= $cat['title'] ?></div>
                        <?php foreach ($cat as $key => $row) {
                            if ($key != 'title') { ?>
                                <a href="<?= $row['link1'] ?>" class="result_row">
                                    <?= $row['text1'] ?>
                                    <div class="under_text"><?= $row['bottom_text'] ?></div>
                                </a>
                            <?php }
                        } ?>
                    </div>
                <?php } ?>
            <?php } ?>
            <div class="cat">
                <div class="cat__title">Нет подходящего варианта?</div>
                <div class="result_row">Больше информации в <a href="/journal" class="orange-link">журнале</a></div>
            </div>
        <?php } else { ?>
            <div class="cat">
                <div class="cat__title">Результат не найден</div>
            </div>
        <?php } ?>
    </div>
<?php }

// function bold_replace($replace_str, $original_str)
// {
//     $pos = mb_stripos($original_str, $replace_str);

//     $replace = mb_substr($original_str, $pos, mb_strlen($replace_str));

//     $text = str_ireplace($replace, "<b>" . $replace . "</b>", $original_str);
//     return $text;
// } 
?>