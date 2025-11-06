<div class="modal fade" id="modalAddJournal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= htmlspecialchars_decode($journal['title'] ?? '') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-3 position-relative">
                        <div class="image-container">
                            <img src="<?= $journal['photo'] ?>" class="avatar" alt="menu-<?= $journal['id'] ?>">
                            <span class="upload-avatar">Изменить</span>
                        </div>
                        <input type="file" style="display: none" name="photo" id="input-img">
                    </div>
                    <div class="col-9">
                        <input type="hidden" name="id" class="form-control" id="input-id"
                            value="<?= $journal['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Заголовок</label>
                            <input type="text" name="title" class="form-control" id="input-title"
                                value="<?= $journal['title'] ?? '' ?>" require>
                        </div>
                    </div>
                    <div class="mb-3">
                            <label for="input-content" class="form-label">Описание</label>
                            <textarea name="content" class="form-control" id="input-content"
                                required><?= ($journal['content'] ?? '') ?></textarea>
                        </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="input-post-type" class="form-label">Тип</label>
                            <select name="post_type" class="form-select" id="input-post-type">
                                <option value="news" <?= $journal['post_type'] == 'news' ? 'selected' : '' ?>>Новость
                                </option>
                                <option value="articles" <?= $journal['post_type'] == 'articles' ? 'selected' : '' ?>>
                                    Статья</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="input-theme" class="form-label">Тема</label>
                            <select name="theme_id" class="form-select" aria-label="Выберите тему" id="input-theme_id">
                                <?php $journal_themes = $journal_themes ?? [];
                                $journal_theme_id = $journal['theme_id'] ?? false;

                                foreach ($journal_themes as $theme): ?>
                                    <option value="<?= $theme['id'] ?>" <?= $theme['id'] == $journal_theme_id ? 'selected' : '' ?>><?= $theme['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="input-timestamp" class="form-label">Дата и время</label>
                            <input type="datetime-local" name="timestamp" class="form-control"
                                value="<?= $journal['date'] ?? '' ?>" id="input-timestamp" required>
                        </div>
                        <div class="mb-3">
                            <label for="input-tags-title" class="form-label">Хештег</label>
                            <input type="text" name="tags_title" class="form-control" id="input-tags-title" required
                                value="<?= htmlspecialchars($journal['tags_title'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-site_from_name" class="form-label">Источник (название)</label>
                            <input type="text" name="site_from_name" class="form-control" id="input-site_from_name"
                                required value="<?= htmlspecialchars($journal['site_from_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-site_from_link" class="form-label">Источник (ссылка)</label>
                            <input type="text" name="site_from_link" class="form-control" id="input-site_from_link"
                                required value="<?= htmlspecialchars($journal['site_from_link'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-description" class="form-label">Ссылка</label>
                            <input type="text" name="alias" class="form-control" id="input-alias"
                                value="<?= $journal['alias'] ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-meta-title" class="form-label">Title страницы</label>
                            <input type="text" name="meta-title" class="form-control" id="input-meta-title"
                                value="<?= $journal['meta_title'] ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-meta-description" class="form-label">Desciption страницы</label>
                            <input type="text" name="meta-description" class="form-control" id="input-meta-description"
                                value="<?= $journal['meta_description'] ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-meta-keywords" class="form-label">Ключевые слова</label>
                            <input type="text" name="meta-keywords" class="form-control" id="input-meta-keywords"
                                value="<?= $journal['meta_keywords'] ?>" require>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary edit"
                    id="journalPagesEdit"><?= isset($journal['id']) ? 'Сохранить' : 'Добавить' ?></button>
            </div>
        </div>
    </div>
</div>