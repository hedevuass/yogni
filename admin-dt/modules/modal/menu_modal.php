<div class="modal fade" id="modalAddMenu" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $menu_item['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="row mb-3">
                        <div class="col-3 mb-3 position-relative">
                            <div class="image-container">
                                <img src="<?= htmlspecialchars($menu_item['img']) ?>" class="avatar"
                                    alt="menu-<?= htmlspecialchars($menu_item['id']) ?>">
                                <span class="upload-avatar">Изменить</span>
                            </div>
                            <input type="file" style="display: none" name="photo" id="input-img">
                        </div>
                        <div class="col-9">
                            <input type="hidden" name="id" class="form-control" id="input-id"
                                value="<?= $menu_item['id'] ?? '' ?>">
                            <div class="mb-3">
                                <label for="input-title" class="form-label">Заголовок</label>
                                <input type="text" name="title" class="form-control" id="input-title"
                                    value="<?= $menu_item['title'] ?? '' ?>" require>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="input-content" class="form-label">Описание</label>
                            <textarea name="content" class="form-control" id="input-content"
                                required><?= htmlspecialchars_decode($menu_item['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="input-classification" class="form-label">Классификация</label>
                            <select name="classification_id" class="form-select" id="input-classification">
                                <option value="" disabled <?= empty($menu_item['classification']) ? 'selected' : '' ?>>
                                    Выберите классификацию
                                </option>
                                <?php foreach ($classifications as $classification): ?>
                                    <option value="<?= $classification['id'] ?>"
                                        <?= ($category ?? $menu_item['classification']) == $classification['id'] ? 'selected' : '' ?>>
                                        <?php
                                        // Проверка, если ID равен 1, отображаем "Юристы и адвокаты"
                                        echo $classification['id'] == 1 ? 'Юристы и адвокаты' : htmlspecialchars($classification['title']);
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="input-parent" class="form-label">Родитель</label>
                            <!-- <select name="parent_group" class="form-select" id="parent-group-selector">
                                <option value="lawyers">Юристы и адвокаты</option>
                                <option value="experts">Эксперты</option>
                                <option value="notaries">Нотариусы</option>
                            </select> -->

                            <!-- Родительский элемент -->
                            <select name="p_id" class="form-select" id="child-items-selector">
                                <option value="0" <?= $menu_item['p_id'] == 0 ? 'selected' : '' ?>>Нет родителя</option>

                                <!-- Группа Юристов -->
                                <optgroup label="Юристы и адвокаты">
                                    <?php foreach ($menu_items as $parent_item): ?>
                                        <?php if (in_array($parent_item['classification_id'], [1, 2]) && $parent_item['p_id'] == 0): ?>
                                            <!-- Родитель -->
                                            <option class="group-lawyers" value="<?= $parent_item['id'] ?>"
                                                <?= $menu_item['p_id'] == $parent_item['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($parent_item['title']) ?>
                                            </option>
                                            <!-- Подкатегории -->
                                            <?php foreach ($menu_items as $child_item): ?>
                                                <?php if ($child_item['p_id'] == $parent_item['id']): ?>
                                                    <option class="group-lawyers" value="<?= $child_item['id'] ?>"
                                                        <?= $menu_item['p_id'] == $child_item['id'] ? 'selected' : '' ?>>
                                                        &nbsp;&nbsp;&nbsp;<?= htmlspecialchars($child_item['title']) ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>

                                <!-- Группа Экспертов -->
                                <optgroup label="Эксперты">
                                    <?php foreach ($menu_items as $parent_item): ?>
                                        <?php if ($parent_item['classification_id'] == 3 && $parent_item['p_id'] == 0): ?>
                                            <!-- Родитель -->
                                            <option class="group-experts" value="<?= $parent_item['id'] ?>"
                                                <?= $menu_item['p_id'] == $parent_item['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($parent_item['title']) ?>
                                            </option>
                                            <!-- Подкатегории -->
                                            <?php foreach ($menu_items as $child_item): ?>
                                                <?php if ($child_item['p_id'] == $parent_item['id']): ?>
                                                    <option class="group-experts" value="<?= $child_item['id'] ?>"
                                                        <?= $menu_item['p_id'] == $child_item['id'] ? 'selected' : '' ?>>
                                                        &nbsp;&nbsp;&nbsp;<?= htmlspecialchars($child_item['title']) ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>

                                <!-- Группа Нотариусов -->
                                <optgroup label="Нотариусы">
                                    <?php foreach ($menu_items as $parent_item): ?>
                                        <?php if ($parent_item['classification_id'] == 4 && $parent_item['p_id'] == 0): ?>
                                            <!-- Родитель -->
                                            <option class="group-notaries" value="<?= $parent_item['id'] ?>"
                                                <?= $menu_item['p_id'] == $parent_item['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($parent_item['title']) ?>
                                            </option>
                                            <!-- Подкатегории -->
                                            <?php foreach ($menu_items as $child_item): ?>
                                                <?php if ($child_item['p_id'] == $parent_item['id']): ?>
                                                    <option class="group-notaries" value="<?= $child_item['id'] ?>"
                                                        <?= $menu_item['p_id'] == $child_item['id'] ? 'selected' : '' ?>>
                                                        &nbsp;&nbsp;&nbsp;<?= htmlspecialchars($child_item['title']) ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="input-slug" class="form-label">Ссылка</label>
                            <input type="text" name="slug" class="form-control" id="input-alias"
                                value="<?= $menu_item['slug'] ?>" require>
                        </div>

                        <div class="mb-3">
                            <label for="input-meta-title" class="form-label">Title страницы</label>
                            <input type="text" name="meta_title" id="input-meta-title" class="form-control"
                                value="<?= htmlspecialchars($menu_item['meta_title'] ?? '') ?>"
                                placeholder="Введите заголовок страницы">
                        </div>

                        <div class="mb-3">
                            <label for="input-meta-description" class="form-label">Description страницы</label>
                            <textarea name="meta_description" id="input-meta-description" class="form-control" rows="3"
                                placeholder="Введите описание страницы"><?= htmlspecialchars($menu_item['meta_description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="input-meta-keywords" class="form-label">Ключевые слова</label>
                            <input type="text" name="meta_keywords" id="input-meta-keywords" class="form-control"
                                value="<?= htmlspecialchars($menu_item['meta_keywords'] ?? '') ?>"
                                placeholder="Введите ключевые слова">
                        </div>

                        <div class="mb-3">
                            <div class="image-container">
                                <img style="width: 30px; margin: 10px 20px 10px 0"
                                    src="../assets/img/<?= htmlspecialchars($menu_item['icon']) ?>" class="icon"
                                    alt="menu-<?= htmlspecialchars($menu_item['id']) ?>">
                                <span class="btn btn-sm btn-primary upload-icon">Изменить иконку</span>
                                <span class="btn btn-sm btn-danger remove-icon">Удалить иконку</span>
                            </div>
                            <input type="file" style="display: none" name="icon" id="input-icon">
                        </div>
                        <hr>
                        <div class="d-flex gap-1 justify-content-start align-items-center mb-2">
                            <h5 class="mb-0">Альтернативные страницы</h5>
                            <button type="button" class="btn btn-success btn-sm add-alternative">Добавить</button>
                            <input type="hidden" class="alternative-original-id" id="menu-id" value="<?= $menu_id ?>">
                            <input type="hidden" class="alternative-classification" id="menu-classification"
                                value="<?= $menu_item['classification'] ?>">
                            <input type="hidden" class="alternative-p_id" id="menu-p-id"
                                value="<?= $menu_item['p_id'] ?>">
                            <input type="hidden" class="type" id="type" value="service">
                        </div>

                        <!-- Контейнер для альтернативных страниц -->
                        <div id="alternatives-container">
                            <?php if (!empty($alternatives)): ?>
                                <?php foreach ($alternatives as $index => $alternative): ?>
                                    <div class="alternative-row bg-light border rounded p-3 mb-2 d-flex justify-content-between align-items-center w-100"
                                        data-id="<?= $alternative['id'] ?>">
                                        <span class="fw-bold"><?= htmlspecialchars($alternative['title']) ?></span>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary edit-alternative"
                                                data-id="<?= $alternative['id'] ?>">Редактировать</button>
                                            <button type="button" class="btn btn-sm btn-danger delete-alternative"
                                                data-type="service" data-id="<?= $alternative['id'] ?>">Удалить</button>
                                        </div>
                                    </div>
                                    <!-- Скрытая форма редактирования -->
                                    <div class="alternative-edit-form mt-2 p-2 border rounded bg-white w-100 d-none"
                                        data-parent-id="<?= $alternative['id'] ?>">
                                        <input type="hidden" class="alternative-classification"
                                            value="<?= $menu_item['classification'] ?>">
                                        <input type="hidden" class="alternative-p_id" value="<?= $menu_item['p_id'] ?>">
                                        <input type="hidden" class="type" id="type" value="service">

                                        <div class="mb-2">
                                            <label>Заголовок</label>
                                            <input type="text" class="form-control alternative-title-input"
                                                value="<?= htmlspecialchars($alternative['title']) ?>">
                                        </div>

                                        <div class="mb-2">
                                            <label>Описание</label>
                                            <textarea id="alternative-description-<?= $index ?>"
                                                class="form-control alternative-description-input"><?= htmlspecialchars_decode($alternative['description'] ?? '') ?></textarea>
                                        </div>

                                        <?php
                                        // 1) Базовый домен
                                        $domain = 'https://urfaq.ru';

                                        // 2) Определяем, откуда брать classification и p_id
                                        $alt_class = $alternative['classification'] ?? $menu_item['classification'];
                                        $alt_p_id = $alternative['p_id'] ?? $menu_item['p_id'];

                                        // 3) Корневой сегмент по classification
                                        switch ((int) $alt_class) {
                                            case 1:
                                            case 2:
                                            case 5:
                                            case 6:
                                            case 7:
                                                $root = 'yuristy-i-advokaty';
                                                break;
                                            case 3:
                                            case 8:
                                                $root = 'experty';
                                                break;
                                            case 4:
                                                $root = 'notariusy';
                                                break;
                                            default:
                                                $root = '';
                                        }

                                        // 4) Если есть родитель — добавляем его slug после корневого сегмента
                                        if (!empty($alt_p_id)) {
                                            $parent = get_service((int) $alt_p_id);
                                            $parent_slug = $parent['slug'] ?? '';
                                            $prefix = "{$domain}/{$root}/{$parent_slug}/";
                                        } else {
                                            // иначе только корневой сегмент
                                            $prefix = "{$domain}/{$root}/";
                                        }
                                        ?>

                                        <div class="mb-2">
                                            <label class="form-label">Ссылка</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><?= htmlspecialchars($prefix) ?></span>
                                                <input type="text" class="form-control alternative-slug-input"
                                                    value="<?= htmlspecialchars($alternative['slug'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <label>Title страницы</label>
                                            <input type="text" class="form-control alternative-meta-title-input"
                                                value="<?= htmlspecialchars($alternative['meta_title'] ?? '') ?>">
                                        </div>

                                        <div class="mb-2">
                                            <label>Description страницы</label>
                                            <input type="text" class="form-control alternative-meta-description-input"
                                                value="<?= htmlspecialchars($alternative['meta_description'] ?? '') ?>">
                                        </div>

                                        <div class="mb-2">
                                            <label>Ключевые слова</label>
                                            <input type="text" class="form-control alternative-meta-keywords-input"
                                                value="<?= htmlspecialchars($alternative['meta_keywords'] ?? '') ?>">
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-secondary btn-sm close-alternative">Закрыть</button>
                                            <button class="btn btn-success btn-sm ms-2 save-alternative" data-type="service"
                                                data-id="<?= $alternative['id'] ?>">Сохранить</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Альтернативных страниц пока нет.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary edit" data-id="<?= $menu_id ?? '' ?>" id="menuEdit">
                    <?= isset($menu_id) ? 'Сохранить' : 'Добавить' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    /* document.getElementById('parent-group-selector').addEventListener('change', function(e) {
        const selectedGroup = e.target.value;


        const childSelect = document.getElementById('child-items-selector');
        childSelect.classList.remove('hidden');


        childSelect.querySelectorAll('option').forEach(option => {
            if (option.classList.contains(`group-${selectedGroup}`)) {
                option.classList.remove('hidden');
            } else {
                option.classList.add('hidden');
            }
        });


        const firstVisibleOption = childSelect.querySelector(`.group-${selectedGroup}:not(.hidden)`);
        if (firstVisibleOption) {
            childSelect.value = firstVisibleOption.value;
        } else {
            childSelect.value = '0';
        }
    }); */
</script>