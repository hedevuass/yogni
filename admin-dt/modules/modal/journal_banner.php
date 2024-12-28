<div class="modal fade" id="modalEditJournalbanner" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $banner['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                <div class="col-3 position-relative">
                        <div class="image-container">
                            <img src="/assets/img/<?= htmlspecialchars($banner['image']) ?>" class="avatar" alt="menu-<?= htmlspecialchars($journal['id']) ?>">
                            <span class="upload-avatar">Изменить</span>
                        </div>
                        <input type="file" style="display: none" name="photo" id="input-img">
                    </div>
                    <div class="col-9">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $banner['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Название</label>
                            <input type="text" name="title" class="form-control" id="input-title" value="<?= $banner['title'] ?? '' ?>" require>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="input-content" class="form-label">Контент</label>
                            <textarea name="content" class="form-control" id="input-content" required><?= ($banner['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="input-description" class="form-label">Ссылка</label>
                            <input type="text" name="alias" class="form-control" id="input-description" value="<?= $banner['link'] ?>" require>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="bannerJournalEdit">Редактировать блок</button>
            </div>
        </div>
    </div>
</div>