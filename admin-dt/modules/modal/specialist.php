<div class="modal fade" id="modalAddSpecialist" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= isset($specialist['id']) ? 'Редактировать специалиста' : 'Добавить специалиста' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $specialist['id'] ?? '' ?>">
                <div class="row mb-3">
                    <div class="col-3 position-relative">
                        <div class="image-container">
                            <img src="<?= $specialist['photo'] ?? '' ?>" class="avatar" alt="specialist-<?= $specialist['id'] ?? ''?>">
                            <span class="upload-avatar">Изменить</span>
                        </div>
                        <input type="file" style="display: none" name="photo" id="input-photo">
                    </div>
                    <div class="col-9">
                        <div class="mb-3">
                            <label for="input-name" class="form-label">Имя</label>
                            <input type="text" name="name" class="form-control" id="input-name" value="<?= $specialist['name'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-lastname" class="form-label">Фамилия</label>
                            <input type="text" name="lastname" class="form-control" id="input-lastname" value="<?= $specialist['lastname'] ?? '' ?>" require>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="input-type" class="form-label">Город</label>
                    <select name="city" class="form-select" aria-label="Выберите город" id="input-city_id">
                        <?php $cities = $cities ?? [];
                        $specialist['city_id'] = $specialist['city_id'] ?? false;

                        foreach ($cities as $city): ?>
                            <option value="<?= $city['id'] ?>" <?= $city['id'] == $specialist['city_id'] ? 'selected' : '' ?>><?= $city['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="input-email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="input-email" value="<?= $specialist['email'] ?? '' ?>"
                           aria-describedby="emailHelp" require <?= !$specialist['email'] ?: 'disabled' ?>>
                </div>
                <div class="mb-3">
                    <label for="input-phone" class="form-label">Телефон</label>
                    <input type="tel" name="phone" class="form-control" maxlength="13" id="input-phone" value="<?= $specialist['phone'] ?? '' ?>" require>
                </div>
                <div class="mb-3">
                    <label for="input-password" class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" id="input-password">
                </div>
                <div class="mb-3">
                    <label for="input-type" class="form-label">Классификация</label>
                    <input type="hidden" name="type" value="<?= $specialist['type'] ?? 1 ?>" id="input-type">
                    <select name="classification_id" class="form-select" aria-label="Выберите классификацию" id="input-classification_id">
                        <?php $classifications = $classifications ?? [];
                        $specialist['classification_id'] = $specialist['classification_id'] ?? false;

                        foreach ($classifications as $classification):?>
                            <option value="<?= $classification['id'] ?>" data-type="<?= $classification['type'] ?>" <?= $classification['id'] == $specialist['classification_id'] ? 'selected' : '' ?>><?= $classification['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3" id="input-company" <?= ($specialist['type'] == 2) ?: 'style="display:none;"' ?>>
                    <label for="input-company" class="form-label">Название компании</label>
                    <input type="text" name="company-name" class="form-control" value="<?= $specialist['company'] ?? '' ?>" id="input-company_name">
                    <input type="hidden" name="company-id" value="<?= $specialist['company_id'] ?>" id="input-company_id">
                </div>

                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="input-active" checked>
                    <label class="form-check-label" for="input-active">
                        Активировать специалиста
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="specialistAdd"><?= isset($specialist['id']) ? 'Редактировать специалиста' : 'Добавить специалиста' ?></button>
            </div>
        </div>
    </div>
</div>