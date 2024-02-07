<div class="modal fade" id="modalEditbanner" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $banner['name'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $banner['id'] ?? '' ?>">
                <div class="mb-3">
                    <label for="input-name" class="form-label">Название баннера</label>
                    <input type="text" name="name" class="form-control" id="input-name" value="<?= $banner['name'] ?? '' ?>" require>
                </div>
                <?php if (isset($slides)):
                    $i = 1;
                    foreach ($slides as $slide): ?>
                    
                        <div class="mb-3">
                            <h5 class="mb-3">Слайд №<?= $i ?></h5>
                            <label for="input-name" class="form-label">Заголовок</label>
                            <input type="text" name="slide-name" class="form-control" id="input-slide-name" value="<?= $slide['title'] ?? '' ?>" require>
                        </div>
                    <?php $i++; endforeach;
                endif; ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="bannerEdit">Редактировать блок</button>
            </div>
        </div>
    </div>
</div>