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
                        <div class="col-3 position-relative">
                            <div class="image-container">
                                <img src="<?= htmlspecialchars($menu_item['img']) ?>" class="avatar" alt="menu-<?= htmlspecialchars($menu_item['id']) ?>">
                                <span class="upload-avatar">Изменить</span>
                            </div>
                            <input type="file" style="display: none" name="photo" id="input-img">
                        </div>
                        <div class="col-9">
                            <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $menu_item['id'] ?? '' ?>">
                            <div class="mb-3">
                                <label for="input-title" class="form-label">Название</label>
                                <input type="text" name="title" class="form-control" id="input-name" value="<?= $menu_item['title'] ?? '' ?>" require>
                            </div>
                            <div class="mb-3">
                                <label for="input-description" class="form-label">Ссылка</label>
                                <input type="text" name="slug" class="form-control" id="input-alias" value="<?= $menu_item['slug'] ?>" require>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Контент</label>
                            <textarea name="content" class="form-control" id="input-content" required><?= htmlspecialchars($menu_item['description'] ?? '') ?></textarea>
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
                                            <option class="group-lawyers" value="<?= $parent_item['id'] ?>" <?= $menu_item['p_id'] == $parent_item['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($parent_item['title']) ?>
                                            </option>
                                            <!-- Подкатегории -->
                                            <?php foreach ($menu_items as $child_item): ?>
                                                <?php if ($child_item['p_id'] == $parent_item['id']): ?>
                                                    <option class="group-lawyers" value="<?= $child_item['id'] ?>" <?= $menu_item['p_id'] == $child_item['id'] ? 'selected' : '' ?>>
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
                                            <option class="group-experts" value="<?= $parent_item['id'] ?>" <?= $menu_item['p_id'] == $parent_item['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($parent_item['title']) ?>
                                            </option>
                                            <!-- Подкатегории -->
                                            <?php foreach ($menu_items as $child_item): ?>
                                                <?php if ($child_item['p_id'] == $parent_item['id']): ?>
                                                    <option class="group-experts" value="<?= $child_item['id'] ?>" <?= $menu_item['p_id'] == $child_item['id'] ? 'selected' : '' ?>>
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
                                            <option class="group-notaries" value="<?= $parent_item['id'] ?>" <?= $menu_item['p_id'] == $parent_item['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($parent_item['title']) ?>
                                            </option>
                                            <!-- Подкатегории -->
                                            <?php foreach ($menu_items as $child_item): ?>
                                                <?php if ($child_item['p_id'] == $parent_item['id']): ?>
                                                    <option class="group-notaries" value="<?= $child_item['id'] ?>" <?= $menu_item['p_id'] == $child_item['id'] ? 'selected' : '' ?>>
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
                            <label for="input-classification" class="form-label">Классификация</label>
                            <select name="classification_id" class="form-select" id="input-classification">
                                <option value="" disabled <?= empty($menu_item['classification']) ? 'selected' : '' ?>>Выберите классификацию</option>
                                <?php foreach ($classifications as $classification): ?>
                                    <option value="<?= $classification['id'] ?>" <?= $menu_item['classification'] == $classification['id'] ? 'selected' : '' ?>>
                                        <?php
                                        // Проверка, если ID равен 1, отображаем "Юристы и адвокаты"
                                        echo $classification['id'] == 1 ? 'Юристы и адвокаты' : htmlspecialchars($classification['title']);
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <? if ($menu_item['icon']) { ?>
                            <div class="mb-3">
                                <div class="image-container">
                                    <img style="width: 20px;" src="../assets/img/<?= htmlspecialchars($menu_item['icon']) ?>" class="icon" alt="menu-<?= htmlspecialchars($menu_item['id']) ?>">
                                    <span class="upload-icon">Изменить иконку</span>
                                </div>
                                <input type="file" style="display: none" name="icon" id="input-icon">
                            </div>
                        <? } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button"
                    class="btn btn-primary edit"
                    data-id="<?= $menu_id ?? '' ?>"
                    id="menuEdit">
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