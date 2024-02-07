<?php

$data['blog_id_input'] = '';
$data['articles_more_btn'] = '';

//получаем блог и выводим последние записи
$blog = get_specialist_blog($_SESSION['user']['id']);
if ($blog) {
    $blog_id = $blog['id'];
    $data['blog_id_input'] = '<input type="hidden" name="blog_id" value="'.$blog_id.'">';
    $articles = get_blog_articles($blog_id, 0, 5);
    $data['articles'] = block_articles_cabinet($articles, true);
    if (count($articles) == 5) {
        $data['articles_more_btn'] = '<button class="more-articles">Больше записей</button>';
    }
}


$HTML = html_explode([
	'articles' => $data['articles'],
    'blog_id_input' => $data['blog_id_input'],
    'articles_more_btn' => $data['articles_more_btn']
], $HTML);