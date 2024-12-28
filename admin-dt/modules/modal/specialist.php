<style>
    .tab-pane:not(.active) {
        display: none !important;
    }
</style>
<div class="modal fade" id="modalAddSpecialist" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= isset($specialist_id) ? 'Редактировать специалиста' : 'Добавить специалиста' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <ul class="nav nav-tabs mb-3" id="specialistTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-basic" data-bs-toggle="tab" data-bs-target="#content-basic" type="button" role="tab" aria-controls="content-basic" aria-selected="true">Специалист</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-schedule" data-bs-toggle="tab" data-bs-target="#content-schedule" type="button" role="tab" aria-controls="content-schedule" aria-selected="false">Расписание</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-documents" data-bs-toggle="tab" data-bs-target="#content-documents" type="button" role="tab" aria-controls="content-documents" aria-selected="false">Документы</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-services" data-bs-toggle="tab" data-bs-target="#content-services" type="button" role="tab" aria-controls="content-services" aria-selected="false">Услуги</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-additional" data-bs-toggle="tab" data-bs-target="#content-additional" type="button" role="tab" aria-controls="content-additional" aria-selected="false">Дополнительно</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-compan" data-bs-toggle="tab" data-bs-target="#content-company" type="button" role="tab" aria-controls="content-compan" aria-selected="false">Компания</button>
                    </li>
                </ul>
                </ul>

                <div class="tab-content" id="specialistTabContent">
                    <!-- Основной контент -->
                    <div class="tab-pane fade show active" id="content-basic" role="tabpanel" aria-labelledby="tab-basic">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $specialist_id ?? '' ?>">
                        <div class="row mb-3">
                            <div class="col-3 position-relative">
                                <div class="image-container">
                                    <img src="<?= $specialist['photo'] ?? '' ?>" class="avatar" alt="specialist-<?= $specialist_id ?? '' ?>">
                                    <span class="upload-avatar">Изменить</span>
                                </div>
                                <input type="file" style="display: none" name="photo" id="input-photo">
                            </div>
                            <div class="col-9">
                                <div class="mb-3">
                                    <label for="input-name" class="form-label">Имя</label>
                                    <input type="text" name="name" class="form-control" id="input-name" value="<?= htmlspecialchars($specialist['name'] ?? '', ENT_QUOTES) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="input-lastname" class="form-label">Фамилия</label>
                                    <input type="text" name="lastname" class="form-control" id="input-lastname" value="<?= htmlspecialchars($specialist['lastname'] ?? '', ENT_QUOTES) ?>" required>
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
                                aria-describedby="emailHelp" required>
                        </div>
                        <div class="mb-3">
                            <label for="input-phone" class="form-label">Телефон</label>
                            <input type="tel" name="phone" class="form-control" maxlength="13" id="input-phone" value="<?= $specialist['phone'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-contacts-email" class="form-label">Контактный Email</label>
                            <input type="email" name="contacts_email" class="form-control" id="input-contacts-email"
                                value="<?= $specialist['contacts_email'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-contacts-site" class="form-label">Контактный Сайт</label>
                            <input type="url" name="contacts_site" class="form-control" id="input-contacts-site"
                                value="<?= $specialist['contacts_site'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-contacts-tg" class="form-label">Telegram</label>
                            <input type="text" name="contacts_tg" class="form-control" id="input-contacts-tg"
                                value="<?= $specialist['contacts_tg'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-contacts-vk" class="form-label">ВКонтакте</label>
                            <input type="url" name="contacts_vk" class="form-control" id="input-contacts-vk"
                                value="<?= $specialist['contacts_vk'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-contacts-dzen" class="form-label">Дзен</label>
                            <input type="url" name="contacts_dzen" class="form-control" id="input-contacts-dzen"
                                value="<?= $specialist['contacts_dzen'] ?? '' ?>">
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

                                foreach ($classifications as $classification): ?>
                                    <option value="<?= $classification['id'] ?>" data-type="<?= $classification['type'] ?>" <?= $classification['id'] == $specialist['classification_id'] ? 'selected' : '' ?>><?= $classification['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- <div class="mb-3" id="input-company" <?= ($specialist['type'] == 2) ?: 'style="display:none;"' ?>>
                            <label for="input-company" class="form-label">Название компании</label>
                            <input type="text" name="company-name" class="form-control" value="<?= $specialist['company'] ?? '' ?>" id="input-company_name">
                            <input type="hidden" name="company-id" value="<?= $specialist['company_id'] ?>" id="input-company_id">
                        </div>
 -->
                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="input-active" checked>
                            <label class="form-check-label" for="input-active">
                                Активировать специалиста
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Расписание -->
                <div class="tab-pane fade" id="content-schedule" role="tabpanel" aria-labelledby="tab-schedule">
                    <label class="form-label">Редактирование расписания</label>
                    <div class="border p-3 rounded">
                        <?php
                        $days = ['1' => 'Понедельник', '2' => 'Вторник', '3' => 'Среда', '4' => 'Четверг', '5' => 'Пятница', '6' => 'Суббота', '7' => 'Воскресенье'];
                        $schedule = $specialist['schedule_parsed'] ?? [];
                        foreach ($days as $dayKey => $dayName): ?>
                            <div class="mb-3">
                                <strong><?= $dayName ?></strong>
                                <div class="row g-2 align-items-center">
                                    <div class="col">
                                        <label>Начало работы</label>
                                        <input type="time" name="schedule[<?= $dayKey ?>][start]" class="form-control"
                                            value="<?= $schedule[$dayKey]['start'] ?? '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Конец работы</label>
                                        <input type="time" name="schedule[<?= $dayKey ?>][end]" class="form-control"
                                            value="<?= $schedule[$dayKey]['end'] ?? '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Начало перерыва</label>
                                        <input type="time" name="schedule[<?= $dayKey ?>][b_start]" class="form-control"
                                            value="<?= $schedule[$dayKey]['b_start'] ?? '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Конец перерыва</label>
                                        <input type="time" name="schedule[<?= $dayKey ?>][b_end]" class="form-control"
                                            value="<?= $schedule[$dayKey]['b_end'] ?? '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Выходной</label>
                                        <input type="checkbox" name="schedule[<?= $dayKey ?>][vac]" class="form-check-input"
                                            value="1" <?= !empty($schedule[$dayKey]['vac']) ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>


                <!-- Документы -->
                <div class="tab-pane fade" id="content-documents" role="tabpanel" aria-labelledby="tab-documents">
                    <label class="form-label">Редактирование документов</label>
                    <div class="border p-3 rounded">
                        <?php $documents = $specialist['documents'] ?? []; ?>
                        <?php if (!empty($documents)): ?>
                            <?php foreach ($documents as $docKey => $document): ?>
                                <div class="row align-items-center mb-3" data-document="<?= $docKey ?>">
                                    <!-- Название документа -->
                                    <div class="col-8">
                                        <label for="document-name-<?= $docKey ?>" class="form-label">Название документа</label>
                                        <input type="text" name="documents[<?= $docKey ?>][name]" class="form-control"
                                            id="document-name-<?= $docKey ?>"
                                            value="<?= htmlspecialchars($document['name']) ?>">
                                    </div>

                                    <!-- Изображение документа -->
                                    <div class="col-4 d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <!-- Превью изображения -->
                                            <img src="/uploads/documents/<?= htmlspecialchars($document['file']) ?>"
                                                class="img-thumbnail document-preview" alt="Документ"
                                                style="max-width: 100px; max-height: 100px;">

                                            <!-- Кнопка для вызова загрузки -->
                                            <button type="button" class="btn btn-sm btn-outline-secondary upload-trigger mt-2">Изменить</button>

                                            <!-- Поле для загрузки файла -->
                                            <input type="file" name="documents[<?= $docKey ?>][file]"
                                                class="form-control document-file-input d-none"
                                                data-file="<?= htmlspecialchars($document['file']) ?>">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Поля для добавления новых документов -->
                        <div class="row align-items-center mb-3">
                            <div class="col-8">
                                <label for="document-new-name" class="form-label">Название нового документа</label>
                                <input type="text" name="documents[new][name]" class="form-control" id="document-new-name">
                            </div>
                            <div class="col-4">
                                <label for="document-new-file" class="form-label">Файл нового документа</label>
                                <input type="file" name="documents[new][file]" class="form-control" id="document-new-file">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Услуги -->
                <div class="tab-pane fade" id="content-services" role="tabpanel" aria-labelledby="tab-services">
                    <label class="form-label">Редактирование услуг</label>
                    <div class="border p-3 rounded">
                        <?php $services = $specialist['services'] ?? []; ?>
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $serviceKey => $service): ?>
                                <div class="row align-items-center mb-3 service-row" data-service="<?= $serviceKey ?>">
                                    <!-- Название услуги -->
                                    <div class="col-5">
                                        <label for="service-name-<?= $serviceKey ?>" class="form-label">Название услуги</label>
                                        <input type="text" name="services[<?= $serviceKey ?>][name]" class="form-control"
                                            id="service-name-<?= $serviceKey ?>"
                                            value="<?= htmlspecialchars($service['name']) ?>">
                                    </div>

                                    <!-- Цена услуги -->
                                    <div class="col-4">
                                        <label for="service-price-<?= $serviceKey ?>" class="form-label">Цена услуги</label>
                                        <input type="text" name="services[<?= $serviceKey ?>][price]" class="form-control"
                                            id="service-price-<?= $serviceKey ?>"
                                            value="<?= htmlspecialchars($service['price']) ?>">
                                    </div>

                                    <!-- Чекбокс услуги -->
                                    <div class="col-1 d-flex align-items-center">
                                        <div class="form-check">
                                            <input type="checkbox" name="services[<?= $serviceKey ?>][is_active]" class="form-check-input"
                                                id="service-active-<?= $serviceKey ?>" <?= $service['is_active'] ? 'checked' : '' ?>>
                                            <label for="service-active-<?= $serviceKey ?>" class="form-check-label">От</label>
                                        </div>
                                    </div>

                                    <!-- Удаление услуги -->
                                    <div class="col-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-danger btn-sm delete-service-btn">Удалить</button>
                                    </div>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Поля для добавления новых услуг -->
                        <div class="row align-items-center mb-3">
                            <div class="col-5">
                                <label for="service-new-name" class="form-label">Название новой услуги</label>
                                <input type="text" name="services[new][name]" class="form-control" id="service-new-name">
                            </div>
                            <div class="col-4">
                                <label for="service-new-price" class="form-label">Цена новой услуги</label>
                                <input type="text" name="services[new][price]" class="form-control" id="service-new-price">
                            </div>
                            <div class="col-1 d-flex align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" name="services[new][is_active]" class="form-check-input" id="service-new-active">
                                    <label for="service-new-active" class="form-check-label">Активна</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="content-additional" role="tabpanel" aria-labelledby="tab-additional">
                    <h5 class="mt-3">Дополнительные данные</h5>

                    <!-- Поле для адреса -->
                    <div class="row align-items-center mb-3">
                        <div class="col-6">
                            <label for="contact-address" class="form-label">Адрес</label>
                            <input type="text" name="contacts[address]" class="form-control" id="contact-address"
                                value="<?= htmlspecialchars($specialist['address'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label for="contact-metro" class="form-label">Метро</label>
                            <input type="text" name="contacts[metro]" class="form-control" id="contact-metro"
                                value="<?= htmlspecialchars($specialist['metro'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Поля для улицы, дома и описания -->
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <label for="contact-street" class="form-label">Улица</label>
                            <input type="text" name="contacts[street]" class="form-control" id="contact-street"
                                value="<?= htmlspecialchars($specialist['street'] ?? '') ?>">
                        </div>
                        <div class="col-4">
                            <label for="contact-house" class="form-label">Дом</label>
                            <input type="text" name="contacts[house]" class="form-control" id="contact-house"
                                value="<?= htmlspecialchars($specialist['house'] ?? '') ?>">
                        </div>
                        <div class="col-4">
                            <label for="contact-how-to-get" class="form-label">Как добраться</label>
                            <input type="text" name="contacts[how_to_get]" class="form-control" id="contact-how-to-get"
                                value="<?= htmlspecialchars($specialist['how_to_get'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Поле для координат -->
                    <div class="row align-items-center mb-3">
                        <div class="col-12">
                            <label for="contact-coordinates" class="form-label">Координаты</label>
                            <input type="text" name="contacts[coordinates]" class="form-control" id="contact-coordinates"
                                value="<?= htmlspecialchars($specialist['coordinates'] ?? '') ?>">
                        </div>
                    </div>

                    <?= $specialization_html ?>
                </div>

                <div class="tab-pane fade" id="content-company" role="tabpanel" aria-labelledby="tab-additional">
                    <h5 class="mt-3">Данные компании</h5>

                    <?php if (!empty($specialist['company'])): ?>
                        <div class="mb-3">
                            <label for="input-company" class="form-label">Компания</label>
                            <input type="text" name="company" class="form-control" id="input-company" value="<?= htmlspecialchars($specialist['company'], ENT_QUOTES) ?>">
                            <input type="hidden" name="company_id" class="form-control" id="input-company_id" value="<?= htmlspecialchars($specialist['company_id'], ENT_QUOTES) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="input-type" class="form-label">Классификация компании</label>
                            <input type="hidden" name="type" value="<?= $specialist['type'] ?? 1 ?>" id="input-type">
                            <select name="classification_id" class="form-select" aria-label="Выберите классификацию" id="input-classification-company_id">
                                <?php
                                $classifications = $classifications ?? [];
                                $companyClassificationId = $specialist['company_classification'] ?? false; // Классификация компании

                                foreach ($classifications as $classification): ?>
                                    <option
                                        value="<?= $classification['id'] ?>"
                                        data-type="<?= $classification['type'] ?>"
                                        <?= $classification['id'] == $companyClassificationId ? 'selected' : '' ?>>
                                        <?= $classification['title'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (!empty($specialist['employees'])): ?>
                            <div class="company-employees">
                                <h5>Сотрудники компании:</h5>
                                <?php foreach ($specialist['employees'] as $index => $employee): ?>
                                    <div class="employee mb-4">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="employee-photo-<?= $index ?>" class="form-label">Фотография</label>
                                                <input type="file" name="employees[<?= $employee['id'] ?>][photo]" class="form-control" id="employee-photo-<?= $index ?>">
                                                <?php if (!empty($employee['photo'])): ?>
                                                    <img src="/uploads/employee/<?= $employee['photo'] ?>" alt="<?= htmlspecialchars($employee['name'] . ' ' . $employee['lastname']) ?>" class="mt-2 img-fluid">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="employee-name-<?= $index ?>" class="form-label">Имя</label>
                                                        <input type="text" name="employees[<?= $employee['id'] ?>][name]" class="form-control" id="employee-name-<?= $index ?>" value="<?= htmlspecialchars($employee['name']) ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="employee-post-<?= $index ?>" class="form-label">Должность</label>
                                                        <input type="text" name="employees[<?= $employee['id'] ?>][post]" class="form-control" id="employee-post-<?= $index ?>" value="<?= htmlspecialchars($employee['post']) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="employees[<?= $index ?>][id]" value="<?= $employee['id'] ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>


                    <?php endif; ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-id="<?= $specialist_id ?>" id="specialistAdd"><?= isset($specialist_id) ? 'Редактировать специалиста' : 'Добавить специалиста' ?></button>
            </div>
        </div>
    </div>
</div>