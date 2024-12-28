<div class="modal fade" id="modalEditService" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $service['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-3 position-relative">
                        <div class="image-container">
                            <img src="<?= htmlspecialchars($service['img']) ?>" class="avatar" alt="service-<?= htmlspecialchars($service['id']) ?>">
                            <span class="upload-avatar">Изменить</span>
                        </div>
                        <input type="file" style="display: none" name="photo" id="input-img">
                    </div>
                    <div class="col-9">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $service['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Название</label>
                            <input type="text" name="title" class="form-control" id="input-title" value="<?= $service['title'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-description" class="form-label">Ссылка</label>
                            <input type="text" name="slug" class="form-control" id="input-slug" value="<?= $service['slug'] ?>" require>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="serviceEdit">Сохранить</button>
            </div>
        </div>
    </div>
</div>