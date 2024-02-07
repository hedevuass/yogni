<div class="modal fade" id="modalEditPromotion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $promotion['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $promotion['id'] ?? '' ?>">
                <input type="hidden" name="type" class="form-control" id="input-type" value="<?= $type ?? '' ?>">
                <div class="mb-3">
                    <label for="input-price" class="form-label">Цена промо-опции</label>
                    <input type="number" name="price" class="form-control" id="input-price" value="<?= $promotion['price'] ?? '' ?>" require>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="promotionEdit">Редактировать промо-опцию</button>
            </div>
        </div>
    </div>
</div>