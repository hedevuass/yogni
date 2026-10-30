<div class="modal fade" id="modalAddMenu" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $service_parent['title'] ?? 'Новый элемент' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="serviceParentForm">
                    <input type="hidden" name="id" id="input-id" value="<?= $service_parent['id'] ?? '' ?>">

                    <div class="mb-3">
                        <label for="input-title" class="form-label">Заголовок</label>
                        <input type="text" name="title" class="form-control" id="input-title"
                            value="<?= $service_parent['title'] ?? '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="input-description" class="form-label">Описание</label>
                        <textarea name="description" class="form-control" id="input-content"
                            required><?= htmlspecialchars_decode($service_parent['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="input-meta_title" class="form-label">Title страницы</label>
                        <input type="text" name="meta_title" class="form-control" id="input-meta-title"
                            value="<?= $service_parent['meta_title'] ?? '' ?>">
                    </div>

                    <div class="mb-3">
                        <label for="input-meta_description" class="form-label">Description страницы</label>
                        <textarea name="meta_description" class="form-control"
                            id="input-meta-description"><?= htmlspecialchars_decode($service_parent['meta_description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="input-meta_keywords" class="form-label">Ключевые слова</label>
                        <input type="text" name="meta_keywords" class="form-control" id="input-meta_keywords"
                            value="<?= $service_parent['meta_keywords'] ?? '' ?>">
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
                </form>
                <hr>
                <div class="d-flex gap-1 justify-content-start align-items-center mb-2">
                    <h5 class="mb-0 mr-2">Альтернативные страницы</h5>
                    <button type="button" class="btn btn-success btn-sm add-alternative">Добавить</button>
                    <input type="hidden" class="alternative-original-id" id="menu-id"
                        value="<?= $service_parent['id'] ?>">
                    <input type="hidden" class="type" id="type" value="page">
                </div>

                <!-- Контейнер для альтернативных страниц -->
                <div id="alternatives-container">
                    <?php if (!empty($alternatives)): ?>
                        <?php foreach ($alternatives as $alternative): ?>
                            <div class="alternative-row bg-light border rounded p-3 mb-2 d-flex justify-content-between align-items-center w-100"
                                data-id="<?= $alternative['id'] ?>">
                                <span class="fw-bold"><?= htmlspecialchars($alternative['title']) ?></span>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary edit-alternative"
                                        data-id="<?= $alternative['id'] ?>">Редактировать</button>
                                    <button type="button" class="btn btn-sm btn-danger delete-alternative" data-type="page"
                                        data-id="<?= $alternative['id'] ?>">Удалить</button>
                                </div>
                            </div>
                            <!-- Скрытая форма редактирования -->
                            <div class="alternative-edit-form mt-2 p-2 border rounded bg-white w-100 d-none"
                                data-parent-id="<?= $service_parent['id'] ?>">
                                <input type="hidden" class="alternative-original-id" value="<?= $service_parent['id'] ?>">
                                <input type="hidden" class="type" id="type" value="page">
                                <div class="mb-2">
                                    <label>Заголовок</label>
                                    <input type="text" class="form-control alternative-title-input"
                                        value="<?= htmlspecialchars($alternative['title']) ?>">
                                </div>
                                <div class="mb-2">
                                    <label>Описание</label>
                                    <textarea
                                        class="form-control alternative-description-input"><?= htmlspecialchars_decode($alternative['description'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Ссылка</label>
                                    <div class="input-group">
                                        <span class="input-group-text">https://yogoort.ru/</span>
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
                                    <button class="btn btn-success btn-sm ms-2 save-alternative" data-type="page"
                                        data-id="<?= $alternative['id'] ?>">Сохранить</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Альтернативных страниц пока нет.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary editParent"
                    data-slug="<?= $service_parent['slug'] ?? '' ?>" id="menuEditParent">
                    Сохранить
                </button>
            </div>
        </div>
    </div>
</div>