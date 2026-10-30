<div class="modal fade" id="modalEditRequest" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= htmlspecialchars($request['title'] ?? '') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $request['id'] ?? '' ?>">
                <input type="hidden" name="type" class="form-control" id="input-type" value="<?= $type ?? '' ?>">

                <div class="mb-3">
                    <label for="input-title" class="form-label">Заголовок</label>
                    <input type="text" name="title" class="form-control" id="input-title" value="<?= htmlspecialchars($request['title'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-description" class="form-label">Описание</label>
                    <textarea name="description" class="form-control" id="input-description" rows="3" required><?= htmlspecialchars($request['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="input-price" class="form-label">Цена</label>
                    <input type="text" name="price" class="form-control" id="input-price" value="<?= htmlspecialchars($request['price'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-promo_description" class="form-label">Описание акции</label>
                    <input type="text" name="promo_description" class="form-control" id="input-promo_description" value="<?= htmlspecialchars($request['promo_description'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-comment_text" class="form-label">Текст комментария</label>
                    <input type="text" name="comment_text" class="form-control" id="input-comment_text" value="<?= htmlspecialchars($request['comment_text'] ?? '') ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="requestEdit">Сохранить</button>
            </div>
        </div>
    </div>
</div>