<?php
$menu_items = get_menu_items();

$all_service_parrent = get_all_service_parents();

// Группируем элементы по p_id и classification
$sorted_items = [];
$category_slugs = [
    'yuristy-i-advokaty' => [1, 2, 5, 6, 7],
    'experty'            => [3, 8],
    'notariusy'          => [4],
];

// Перевернём, чтобы получить из slug заголовок
$categories = [];
$category_id_mapping = [];

foreach ($category_slugs as $slug => $classifications) {
    // Берём title из $all_service_parrent, если нет — fallback на slug
    $title = $all_service_parrent[$slug]['page_title'] ?? $all_service_parrent[$slug]['title'] ?? $slug;

    $categories[$title] = $classifications;

    // Для маппинга — берем первый classification из списка
    $category_id_mapping[$title] = $classifications[0];
}

// Определяем категорию для каждого элемента
foreach ($menu_items as $item) {
    foreach ($categories as $category_name => $classification_ids) {
        if (in_array($item['classification_id'], $classification_ids)) {
            $sorted_items[$category_name][$item['p_id']][] = $item;
            break;
        }
    }
}

// Гарантируем, что каждая категория присутствует в $sorted_items
foreach ($categories as $category_name => $classification_ids) {
    if (!isset($sorted_items[$category_name])) {
        $sorted_items[$category_name] = []; // Если нет элементов, создаем пустой массив
    }
}

// Функция для рекурсивного вывода элементов с вложенностью
function renderMenuItems($parent_id, $sorted_items, $category_name, $level = 0)
{
    if (!isset($sorted_items[$parent_id])) {
        return;
    }

    foreach ($sorted_items[$parent_id] as $item) {
        // Определим отступ для всей строки
        $padding_left = $level * 50; // Можно Редактировать значение для нужного отступа
        ?>
        <tr>
            <td style="padding-left: <?= $padding_left ?>px;">
                <?= $item['title'] ?>
            </td>
            <td><?= $item['slug'] ?: "-" ?></td>
            <td><?= $item['parent_title'] ?: "-" ?></td>
            <td>
                <div class="btn-group" role="group">
                    <button type="submit" class="btn modalAdd btn-info" data-title="<?= $item['title'] ?>"
                        data-content="<?= $item['content'] ?? '' ?>" data-id="<?= $item['id'] ?>">
                        Редактировать
                    </button>
                    <button type="submit" class="btn btn-danger delete" data-id="<?= $item['id'] ?>">
                        Удалить
                    </button>
                </div>
            </td>
        </tr>
        <?php
        // Рекурсивно выводим дочерние элементы с увеличенным отступом
        renderMenuItems($item['id'], $sorted_items, $category_name, $level + 1);
    }
}
?>

<div id="menuPage" class="wrapper">
<?php $index = 0; ?>
<?php foreach ($sorted_items as $category_name => $items): ?>
    <div class="d-flex justify-content-center gap-3 <?= $index !== 0 ? 'mt-5' : '' ?> mb-5">
            <p class="h1 mb-0">
                <?= $category_name ?>
            </p>
            <div class="btn-group">
                <?php
                $page_id++;
                // Определяем slug в зависимости от категории
                $slug = '';
                if (in_array($category_id_mapping[$category_name], [1, 2, 5, 6, 7])) {
                    $slug = 'yuristy-i-advokaty';
                } elseif (in_array($category_id_mapping[$category_name], [3, 8])) {
                    $slug = 'experty';
                } elseif (in_array($category_id_mapping[$category_name], [4])) {
                    $slug = 'notariusy';
                }
                ?>
                <button type="button" class="btn btn-success modalAdd" data-page-id="<?= $page_id?>" data-slug="<?= $slug ?>">
                    Редактировать раздел
                </button>
                <button type="button" class="btn btn-info modalAdd"
                    data-category="<?= $category_id_mapping[$category_name] ?>">
                    Добавить раздел
                </button>
            </div>
        </div>


        <div class="table-responsive">
            <div class="container-fluid">
                <table id="<?= $category_name[$parent_id] ?>" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <td style="width: 30%">Заголовок</td>
                            <td style="width: 30%">Ссылка</td>
                            <td style="width: 25%">Родитель</td>
                            <td style="width: 15%">Действие</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php renderMenuItems(0, $items, $category_name); ?>
                        <?php else: ?>
                            <tr>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php $index++; ?>
    <?php endforeach; ?>
</div>