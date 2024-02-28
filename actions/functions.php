<?php

// Блок специалистов
function block(array $specialists, $class = false)
{
    $data = '';

    foreach ($specialists as $key => $specialist) {
        if (!$class) $cl = '';
        else $cl = $class;

        $data .= <<<EOF
            <div class="item $cl">
            <div class="image"><div class="image-box" style="background-image: url('/uploads/avatars/{$specialist['photo']}')"></div>
                <div class="rating">
        EOF;

        if ($specialist['rating']) {
			$data .= rating($specialist['rating']);
		}


        $data .= <<<EOF
					</div>
				</div>
			<a href="/specialist/id{$specialist['id']}" class="link"></a><div class="rocket"></div>
			<div class="name">
		EOF;

		if ($specialist['company_name']) {
			$data .= $specialist['company_name'];
			$page = 'company';
		}

		if (!$specialist['company_name']) {
			$data .= $specialist['name'] . ' ' . $specialist['lastname'];
			$page = 'specialist';
		}

		$data .= <<<EOF
                </div>
                <div class="prof">
                    {$specialist['classification_title']}
                </div>
                <div class="city">
                    {$specialist['city_name']}
        EOF;

        if ($specialist['metro_name'] && $specialist['classification_title'] == 'Нотариус') $data .= "<br /> м. {$specialist['metro_name']}";

        $data .= <<<EOF
			</div>
			<div class="cats">
		EOF;


		if ($specialist['specialization_count'] > 1) {
			if ($specialist['specialization']) $data .=  mb_strimwidth($specialist['specialization'], 0, 21, '...', 'UTF-8');
			$specialization = num_decline(($specialist['specialization_count'] - 1), 'специализация, специализации, специализаций');
			$data .= "<br> и ещё <a href='/specialist/id{$specialist['id']}'>$specialization</a>";
		} else if ($specialist['specialization']) {
			$data .=  mb_strimwidth($specialist['specialization'], 0, 42, '...', 'UTF-8');
		}

		$data .= <<<EOF
                </div>
            </div>
        EOF;
    }

    return $data;
}

// Рейтинг
function rating($rating)
{
	$rating = ceil($rating);
	
	$star = $rating;
	$star_o = 5 - $rating;
	
	$data = '';

	for($star; $star > 0; $star--) {
		$data .= "<i class='fa fa-star'></i>";
	}

	for($star_o; $star_o > 0; $star_o--) {
		$data .= "<i class='fa fa-star-o'></i>";
	}

	return $data;
}

//получение ссылки классификации
function get_classification_link(int $classification): string
{
	if($classification == 3 || $classification == 8 ) {
		$link = 'experty';
	} else if ($classification == 4){
		$link = 'notariusy';
	} else {
		$link = 'yuristy-i-advokaty';
	}
	return $link;
}

//выводит пост(ы) блога, если переменная is_singe равна true, то выводится публикация полностью (для страницы публикации)
/**
 * @param array $articles
 * @param bool $is_single
 * @param int $blog_id
 * @return string
 */
function block_articles(array $articles, bool $is_single = false, int $blog_id = 0): string
{
    $code = '';

    if ($articles) {
        foreach ($articles as $article) {
            $specialist = get_specialist($article['specialist_id']);
            $specialist_rating = '';
            if ($specialist['rating']) {
                $specialist_rating = rating($specialist['rating']);
            }
            if ($specialist['company']) {
                $specialist_name = $specialist['company'];
            } else {
                $specialist_name = $specialist['name'] . ' ' . $specialist['lastname'];
            }

            $date = date("d.m.Y", strtotime($article['date']));
			$article_title = '<div class="link nohover">' . $article['title'] . '</div>';
			$article_content = $article['content'];
            $article_content = nl2br($article_content);
			$article_link = '';
			if (!$is_single) {
				$article_title = '<a href="/article/'.$article['id'].'" class="link">'.$article['title'].'</a>';
				$article_content = cut_text($article['content'], 650);
				$article_link = '<div class="article-bottom-link"><a href="/article/'. $article['id'].'">ЧИТАТЬ ДАЛЕЕ</a></div>';
			} else if ($blog_id){
				$article_link = '<a href="/journal/id' . $specialist['id'] . '" class="articles__back">ВСЕ ЗАПИСИ АВТОРА</a><a href="/journal" class="articles__to-main bottom-button">Смотреть все блоги</a>';
				
			}
			$article_mobile_title = '<div class="mobile-article_profile">
										<span>Автор: </span><a href="/specialist/id'.$article['specialist_id'].'" class="link">'.$specialist_name.'</a>
										<span class="profile-type">/'.$specialist['classification'].'/</span>
									</div>';

            $themes = '';
            $themes_arr = get_article_themes($article['id']);
            if ($themes_arr) {
                $themes = '<div class="tags">';
                foreach ($themes_arr as $theme) {
					$class_link = get_classification_link($theme['classification']);
                    $themes .= '<a href="/'.$class_link.'/' . ($theme['p_id'] === 0 ? '' : $theme['p_slug'] . '/') . $theme['slug'] . '">#' . $theme['title'] . '</a>';
                }
                $themes .= '</div>';
            }
            $code .= <<<EOF
            <div class="article_item">
                <div class="article_profile item">
			    	<div class="image">
						<div class="image-box" style="background-image: url('/uploads/avatars/{$specialist['photo']}')"></div>
			    		<div class="rating">{$specialist_rating}</div>
			    	</div>
			    	<div class="name">{$specialist_name}</div>
			    	<div class="prof">{$specialist['classification']}</div>
                    <div class="city">{$specialist['city']}
EOF;
            if ($specialist['metro']) $code .= "<br /> м. {$specialist['metro']}";

            $code .= <<<EOF
			        </div>
			        <div class="cats">
EOF;
            if ($specialist['specialization']) $code .=  mb_strimwidth($specialist['specialization'], 0, 21, '...', 'UTF-8');;


            if ($specialist['specialization_count'] > 1) {
                $specialization = num_decline(($specialist['specialization_count'] - 1), 'специализация, специализации, специализаций');
                $code .= "<br> и ещё <a href='/specialist/id{$specialist['id']}'>$specialization</a>";
            }
            $code .= <<<EOF
                    </div>
                    <a href="/specialist/id{$specialist['id']}" class="link"></a>
				</div>
                <div class="article_body">
                    <div class="date">{$date}{$themes}</div>
                    {$article_title}
					{$article_mobile_title}
                    <div class="article_text">{$article_content}</div>
                    {$article_link}
                </div>
            </div>
EOF;
        }
    }
    return $code;
}

//выводит пост(ы) блога в ЛИЧНОМ КАБИНЕТЕ, если переменная is_singe равна true, то выводится публикация полностью (для страницы публикации)
/**
 * @param array $articles
 * @param bool $is_single
 * @param int $blog_id
 * @return string
 */
function block_articles_prosmotr(array $articles, bool $is_single = false, int $blog_id = 0): string
{
    $code = '';

    if ($articles) {
        foreach ($articles as $article) {
            $specialist = get_specialist($article['specialist_id']);
            

            $date = date("d.m.Y", strtotime($article['date']));
			$article_title = '<div class="link nohover">' . $article['title'] . '</div>';
			$article_content = $article['content'];
            $article_content = nl2br($article_content);
			$article_link = '';
			if (!$is_single) {
				$article_title = '<a href="/article/'.$article['id'].'" class="link">'.$article['title'].'</a>';
				$article_content = cut_text($article['content'], 650);
			} else if ($blog_id){
				
			}
			$article_mobile_title = '<div class="mobile-article_profile">
									</div>';

            $themes = '';
            $themes_arr = get_article_themes($article['id']);
            if ($themes_arr) {
                $themes = '<div class="tags">';
                foreach ($themes_arr as $theme) {
					$class_link = get_classification_link($theme['classification']);
                    $themes .= '<a href="/'.$class_link.'/' . ($theme['p_id'] === 0 ? '' : $theme['p_slug'] . '/') . $theme['slug'] . '">' . $theme['title'] . '</a>';
                }
                $themes .= '</div>';
            }
            $code .= <<<EOF
            <div class="article_item">
                    <div class="city">
EOF;
            if ($specialist['metro']) $code .= "<br /> м. {$specialist['metro']}";

            $code .= <<<EOF
			        </div>
			        <div class="cats">
EOF;
            if ($specialist['specialization']) $code .=  mb_strimwidth($specialist['specialization'], 0, 21, '...', 'UTF-8');;


            
            $code .= <<<EOF
                    </div>
                    <a href="/specialist/id{$specialist['id']}" class="link"></a>
                <div class="article_body">
                    <div class="date">{$date}{$themes}</div>
                    {$article_title}
					{$article_mobile_title}
                    <div class="article_text">{$article_content}</div>
                    {$article_link}
                </div>
            </div>
EOF;
        }
    }
    return $code;
}

//выводит блок пагинации
/**
 * @param int $count
 * @param int $limit
 * @param int $page
 * @param string $requestion
 * @param int|array $vars
 * @return string
 */
function block_pagination(int $count, int $limit, int $page, string $requestion, string|array $vars): string
{
   	$code = '';
    $total_page = ceil($count / $limit);

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        if (isset($_GET['company'])) $get = 'company=true&';
        if (isset($_GET['specialist'])) $get = 'specialist=true&';
        if (isset($_GET['company']) && isset($_GET['specialist'])) $get = '';

        $code = "<div class='pages'>";

        if ($start > 1) $code .= "<a href='/$requestion'>1</a><a>...</a>";

        for ($i = $start; $i <= $end; $i++) {
            $class =  ($page == $i) ? 'active' : '';
            $code .= "<a href='/$requestion?{$get}page=$i' class='$class'>$i</a>";
        }

        if ($end < $total_page) $code .= "<a>...</a><a href='/$requestion?{$get}page=$total_page'>$total_page</a>";
        
        $code .= "</div>";
		
		//скрытые поля для передачи значений по GET + кнопка "Показать еще"
		if ($page != $total_page) {
			$next_page = $page + 1;
			$category = is_string($vars) ? $vars : 0;
			$classification = isset($vars['classification']) ? implode(',', $vars['classification']) : 0;
			$specialist = isset($_GET['specialist']) && !isset($_GET['company']) ? 1 : 0;
			$company = !isset($_GET['specialist']) && isset($_GET['company']) ? 1 : 0;

			$code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-specialist">
					<input type="hidden" name="requestion" value="{$requestion}">
					<input type="hidden" name="category" value="{$category}">
					<input type="hidden" name="classification" value="{$classification}">
					<input type="hidden" name="specialist" value="{$specialist}">
					<input type="hidden" name="company" value="{$company}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
		}
    }

    return $code;
}

//пагинация для поиска

function block_pagination_search(int $count, int $limit, int $page, string $requestion, string $filter_req = 'lawyers'): string
{
   	$code = '';
    $total_page = ceil($count / $limit);

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        $code = "<div class='pages'>";

        if ($start > 1) $code .= '<a href="/poisk?zapros='.$requestion.($filter_req ? '&filter='.$filter_req : '').'">1</a><a>...</a>';

        for ($i = $start; $i <= $end; $i++) {
            $class =  ($page == $i) ? 'active' : '';
            $code .= '<a href="/poisk?zapros='.$requestion.($filter_req ? '&filter='.$filter_req : '').'&page='.$i.'" class="'.$class.'">'.$i.'</a>';
        }

        if ($end < $total_page) $code .= '<a>...</a><a href="/poisk?zapros='.$requestion.($filter_req ? '&filter='.$filter_req : '').'&page='.$total_page.'">'.$total_page.'</a>';
        
        $code .= "</div>";
		
		//скрытые поля для передачи значений по GET + кнопка "Показать еще"
		if ($page != $total_page) {
			$next_page = $page + 1;

			$code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-search-result">
					<input type="hidden" name="requestion" value="{$requestion}">
					<input type="hidden" name="filter" value="{$filter_req}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
		}
    }

    return $code;
}

//пагинация для избранного
function block_pagination_fav(int $count, int $limit, int $page, string $specialization): string
{
   	$code = '';
    $total_page = ceil($count / $limit);

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        $code = "<div class='pages'>";

        if ($start > 1) $code .= "<a href='/izbrannoe?specialization={$specialization}'>1</a><a>...</a>";

        for ($i = $start; $i <= $end; $i++) {
            $class =  ($page == $i) ? 'active' : '';
            $code .= "<a href='/izbrannoe?specialization={$specialization}&page=$i' class='$class'>$i</a>";
        }

        if ($end < $total_page) $code .= "<a>...</a><a href='/izbrannoe?specialization={$specialization}&page=$total_page'>$total_page</a>";
        
        $code .= "</div>";

		//скрытые поля для передачи значений по GET + кнопка "Показать еще"
		if ($page != $total_page) {
			$next_page = $page + 1;
			$specialization = isset($_GET['specialization']) ? $_GET['specialization'] : 'lawyers';

			$code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-favorite">
					<input type="hidden" name="specialization" value="{$specialization}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
		}
    }

    return $code;
}


//пагинация для блогов
function block_pagination_blog(int $count, int $limit, int $page, int $blog_id): string
{
   	$code = '';
    $total_page = ceil($count / $limit);

	$blog_href = '';
	if ($blog_id !== -1) {
        $specialist_id = get_blog_by_id($blog_id)['specialist_id'];
		$blog_href = '/id'.$specialist_id;
	}

    if ($total_page > 1) {
        $start = (($page - 2) > 0) ? $page - 2 : 1;
        $end = (($page + 2) < $total_page) ? $page + 2 : $total_page;

        $code = "<div class='pages'>";

        if ($start > 1) $code .= "<a href='/journal{$blog_href}'>1</a><a>...</a>";

        for ($i = $start; $i <= $end; $i++) {
            $class =  ($page == $i) ? 'active' : '';
            $code .= "<a href='/journal{$blog_href}?page=$i' class='$class'>$i</a>";
        }

        if ($end < $total_page) $code .= "<a>...</a><a href='/journal{$blog_href}?page=$total_page'>$total_page</a>";
        
        $code .= "</div>";
		
		//скрытые поля для передачи значений по GET + кнопка "Показать еще"
		if ($page != $total_page) {
			$next_page = $page + 1;

			$code .= <<<EOF
				<div class="bottom-button btn-more-catalog" id="get-blog">
					<input type="hidden" name="blog_id" value="{$blog_id}">
					<input type="hidden" name="page" value="{$next_page}">
					<input type="hidden" name="count" value="{$count}">
					<input type="hidden" name="limit" value="{$limit}">
					<button class="btn btn__orange btn__more">Показать ещё</button>
				</div>
			EOF;
		}
    }

    return $code;
}

/**
 * @param array $articles
 * @param bool $has_text_no_articles
 * @return string
 */
function block_articles_cabinet(array $articles, bool $has_text_no_articles): string
{
    $code = '';
    if ($articles) {
        foreach ($articles as $article) {
            $date = date("d.m.Y", strtotime($article['date']));
            $moderation = '';
            if ($article['moderation'] == 1) {
                $moderation = '<div class="moderation">на модерации</div>';
            }
            $themes = '';
            $themes_arr = get_article_themes($article['id']);
            if ($themes_arr) {
                $themes = '<div class="tags">';
                foreach ($themes_arr as $theme) {
                    $class_link = get_classification_link($theme['classification']);
                    $themes .= '<a href="/'.$class_link.'/' . ($theme['p_id'] === 0 ? '' : $theme['p_slug'] . '/') . $theme['slug'] . '">#' . $theme['title'] . '</a>';
                }
                $themes .= '</div>';
            }
            $code .= <<<EOF
                <div class="article__item">
                    <div class="article_body">
                        <div class="date">{$date}{$themes}</div>
                        <a href="/article/{$article['id']}" class="link">{$article['title']}</a>
                        <div class="article_text">{$article['content']}</div>
                    </div>
                    {$moderation}
                    <div class="article-menu-btn">
                        <div class="article-menu">
                            <a class="menu-point edit" href="/cabinet/prosmotr-zapisi/{$article['id']}">
                                <div class="menu-point-img"><img src="/assets/img/cabinet/open-note.svg"></div>
                                Открыть
                            </a>
                            <a class="menu-point edit" href="/cabinet/sozdat-zapis/{$article['id']}">
                                <div class="menu-point-img"><img src="/assets/img/cabinet/edit-note.svg"></div>
                                Редактировать
                            </a>
                            <a class="menu-point delete" data-id="{$article['id']}">
                                <div class="menu-point-img"><img src="/assets/img/cabinet/delete-note.svg"></div>
                                Удалить
                            </a>
                        </div>
                    </div>
                </div>
            EOF;
		}
    } else if ($has_text_no_articles) {
		$code .= <<<EOF
                <div class="no_articles">
                    В вашем блоге еще нет записей.
                </div>
            EOF;
	}

    return $code;
}