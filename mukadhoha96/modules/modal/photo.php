<div class="modal fade" id="modalEditPhoto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= htmlspecialchars_decode($pro_photo['title']) ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $pro_photo['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Заголовок</label>
                            <input type="text" name="title" class="form-control" id="input-name" value="<?= $pro_photo['title'] ?? '' ?>" require>
                        </div>
                        <?php if ($pro_photo['id'] == '4') {
                        ?>
                            <div class="mb-3">
                                <label for="input-description" class="form-label">Цена</label>
                                <input type="text" name="price" class="form-control" id="input-price" value="<?= $pro_photo['price'] ?>" require>
                            </div>
                        <?php
                        } else {
                        ?>
                            <div class="mb-3">
                                <label for="input-description" class="form-label">Цена</label>
                                <input type="text" name="price_old" class="form-control" id="input-old-price" value="<?= $pro_photo['price_old'] ?>" require>
                            </div>
                            <div class="mb-3">
                                <label for="input-description" class="form-label">Цена со скидкой</label>
                                <input type="text" name="price" class="form-control" id="input-price" value="<?= $pro_photo['price'] ?>" require>
                            </div>
                        <?php
                        }
                        ?>
                        <div class="mb-3">
                            <label for="input-description" class="form-label">Длительность</label>
                            <input type="text" name="time" class="form-control" id="input-alias" value="<?= $pro_photo['time'] ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-description" class="form-label">Кол-во фото</label>
                            <input type="text" name="number" class="form-control" id="input-alias" value="<?= $pro_photo['number'] ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Описание</label>
                            <textarea name="description" class="form-control" id="input-content" required><?= ($pro_photo['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="input-popup-line1" class="form-label">Описание в попапе</label>
                            <input type="text" name="popup_line1" class="form-control"
                                id="input-popup-line1"
                                value="<?= !empty($pro_photo['popup_description'])
                                            ? (json_decode($pro_photo['popup_description'], true)['line1'] ?? '')
                                            : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label for="input-popup-line2" class="form-label">Кол-во фото в попапе</label>
                            <input type="text" name="popup_line2" class="form-control"
                                id="input-popup-line2"
                                value="<?= !empty($pro_photo['popup_description'])
                                            ? (json_decode($pro_photo['popup_description'], true)['line2'] ?? '')
                                            : '' ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="photoEdit">Сохранить</button>
            </div>
        </div>
    </div>
</div>