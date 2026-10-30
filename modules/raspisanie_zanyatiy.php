<?php

//получаем специалиста
/* $specialist = get_specialist($_SESSION['user']['id']); */

$weekdays_short = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
$months_ru = [1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

// часовой пояс, в котором определяем «сегодня» (TODO: брать из настроек сайта/специалиста)
$tz = new DateTimeZone('Europe/Moscow');

$today         = new DateTimeImmutable('today', $tz);
$selected_date = $today;                              // позже: из $_GET['date']
$week_monday   = $selected_date->modify('monday this week'); // лента всегда начинается с понедельника
$strip_days    = 9;

$today_ymd    = $today->format('Y-m-d');
$selected_ymd = $selected_date->format('Y-m-d');

// заголовок над лентой: «Октябрь 2026» или «Октябрь – Ноябрь 2026»
$month_title = function (DateTimeImmutable $from, int $days) use ($months_ru): string {
    $to = $from->modify('+' . ($days - 1) . ' days');
    $a = $months_ru[(int)$from->format('n')];
    $b = $months_ru[(int)$to->format('n')];
    if ($from->format('Y-m') === $to->format('Y-m')) return $a . ' ' . $from->format('Y');
    if ($from->format('Y') === $to->format('Y'))     return $a . ' – ' . $b . ' ' . $from->format('Y');
    return $a . ' ' . $from->format('Y') . ' – ' . $b . ' ' . $to->format('Y');
};

// статусы: done | closed | open
$status_labels = [
    'done'   => 'Проведено',
    'closed' => 'Запись закрыта',
    'open'   => 'Запись идёт',
];

$tomorrow_ymd = $today->modify('+1 day')->format('Y-m-d');

$now           = new DateTimeImmutable('now', $tz);
$specialist = get_specialist($_SESSION['user']['id']);
$specialist_id = (int)$specialist['id'];            // для проверки можно временно написать 1766
$range_end     = $week_monday->modify('+' . ($strip_days - 1) . ' days');

$db_lessons = get_schedule_lessons($specialist_id, $week_monday->format('Y-m-d'), $range_end->format('Y-m-d'), $now);

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$popup_statuses = ['open' => 'Запись идёт', 'closed' => 'Запись закрыта']; // «Проведено» в списке нет: ставится автоматически
$menu = get_services_for_menu(); 

$all_services = [];
$parent_ids   = [];
foreach ($menu as $by_parent) {
    foreach ($by_parent as $p_id => $services) {
        foreach ($services as $service) {
            $all_services[$service['id']] = $service;
            if ((int)$service['p_id'] > 0) $parent_ids[(int)$service['p_id']] = true;
        }
    }
}

// в список идут только конечные направления (у которых нет дочерних),
// порядок как в меню: сначала sort_order, затем название
$leaf_services = array_filter($all_services, fn($s) => !isset($parent_ids[(int)$s['id']]));
usort($leaf_services, fn($a, $b) => [$a['sort_order'], $a['title']] <=> [$b['sort_order'], $b['title']]);

$dicts = get_schedule_dicts($specialist);

$popup_directions = $dicts['directions'];
$popup_teachers   = $dicts['teachers'];
$popup_halls      = $dicts['halls'];

$popup_levels   = [1 => 'L-1 (начальный)', 2 => 'L-2 (лёгкий)', 3 => 'L-3 (средний)', 4 => 'L-4 (интенсивный)'];
$popup_forbidden_words = [];

// время начала: шаг 15 минут
$popup_times = [];
for ($m = 0; $m < 24 * 60; $m += 15) {
    $t = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
    $popup_times[$t] = $t;
}
$popup_durations = [];
for ($m = 15; $m <= 180; $m += 15) {
    $popup_durations[$m] = $m . ' мин';
}
$opts = function (array $items, ?string $placeholder = null) use ($e): string {
    $html = $placeholder !== null
        ? '<option value="" selected disabled hidden>' . $e($placeholder) . '</option>'
        : '';
    foreach ($items as $value => $label) {
        $html .= '<option value="' . $e($value) . '">' . $e($label) . '</option>';
    }
    return $html;
};

$lessons_by_date = [];
for ($i = 0; $i < $strip_days; $i++) {
    $ymd = $week_monday->modify("+$i days")->format('Y-m-d');

    $lessons_by_date[$ymd] = array_map(function ($r) use ($popup_teachers) {
        return [
            'id'           => $r['id'],
            'time'         => $r['time'],
            'duration'     => $r['duration'],
            'status'       => $r['status'],
            'title'        => $r['custom_title'] ?: ($r['service_title'] ?? ''),
            'trainer'      => $popup_teachers[$r['employee_id']] ?? '',
            // данные для попапа редактирования
            'direction'    => $r['service_id'] ?: 'custom',
            'custom_title' => $r['custom_title'] ?? '',
            'teacher'      => $r['employee_id'],
            'hall'         => $r['room_idx'],
            'level'        => $r['level'],
        ];
    }, $db_lessons[$ymd] ?? []);
}

/* ------------------------------------------------------------------
 * Лента дней
 * ------------------------------------------------------------------ */
$days_html = '';
for ($i = 0; $i < $strip_days; $i++) {
    $d   = $week_monday->modify("+$i days");
    $ymd = $d->format('Y-m-d');

    $cls = 'schedule-day';
    if ($ymd < $today_ymd)       $cls .= ' schedule-day--past';
    elseif ($ymd === $today_ymd) $cls .= ' schedule-day--today';
    else                         $cls .= ' schedule-day--future';
    if ($ymd === $selected_ymd)  $cls .= ' schedule-day--selected';

    $days_html .= '
        <button type="button" class="' . $cls . '" data-date="' . $ymd . '">
            <span class="schedule-day__name">' . $weekdays_short[(int)$d->format('N') - 1] . '</span>
            <span class="schedule-day__num">' . $d->format('j') . '</span>
        </button>';
}


/* ------------------------------------------------------------------
 * Список занятий
 * ------------------------------------------------------------------ */
$render_lessons = function ($lessons) use ($e, $status_labels): string {
    $lessons = is_array($lessons) ? array_filter($lessons, 'is_array') : [];

    return implode('', array_map(function ($lesson) use ($e, $status_labels) {
        $is_done = $lesson['status'] === 'done';

        $dropdown = $is_done ? '' : '
                <div class="schedule-dropdown" hidden>
                    <button type="button" class="schedule-dropdown__item" data-action="edit" data-id="' . (int)$lesson['id'] . '">
                       <span class="icon icon-add"><svg width="8" height="15" viewBox="0 0 9 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.210271 1.16312L6.3579 7.01909C6.49273 7.14686 6.56836 7.31934 6.56836 7.49907C6.56836 7.6788 6.49273 7.85128 6.3579 7.97905L0.211604 13.8363C0.0768444 13.9648 0.00133379 14.1379 0.00133379 14.3181C0.00133379 14.4984 0.0768444 14.6714 0.211604 14.8C0.277436 14.8633 0.356119 14.9136 0.443016 14.9479C0.529914 14.9823 0.623267 15 0.717574 15C0.811881 15 0.905234 14.9823 0.992131 14.9479C1.07903 14.9136 1.15771 14.8633 1.22354 14.8L7.36984 8.94525C7.77372 8.55962 8 8.04077 8 7.50032C8 6.95988 7.77372 6.44102 7.36984 6.05539L1.22354 0.200668C1.15769 0.137169 1.07893 0.0866928 0.991905 0.0522261C0.904884 0.0177593 0.811375 9.53674e-07 0.716907 9.53674e-07C0.622439 9.53674e-07 0.52893 0.0177593 0.44191 0.0522261C0.354889 0.0866928 0.276124 0.137169 0.210271 0.200668C0.0755112 0.329231 0 0.502271 0 0.68252C0 0.86277 0.0755112 1.03581 0.210271 1.16437" fill="currentColor"/>
                        </svg></span>Открыть
                    </button>
                    <button type="button" class="schedule-dropdown__item" data-action="edit" data-id="' . (int)$lesson['id'] . '">
                        <span class="icon icon-edit"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 13H15V14H1V13ZM12.7 4.5C13.1 4.1 13.1 3.5 12.7 3.1L10.9 1.3C10.5 0.9 9.9 0.9 9.5 1.3L2 8.8V12H5.2L12.7 4.5ZM10.2 2L12 3.8L10.5 5.3L8.7 3.5L10.2 2ZM3 11V9.2L8 4.2L9.8 6L4.8 11H3Z" fill="currentColor"></path></svg></span>Редактировать
                    </button>
                    <button type="button" class="schedule-dropdown__item schedule-dropdown__item--danger" data-action="delete" data-id="' . (int)$lesson['id'] . '">
                        <span class="icon icon-delete"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_1_5432)"><path d="M9.52 1.33301C9.79984 1.33308 10.0726 1.4212 10.2995 1.58488C10.5265 1.74856 10.6962 1.97951 10.7847 2.24501L11.1467 3.33301H13.3333C13.5101 3.33301 13.6797 3.40325 13.8047 3.52827C13.9298 3.65329 14 3.82286 14 3.99967C14 4.17649 13.9298 4.34605 13.8047 4.47108C13.6797 4.5961 13.5101 4.66634 13.3333 4.66634L13.3313 4.71367L12.7533 12.809C12.7173 13.3134 12.4915 13.7854 12.1214 14.13C11.7513 14.4747 11.2644 14.6663 10.7587 14.6663H5.24133C4.73564 14.6663 4.24874 14.4747 3.87864 14.13C3.50855 13.7854 3.28274 13.3134 3.24667 12.809L2.66867 4.71301C2.66746 4.69748 2.66679 4.68192 2.66667 4.66634C2.48986 4.66634 2.32029 4.5961 2.19526 4.47108C2.07024 4.34605 2 4.17649 2 3.99967C2 3.82286 2.07024 3.65329 2.19526 3.52827C2.32029 3.40325 2.48986 3.33301 2.66667 3.33301H4.85333L5.21533 2.24501C5.3038 1.9794 5.47362 1.74838 5.70073 1.58468C5.92784 1.42099 6.20071 1.33294 6.48067 1.33301H9.51933H9.52ZM11.998 4.66634H4.002L4.57667 12.7137C4.58863 12.8818 4.66385 13.0392 4.78717 13.1541C4.9105 13.269 5.07277 13.3329 5.24133 13.333H10.7587C10.9272 13.3329 11.0895 13.269 11.2128 13.1541C11.3362 13.0392 11.4114 12.8818 11.4233 12.7137L11.998 4.66634ZM6.66667 6.66634C6.82996 6.66636 6.98756 6.72631 7.10958 6.83482C7.23161 6.94332 7.30956 7.09284 7.32867 7.25501L7.33333 7.33301V10.6663C7.33315 10.8363 7.26808 10.9997 7.15143 11.1233C7.03479 11.2468 6.87536 11.3212 6.70574 11.3311C6.53611 11.3411 6.36908 11.2859 6.23878 11.1768C6.10848 11.0678 6.02474 10.9131 6.00467 10.7443L6 10.6663V7.33301C6 7.1562 6.07024 6.98663 6.19526 6.8616C6.32029 6.73658 6.48986 6.66634 6.66667 6.66634ZM9.33333 6.66634C9.51014 6.66634 9.67971 6.73658 9.80474 6.8616C9.92976 6.98663 10 7.1562 10 7.33301V10.6663C10 10.8432 9.92976 11.0127 9.80474 11.1377C9.67971 11.2628 9.51014 11.333 9.33333 11.333C9.15652 11.333 8.98695 11.2628 8.86193 11.1377C8.73691 11.0127 8.66667 10.8432 8.66667 10.6663V7.33301C8.66667 7.1562 8.73691 6.98663 8.86193 6.8616C8.98695 6.73658 9.15652 6.66634 9.33333 6.66634ZM9.52 2.66634H6.48L6.258 3.33301H9.742L9.51933 2.66634H9.52Z" fill="currentColor"></path></g></svg></span>Удалить
                    </button>
                </div>';

                $edit_data = [
                    'status'       => $lesson['status'],
                    'time'         => $lesson['time'],
                    'duration'     => (int)$lesson['duration'],
                    'direction'    => $lesson['direction'] ?? '',
                    'custom_title' => $lesson['custom_title'] ?? '',
                    'teacher'      => $lesson['teacher'] ?? '',
                    'hall'         => $lesson['hall'] ?? '',
                    'level'        => $lesson['level'] ?? '',
                ];
        
                return '
                <div class="schedule-lesson' . ($is_done ? ' schedule-lesson--done' : '') . '" data-lesson-id="' . (int)$lesson['id'] . '" data-lesson="' . $e(json_encode($edit_data, JSON_UNESCAPED_UNICODE)) . '">
                    <div class="schedule-lesson__time">' . $e($lesson['time']) . '</div>
                    <div class="schedule-lesson__title" title="' . $e($lesson['title']) . '">' . $e($lesson['title']) . '</div>
                    <div class="schedule-lesson__duration">' . (int)$lesson['duration'] . ' мин</div>
                    <div class="schedule-lesson__trainer" title="' . $e($lesson['trainer']) . '">' . $e($lesson['trainer']) . '</div>
                    <div class="schedule-lesson__status schedule-lesson__status--' . $e($lesson['status']) . '" title="' . $e($status_labels[$lesson['status']]) . '">' . $e($status_labels[$lesson['status']]) . '</div>
                    <div class="schedule-lesson__menu">
                        <button type="button" class="schedule-lesson__menu-btn" aria-label="Действия" aria-expanded="false"' . ($is_done ? ' disabled' : '') . '>
                            <span class="schedule-dots"></span>
                        </button>' . $dropdown . '
                    </div>
                </div>';
    }, $lessons));
};

$empty_html = '<div class="schedule-empty">Расписание на день не заполнено.</div>';

// блок одного дня: список или заглушка
$render_day = function (string $ymd, $lessons) use ($render_lessons, $empty_html, $selected_ymd): string {
    $html = $render_lessons($lessons);

    return '<div class="schedule-day-content" data-date="' . $ymd . '"' . ($ymd === $selected_ymd ? '' : ' hidden') . '>'
        . ($html !== '' ? '<div class="schedule-lessons">' . $html . '</div>' : $empty_html)
        . '</div>';
};

$lessons_block = '';
foreach ($lessons_by_date as $ymd => $day_lessons) {
    $lessons_block .= $render_day($ymd, $day_lessons);
}

// заглушка для всех дат, по которым данных нет; показывается, когда подходящего блока не нашлось
$lessons_block .= '<div class="schedule-day-content schedule-day-content--fallback" data-date="" hidden>' . $empty_html . '</div>';

$data['schedule_content'] = '
        <div class="schedule-top-text">Создайте расписание и поддерживайте его в актуальном состоянии, чтобы пользователи могли записаться к вам на занятие.</div><br>
        <div class="schedule-top-text">Для корректного отображения расписания заполните все блоки раздела “Редактировать информацию”.</div>
 
        <div class="schedule-card"> 
            <div class="schedule-month">
                <div class="schedule-month__title">' . $month_title($week_monday, $strip_days) . '</div>
                <button type="button" class="schedule-month__calendar" aria-label="Открыть календарь">
                    <span class="icon icon-calendar"></span>
                </button>
            </div>
 
            <div class="schedule-week" data-today="' . $today_ymd . '" data-start="' . $week_monday->format('Y-m-d') . '" data-selected="' . $selected_ymd . '" data-days="' . $strip_days . '">
                <button type="button" class="schedule-week__arrow" data-dir="-1" aria-label="Назад">
                    <span class="icon icon-arrow-left"><img src="/assets/img/cabinet/arrow-left-class.svg"></span>
                </button>
                <div class="schedule-week__days">' . $days_html . '
                </div>
                <button type="button" class="schedule-week__arrow" data-dir="1" aria-label="Вперёд">
                    <span class="icon icon-arrow-right"><img src="/assets/img/cabinet/arrow-right-class.svg"></span>
                </button>
            </div>
 
            <div class="schedule-actions">
                <button type="button" class="schedule-actions__btn schedule-actions__copy" data-action="copy-day">
                    <span class="icon icon-copy"></span>Копировать занятия дня
                </button>
                <button type="button" class="schedule-actions__btn schedule-actions__paste" data-action="paste-day" disabled>
                    <span class="icon icon-paste"></span>Вставить
                </button>
            </div>
 
            ' . $lessons_block . '

            <button type="button" class="schedule-add" data-action="add-lesson"><img src="/assets/img/cabinet/schedule-logo.svg"> Добавить занятие</button>

            <template id="schedule-row-tpl">' . $render_lessons([[
                'id' => 0, 'time' => '', 'title' => '', 'duration' => 0, 'trainer' => '', 'status' => 'open',
                'direction' => '', 'custom_title' => '', 'teacher' => '', 'hall' => '', 'level' => '',
            ]]) . '</template>
        </div>
';

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");
$data['schedule_popup'] .= <<<EOF
<input type="hidden" id="popup-forbidden-words" value="{$escaped_forbidden_words}">
EOF;

$data['schedule_popup'] .= '
<div class="popup schedule-popup" id="lesson-popup">
    <div class="bg"></div>
    <div class="window middle">
        <div class="close"></div>
        <div class="reg">
            <div id="lesson-popup-form">
                <span class="icon icon-lotus schedule-popup__logo"></span>
                <div class="name">Карточка занятия<span class="schedule-popup__date" id="lesson-popup-date"></span></div>

                <input type="hidden" id="popup-forbidden-words" value="' . $e(json_encode($popup_forbidden_words, JSON_UNESCAPED_UNICODE)) . '">
                <input type="hidden" name="lesson_id" value="">
                <input type="hidden" name="date" value="' . $selected_ymd . '">

                <div class="popup-body">
                    <div class="field">
                        <div class="field-name big">Статус занятия</div>
                        <select class="field-input" name="status">' . $opts($popup_statuses, 'Статус') . '</select>
                    </div>

                    <div class="field">
                        <div class="field-name big">Начало и длительность</div>
                        <div class="schedule-popup__row">
                            <select class="field-input" name="time">' . $opts($popup_times, '10:00') . '</select>
                            <select class="field-input" name="duration">' . $opts($popup_durations, '15 мин') . '</select>
                        </div>
                    </div>

                    <div class="field">
                        <div class="field-name big">Направление занятия</div>
                        <select class="field-input" name="direction">' . $opts($popup_directions, 'Направление') . '<option value="custom">Нет в списке</option></select>

                        <div class="schedule-popup__custom" hidden>
                            <div class="schedule-popup__hint">Не нашли нужное направление? Введите направление / название занятия в поле ниже.</div>
                            <input type="text" class="field-input" name="custom_title" maxlength="30" placeholder="Введите название (до 30 символов)" autocomplete="off">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="field">
                        <div class="field-name big">Преподаватель</div>
                        <select class="field-input" name="teacher">' . $opts($popup_teachers, 'Преподаватель (из блока “Команда”)') . '</select>
                    </div>

                    <div class="field">
                        <div class="field-name big">Зал для занятий</div>
                        <select class="field-input" name="hall">' . $opts($popup_halls, 'Зал (из блока “Залы”)') . '</select>
                    </div>

                    <div class="field">
                        <div class="field-name big">Уровень подготовки</div>
                        <select class="field-input" name="level">' . $opts($popup_levels, 'Уровень') . '</select>
                    </div>

                    <div class="button">
                        <button type="button" id="lesson-popup-submit" class="popup-btn" disabled>Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

$data = array_filter(
    $data,
    fn($value, $key) => (is_scalar($value) || $value === null)
        && substr_count($HTML, "<!--data:$key-->") >= 2,
    ARRAY_FILTER_USE_BOTH
);

$HTML = html_explode($data, $HTML);
