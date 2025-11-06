<?php

session_cache_limiter('');
session_start();

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$lastModifiedRow = get_last_modified();
$lastModified = $lastModifiedRow ? strtotime($lastModifiedRow['last_modified']) : time();

$isUser = isset($_SESSION['user']) ? 'user' : 'guest';

$metroData = $_SESSION['metro'] ?? '';
if (!is_array($metroData)) {
    $metroData = explode(',', (string)$metroData);
}

// Формируем ETag с учетом статуса пользователя
$etag = md5($lastModified . implode(',', $metroData) . $isUser);

// --- 0. URL, для которых кэш всегда запрещён ---
$noCacheUris = [
    '/izbrannoe',
    '/search',
    '/moy_urfaq',
    '/404',
    '/action',
];

if (in_array($requestUri, $noCacheUris, true)) {
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    return;
}

// --- 1. Специальная логика для /notariusy ---
if ($requestUri === '/notariusy') {
    if ($isUser === 'user') {
        // авторизованный — никогда не кэшируем
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    } else {
        // гость — можно кэшировать
        header("ETag: \"$etag\"");
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        header("Vary: Cookie, Accept-Encoding");
        header("Cache-Control: private, max-age=300, must-revalidate, no-cache");

        if (!empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
            $clientEtags = explode(',', $_SERVER['HTTP_IF_NONE_MATCH']);
            foreach ($clientEtags as $cEtag) {
                $cEtag = trim($cEtag, " \"");
                $cEtag = preg_replace('/-gzip$/', '', $cEtag);
                if ($cEtag === $etag) {
                    header('HTTP/1.1 304 Not Modified');
                    exit;
                }
            }
        }
    }
    return; // завершаем, чтобы не выполнять общий блок
}

// --- 2. Общая логика для всех остальных страниц ---
if ($isUser === 'user') {
    // авторизованный
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
} else {
    // гость
    header("ETag: \"$etag\"");
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
    header("Cache-Control: private, max-age=300, must-revalidate, no-cache");

    if (!empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
        $clientEtags = explode(',', $_SERVER['HTTP_IF_NONE_MATCH']);
        foreach ($clientEtags as $cEtag) {
            $cEtag = trim($cEtag, " \"");
            $cEtag = preg_replace('/-gzip$/', '', $cEtag);
            if ($cEtag === $etag) {
                header('HTTP/1.1 304 Not Modified');
                exit;
            }
        }
    }
}


// Путь к фавиконкам
$uploadDirIcon = $_SERVER['DOCUMENT_ROOT'] . '/uploads/fav-icon/';
$faviconPath = '';
$faviconType = '';

$cityGenitive = $cityGenitive ?: ($_SESSION['city_genitive'] ?? 'Москвы');
$cityPrepositional = $cityPrepositional ?: ($_SESSION['city_prepositional'] ?? 'Москве');

$city = $_SESSION['city'];
$cityName = $city['name'];

if (file_exists($uploadDirIcon . 'favicon.svg')) {
    $faviconPath = '/uploads/fav-icon/favicon.svg';
    $faviconType = 'image/svg+xml';
} elseif (file_exists($uploadDirIcon . 'favicon.png')) {
    $faviconPath = '/uploads/fav-icon/favicon.png';
    $faviconType = 'image/png';
}

// Метки для фильтров
$filter_labels = [
    'private' => 'Частные',
    'company' => 'Юридические лица'
];

// Разбираем URL без GET-параметров
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url_parts = explode('/', trim($requestPath, '/'));

// Определяем фильтр и alias
$last = end($url_parts);
$filter = in_array($last, array_keys($filter_labels)) ? $last : null;

if ($filter) {
    // если есть фильтр, alias — предпоследний сегмент
    $alias = prev($url_parts);
} else {
    // иначе — последний
    $alias = $last;
}

if (empty($alias)) {
    $alias = 'index';
}

// Раздел (для блога)
$section = $url_parts[0] ?? 'index';

// Дефолтные мета-данные
$default_meta = get_page_meta('index');
$default_meta = get_page_meta('journal');

$category_meta = array_merge($default_meta, get_all_service_meta($filter, $filter_labels, $cityGenitive));

// Основная логика получения мета-данных
function resolve_meta($alias, $section, $category_meta, $cityName, $cityPrepositional)
{
    // Приоритет 1: Журнал
    if ($section == 'journal') {
        $meta = get_journal_post_meta($alias);
        if ($meta && $meta['title'] !== 'Статья не найдена')
            return $meta;
    }

    $url = strtok($_SERVER['REQUEST_URI'], '?');

    if ($url == '/search') {
        return [
            'title'       => 'Найденные специалисты каталога юридических и экспертных услуг | ЮРФАК.РУ',
            'description' => 'Найденные пользователем на портале юристы, адвокаты, эксперты и нотариусы.',
            'keywords'    => 'юристы, адвокаты, эксперты, нотариусы',
        ];
    }

    // Приоритет 2: Категории (включая dt_service_parent)
    if (isset($category_meta[$alias]))
        return $category_meta[$alias];

    // Приоритет 3: Страница
    $meta = get_page_meta($alias);
    if ($meta && $meta['title'] !== 'Страница не найдена') {
        // Вставляем город только для определённых страниц
        if (in_array($alias, ['index'])) {
            $parts = explode('|', $meta['title']);
            if (count($parts) > 1) {
                $parts[0] = rtrim($parts[0]) . " в {$cityPrepositional}";
                $meta['title'] = implode(' |', $parts);
            }
        }

        return $meta;
    }

    // Приоритет 4: Специалист
    if ($section == 'specialist') {

        if (preg_match('/^id(\d+)$/', $alias, $matches)) {
            $specialist = get_specialist((int) $matches[1]);
        } else {
            // Иначе считаем что это slug
            $specialist = get_specialist_by_slug($alias);
        }

        $rating = !empty($specialist['rating'])
            ? round((float)$specialist['rating'], 1)
            : null;

        // составляем имя владельца
        $hasCompany = !empty($specialist['company']);
        $ownerName  = $hasCompany
            ? $specialist['company']
            : trim($specialist['name'] . ' ' . $specialist['lastname']);

        // карта фраз по classification_id
        $classificationTitleMap = [
            1 => 'Юрист',
            2 => 'Адвокат',
            3 => 'Эксперт',
            4 => 'Нотариус',
            5 => 'Юридическая компания',
            6 => 'Коллегия адвокатов',
            7 => 'Адвокатское бюро',
            8 => 'Экспертное учреждение',
        ];

        // старая карта для description (родительный падеж)
        $classificationMap = [
            1 => 'юриста',
            2 => 'адвоката',
            3 => 'эксперта',
            4 => 'нотариуса',
            5 => 'юридической компании',
            6 => 'коллегии адвокатов',
            7 => 'адвокатского бюро',
            8 => 'экспертного учреждения',
        ];

        $cid = (int) $specialist['classification_id'];

        if ($hasCompany) {
            $cid = (int) $specialist['classification_id'];
        }

        if ($hasCompany) {
            // компания — берём напрямую
            $titleCode = $cid;
        } else {
            // без компании: 1–4
            if ($cid >= 1 && $cid <= 4) {
                $titleCode = $cid;
            } elseif ($cid >= 5 && $cid <= 8) {
                $remap = [5 => 1, 6 => 2, 7 => 2, 8 => 3];
                $titleCode = $remap[$cid];
            } else {
                $titleCode = 1; // фолбэк — юрист
            }
        }

        // собираем title
        $titlePrefix = $classificationTitleMap[$titleCode];
        $ratingText  = $rating ? " (отзывы и рейтинг ★{$rating})" : '';

        $cityName = $specialist['city'];

        // Если есть компания — с кавычками, иначе — без
        if ($hasCompany) {
            $title = "{$titlePrefix} «{$ownerName}»{$ratingText}, г. {$cityName} | ЮРФАК.РУ";
        } else {
            $title = "{$titlePrefix} {$ownerName}{$ratingText}, г. {$cityName} | ЮРФАК.РУ";
        }

        // description остаётся по прежнему, но с картой в родительном
        $descCode   = $hasCompany ? $cid : $titleCode;
        $phrase     = $classificationMap[$descCode];

        if (in_array($titleCode, [5, 6, 7], true)) {
            // корпоративные: юр. компания / коллегия / бюро
            $phrase = $classificationMap[$titleCode];
            $description = "Услуги {$phrase} «{$ownerName}»: подготовка документов, досудебное урегулирование споров, представительство в суде, консультация. Цены, контакты.";
        } elseif (in_array($titleCode, [1, 2], true)) {
            // индивидуальные юрист / адвокат
            $lastname = str_replace(' ', '', $specialist['lastname']);
            $name = str_replace(' ', '', $specialist['name']);
            $phrase = $classificationMap[$titleCode];
            $ownerName = morphos\Russian\inflectName($lastname . ' ' . $name, 'родительный');
            $ownerName = implode(' ', array_reverse(explode(' ', $ownerName)));
            $description = "Услуги {$phrase} {$ownerName}: подготовка документов, досудебное урегулирование споров, представительство в суде, консультация. Цены, контакты.";
        } elseif (in_array($titleCode, [3, 8], true)) {
            if ($titleCode === 8) {
                // экспертное учреждение
                $description = "Услуги экспертного учреждения «{$ownerName}»: строительные, медицинские, автотехнические, почерковедческие и другие виды экспертиз. Заключение для суда и страховых компаний. Цены, контакты.";
            } else {
                $lastname = str_replace(' ', '', $specialist['lastname']);
                $name = str_replace(' ', '', $specialist['name']);
                $ownerName = morphos\Russian\inflectName($lastname . ' ' . $name, 'родительный');
                $ownerName = implode(' ', array_reverse(explode(' ', $ownerName)));
                // индивидуальный эксперт
                $description = "Услуги эксперта {$ownerName}: строительные, медицинские, автотехнические, почерковедческие и другие виды экспертиз. Заключение для суда и страховых компаний. Цены, контакты.";
            }
        } elseif ($titleCode === 4) {
            $lastname = str_replace(' ', '', $specialist['lastname']);
            $name = str_replace(' ', '', $specialist['name']);
            $ownerName = morphos\Russian\inflectName($lastname . ' ' . $name, 'родительный');
            $ownerName = implode(' ', array_reverse(explode(' ', $ownerName)));
            // нотариус
            $description = "Услуги нотариуса {$ownerName}: разработка и удостоверение документов, открытие наследства, свидетельствование верности перевода документов и прочие нотариальные действия. Цены, контакты.";
        } else {
            // универсальный фолбэк
            $phrase = $classificationMap[$titleCode] ?? 'специалиста';
            $description = "Услуги {$phrase} «{$ownerName}»: подготовка документов, досудебное урегулирование споров, представительство в суде, консультация. Цены, контакты.";
        }

        // ключевые слова — в зависимости от классификации
        if (in_array($titleCode, [1, 2, 5, 6, 7], true)) {
            // Юристы и адвокаты (индивидуальные и корпоративные)
            $keywords = 'юрист, адвокат, поиск адвоката, поиск юриста, юридические услуги, помощь юриста, юридическая помощь, юридическая консультация, помощь адвоката';
        } elseif (in_array($titleCode, [3, 8], true)) {
            // Эксперты и экспертные учреждения
            $keywords = 'независимая экспертиза, заказать экспертизу, экспертные исследования, судебная экспертиза, экспертные заключения, проведение экспертиз, специалисты-эксперты';
        } elseif ($titleCode === 4) {
            // Нотариусы
            $keywords = 'нотариус, заверение документов, нотариальная контора, нотариальные услуги, нотариальные действия, оформление доверенности, наследственные дела';
        } else {
            // Универсальный фолбэк (например, для «специалиста»)
            $keywords = 'юрист, адвокат, юридические услуги, помощь юриста, консультация';
        }


        return [
            'title'       => $title,
            'description' => $description,
            'keywords'    => $keywords,
        ];
    }

    // Приоритет 5: Фоллбэк
    return [
        'title' => 'Страница не найдена',
        'description' => 'Ошибка 404. Страница не существует.',
        'keywords' => ''
    ];
}

/**
 * Возвращает абсолютный URL по относительному пути или возвращает уже абсолютный URL.
 *
 * @param string|null $path
 * @return string|null
 */
function full_url(?string $path): ?string
{
    if (empty($path)) return null;
    $path = trim($path);
    if (preg_match('#^https?://#i', $path)) return $path;

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'example.com';
    $path = '/' . ltrim($path, '/');
    return $scheme . '://' . $host . $path;
}

// Получаем финальные мета-данные
$page_meta = resolve_meta($alias, $section, $category_meta, $cityName, $cityPrepositional);

// Поддержка глобальных переменных (на всякий случай)
if ($page_meta['title'] === 'Статья не найдена') {
    global $Title, $Description, $Keywords, $Favicon;
    if (isset($Title[$alias])) {
        $HTML = str_replace('<!--title-->', $Title[$alias], $HTML);
    }
    if (isset($Description[$alias])) {
        $HTML = str_replace('<!--description-->', $Description[$alias], $HTML);
    }
    if (isset($Keywords[$alias])) {
        $HTML = str_replace('<!--keywords-->', $Keywords[$alias], $HTML);
    }
    if (isset($Favicon[$alias])) {
        $HTML = str_replace('<!--favicon-->', $favicon, $HTML);
    }
} else {
    $HTML = str_replace('<!--title-->', htmlspecialchars($page_meta['title']), $HTML);
    $HTML = str_replace('<!--description-->', htmlspecialchars($page_meta['description']), $HTML);
    $HTML = str_replace('<!--keywords-->', htmlspecialchars($page_meta['keywords']), $HTML);
    $HTML = str_replace('<!--favicon-->', htmlspecialchars($faviconPath), $HTML);
}
