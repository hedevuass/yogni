<?php
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/S.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/CasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Gender.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/BaseInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/NamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianLanguage.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianCasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/FirstNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/MiddleNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/LastNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

$limit       = isset($_POST['limit'])     ? (int)$_POST['limit']     : 1;
$start       = isset($_POST['start'])     ? (int)$_POST['start']     : 0;
$post_type   = isset($_POST['post_type']) ? $_POST['post_type']       : '';

$blog_id     = 0;
$theme_id    = 0;
$tag_id      = 0;
$max_date    = null;
$queryMode   = 'type';
$total_posts = 0;

if (preg_match('/^id(\d+)$/', $post_type, $m)) {
    $blog_id   = (int)$m[1];
    $queryMode = 'blog';
    $row = $data_base->selectRow(
        "SELECT COUNT(*) AS cnt FROM dt_post WHERE post_type='blogs' AND blog_id=? AND moderation=0",
        [$blog_id]
    );
    $total_posts = (int)$row['cnt'];
} elseif ($svc = $data_base->selectRow(
    "SELECT id FROM dt_service WHERE slug = ? LIMIT 1",
    [$post_type]
)) {
    $theme_id  = (int)$svc['id'];
    $queryMode = 'theme';
    $row = $data_base->selectRow(
        "SELECT COUNT(*) AS cnt FROM dt_post WHERE theme_id = ? AND moderation = 0",
        [$theme_id]
    );
    $total_posts = (int)$row['cnt'];
} elseif ($tag = $data_base->selectRow(
    "SELECT id FROM dt_tags WHERE alias = ? LIMIT 1",
    [$post_type]
)) {
    $tag_id    = (int)$tag['id'];
    $queryMode = 'tag';
    $row = $data_base->selectRow(
        "SELECT COUNT(*) AS cnt
         FROM dt_post p
         JOIN dt_post_tags pt ON p.id = pt.article_id
         WHERE pt.tag_id = ? AND p.moderation = 0",
        [$tag_id]
    );
    $total_posts = (int)$row['cnt'];
} elseif (in_array($post_type, ['news', 'blogs', 'articles'], true)) {
    $queryMode = 'type';
    $row = $data_base->selectRow(
        "SELECT COUNT(*) AS cnt FROM dt_post WHERE post_type=? AND moderation=0",
        [$post_type]
    );
    $total_posts = (int)$row['cnt'];
} else {
    $row = $data_base->selectRow(
        "SELECT date FROM dt_post WHERE alias=? LIMIT 1",
        [$post_type]
    );
    if (!empty($row['date'])) {
        $max_date  = $row['date'];
        $queryMode = 'alias';
        $row2 = $data_base->selectRow(
            "SELECT COUNT(*) AS cnt FROM dt_post WHERE date <= ? AND moderation=0",
            [$max_date]
        );
        $total_posts = (int)$row2['cnt'];
    } else {
        $row = $data_base->selectRow(
            "SELECT COUNT(*) AS cnt FROM dt_post WHERE moderation=0"
        );
        $total_posts = (int)$row['cnt'];
    }
}

$posts = [];

if ($queryMode === 'blog') {
    if ($start < $total_posts) {
        $posts = get_actual_posts('', 0, 0, $limit, $start, $blog_id, '');
    } else {
        $new_start = max(0, $start - $total_posts);
        $posts = get_actual_posts('', 0, 0, $limit, $new_start, 0, '', $blog_id);
        $total_all = $data_base->selectRow(
            "SELECT COUNT(*) AS cnt FROM dt_post WHERE moderation=0 AND (blog_id IS NULL OR blog_id != ?)",
            [$blog_id]
        );
        $total_posts = (int)$total_all['cnt'];
        $start = $new_start;
    }
} elseif ($queryMode === 'alias') {
    $posts = get_actual_posts('', 0, 0, $limit, $start, 0, $max_date);
} elseif ($queryMode === 'theme') {
    $posts = get_actual_posts('', $theme_id, 0, $limit, $start, 0, '');
} elseif ($queryMode === 'tag') {
    $posts = get_actual_posts('', 0, $tag_id, $limit, $start, 0, '');
} else {
    $posts = get_actual_posts($post_type, 0, 0, $limit, $start, 0, '');
}

$loaded_count   = count($posts);
$has_more_posts = ($start + $loaded_count < $total_posts);

// Формируем HTML для постов
$data = '';
foreach ($posts as $post) {
    $post_alias = '/journal';
    $author_name = $author_blog_link = '';

    if ($post['post_type'] == 'blogs') {
        $post_alias .= '/id' . $post['blog_id'];
        $specialist = get_specialist($post['author_id']);
        $author_name = $specialist['company'] ? $specialist['company'] : $specialist['name'] . ' ' . $specialist['lastname'];
        $author_blog_link = $post_alias;
    }

    $post_alias .= '/' . $post['alias'];

    $data .= '<div class="item2">';
    $data .= '<div class="flex post-data">';
    $data .= '<a href="/journal/' . $post['service_slug'] . '" class="cat_' . $post['post_type'] . '"><span>' . $post['service_title'] . '</span></a>';
    $data .= '<div class="date">' . date('d.m.Y', strtotime($post['date'])) . '</div>';
    $data .= '</div>';
    $data .= '<a href="' . $post_alias . '" class="name">' . strip_tags(html_entity_decode($post['title'])) . '</a>';
    if ($post['photo'] != '' && !is_null($post['photo'])) {
        $data .= '<a href="' . $post_alias . '" class="image lazy-bg" data-bg="/uploads/articles/' . $post['photo'] . '"></a>';
    }
    if ($post['post_type'] == 'blogs') {
        $content = html_entity_decode($post['content'], ENT_QUOTES, 'UTF-8');
        // Меняем переносы и блочные теги на пробел
        $content = preg_replace('/<\s*br\s*\/?>/i', ' ', $content);
        // Убираем остальные HTML-теги
        $content = strip_tags($content);
        // Сжимаем лишние пробелы
        $content = preg_replace('/\s{2,}/u', ' ', trim($content));

        $data .= '<a class="content cut-text-blogs" href="' . $post_alias . '">' . $content . '</a>';
    } else {
        $content = html_entity_decode($post['content'], ENT_QUOTES, 'UTF-8');
        $content = preg_replace('/<\s*br\s*\/?>/i', ' ', $content);
        $content = preg_replace('/<\/(p|div|li|h[1-6])>/i', ' ', $content);
        $content = strip_tags($content);
        $content = preg_replace('/\s{2,}/u', ' ', trim($content));

        $data .= '<a class="content cut-text" href="' . $post_alias . '">' . $content . '</a>';
    }


    if ($author_blog_link && $author_name) {
        $data .= '<a href="' . $author_blog_link . '" class="author">' . htmlspecialchars($author_name) . '</a>';
    }
    $article_tags = get_article_tags($post['id']);
    if ($post['post_type'] != 'blogs' && $article_tags) {
        $data .= '<div class="tags">';
        foreach ($article_tags as $tag) {
            $data .= '<a href="/journal/' . $tag['alias'] . '">#' . $tag['title'] . '</a>';
        }
        $data .= '</div>';
    }
    $data .= '</div>';
}

echo json_encode([
    'posts' => $data,
    'loaded_count' => $loaded_count,
    'has_more_posts' => $has_more_posts
]);
