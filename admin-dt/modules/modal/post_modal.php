<div class="modal fade" id="modalEditPosts" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $posts['name'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $posts['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Название</label>
                            <input type="text" name="name" class="form-control" id="input-name" value="<?= $posts['name'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Контент</label>
                            <textarea name="content" class="form-control" id="input-content" required><?= htmlspecialchars($posts['content'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="input-description" class="form-label">Ссылка</label>
                            <input type="text" name="alias" class="form-control" id="input-alias" value="<?= $posts['alias'] ?>" require>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="faqPagesEdit">Редактировать вопрос</button>
            </div>
        </div>
    </div>
</div>