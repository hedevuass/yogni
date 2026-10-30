<style>
    .tab-pane:not(.active) {
        display: none !important;
    }
</style>

<?php
session_start();
$userRole = $_SESSION['user_role'] ?? 'editor';
?>

<div class="modal fade" id="modalAddSpecialist" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">
                    <?= ($specialist_id > 0) ? 'Карточка специалиста (id ' . $specialist_id . ') ' : 'Добавить специалиста' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <ul class="nav nav-tabs mb-3 justify-content-center row-gap-0 column-gap-2" id="specialistTab" role="tablist">
                    <!-- 1. Основная информация -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-basic" data-bs-toggle="tab"
                            data-bs-target="#content-basic" type="button" role="tab"
                            aria-controls="content-basic" aria-selected="true">Основное</button>
                    </li>

                    <!-- Галерея -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-gallery" data-bs-toggle="tab"
                            data-bs-target="#content-gallery" type="button" role="tab"
                            aria-controls="content-basic" aria-selected="true">Галерея</button>
                    </li>

                    <!-- 2. Контакты -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-address" data-bs-toggle="tab"
                            data-bs-target="#content-address" type="button" role="tab"
                            aria-controls="content-address" aria-selected="false">Контакты</button>
                    </li>

                    <!-- 3. Направления работы -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-specialization" data-bs-toggle="tab"
                            data-bs-target="#content-specialization" type="button" role="tab"
                            aria-controls="content-specialization" aria-selected="false">Направления</button>
                    </li>

                    <!-- Удобства и оборудование -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-udobstva" data-bs-toggle="tab"
                            data-bs-target="#content-udobstva" type="button" role="tab"
                            aria-controls="content-udobstva" aria-selected="false">Удобства и оборудование</button>
                    </li>

                    <!-- 4. Стоимость услуг -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-services" data-bs-toggle="tab"
                            data-bs-target="#content-services" type="button" role="tab"
                            aria-controls="content-services" aria-selected="false">Стоимость</button>
                    </li>

                    <!-- 5. Сотрудники -->
                    <li class="nav-item" id="content-company-id" role="presentation">
                        <button class="nav-link" id="tab-company" data-bs-toggle="tab"
                            data-bs-target="#content-company" type="button" role="tab"
                            aria-controls="content-company" aria-selected="false">Команда</button>
                    </li>

                    <!-- Залы -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-rooms" data-bs-toggle="tab"
                            data-bs-target="#content-rooms" type="button" role="tab"
                            aria-controls="content-rooms" aria-selected="true">Залы</button>
                    </li>

                    <!-- 6. Документы -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-documents" data-bs-toggle="tab"
                            data-bs-target="#content-documents" type="button" role="tab"
                            aria-controls="content-documents" aria-selected="false">Акции</button>
                    </li>

                    <!-- 7. Отзывы (только admin) -->
                    <?php if ($userRole == 'superadmin'): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-reviews" data-bs-toggle="tab"
                                data-bs-target="#content-reviews" type="button" role="tab"
                                aria-controls="content-reviews" aria-selected="false">Отзывы</button>
                        </li>
                    <?php endif; ?>

                    <!-- 8. Продвижение (для всех) -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-promotion" data-bs-toggle="tab"
                            data-bs-target="#content-promotion" type="button" role="tab"
                            aria-controls="content-promotion" aria-selected="false">Продвижение</button>
                    </li>

                    <!-- 9. Баннеры (только admin) -->
                    <?php if ($userRole == 'superadmin'): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-banners" data-bs-toggle="tab"
                                data-bs-target="#content-banners" type="button" role="tab"
                                aria-controls="content-banners" aria-selected="false">Баннеры</button>
                        </li>
                    <?php endif; ?>

                    <!-- 9/10 Заявки (для всех) -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-requests" data-bs-toggle="tab"
                            data-bs-target="#content-requests" type="button" role="tab"
                            aria-controls="content-requests" aria-selected="false">Заявки</button>
                    </li>

                    <!-- 10. Фотосъемка (для всех) -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-pro-photo" data-bs-toggle="tab"
                            data-bs-target="#content-pro-photo" type="button" role="tab"
                            aria-controls="content-pro-photo" aria-selected="false">Фотосъемка</button>
                    </li>

                    <!-- 11. Оплата (только admin) -->
                    <?php if ($userRole == 'superadmin'): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-payment" data-bs-toggle="tab"
                                data-bs-target="#content-payment" type="button" role="tab"
                                aria-controls="content-payment" aria-selected="false">Оплата</button>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content" id="specialistTabContent">
                    <!-- Основной контент -->
                    <div class="tab-pane fade show active" id="content-basic" role="tabpanel"
                        aria-labelledby="tab-basic">
                        <input type="hidden" name="id" class="form-control" id="input-id"
                            value="<?= $specialist_id ?? '' ?>">

                        <div class="mb-3" <?= $userRole === 'admin' ? 'style="position:absolute; left:-9999px; opacity:0;"' : '' ?>>
                            <label for="input-type" class="form-label">Категория</label>
                            <input type="hidden" name="type" value="<?= $specialist['type'] ?? 1 ?>" id="input-type">
                            <select name="classification_id" class="form-select" aria-label="Выберите классификацию"
                                id="input-classification_id">
                                <?php $classifications = $classifications ?? [];
                                $specialist['classification_id'] = $specialist['classification_id'] ?? false;

                                foreach ($classifications as $classification): ?>
                                    <option value="<?= $classification['id'] ?>" data-type="<?= $classification['type'] ?>"
                                        <?= $classification['id'] == $specialist['classification_id'] ? 'selected' : '' ?>>
                                        <?= $classification['title'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <div id="companyFields">
                            <div class="mb-3">
                                <input type="hidden" name="type" value="<?= $specialist['type'] ?? 1 ?>"
                                    id="input-type">
                                <input type="hidden" name="classification_id" id="input-classification-company_id"
                                    value="<?= $specialist['company_classification'] ?? '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="input-company" class="form-label">Наименование студии</label>
                                <input type="text" name="company" class="form-control" id="input-company"
                                    value="<?= htmlspecialchars($specialist['company'], ENT_QUOTES) ?>">
                                <input type="hidden" name="company_id" class="form-control" id="input-company_id"
                                    value="<?= htmlspecialchars($specialist['company_id'], ENT_QUOTES) ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="input-name" class="form-label">Имя</label>
                                    <input type="text" name="name" class="form-control" id="input-name"
                                        value="<?= htmlspecialchars($specialist['name'] ?? '', ENT_QUOTES) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="input-lastname" class="form-label">Фамилия</label>
                                    <input type="text" name="lastname" class="form-control" id="input-lastname"
                                        value="<?= htmlspecialchars($specialist['lastname'] ?? '', ENT_QUOTES) ?>"
                                        required>
                                </div>
                            </div>
                        </div>
                        <?php if ($userRole == 'superadmin'): ?>
                            <div class="mb-3">
                                <label for="input-email" class="form-label">Email для входа</label>
                                <input type="email" name="email" class="form-control" id="input-email"
                                    value="<?= $specialist['email'] ?? '' ?>" aria-describedby="emailHelp" required>
                            </div>
                            <div class="mb-3">
                                <label for="input-password" class="form-label">Пароль</label>
                                <input type="password" name="password" class="form-control" id="input-password">
                            </div>
                            <div class="mb-3">
                                <label for="input-inn" class="form-label">ИНН</label>
                                <input type="text" name="inn" class="form-control" id="input-inn"
                                    value="<?= htmlspecialchars($specialist['inn'] ?? '') ?>"
                                    maxlength="12" inputmode="numeric" pattern="[0-9]*"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    placeholder="ИНН (10 или 12 символов)">
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="input-page-discription" class="form-label">Описание страницы</label>
                            <textarea name="page-discription" class="form-control"
                                id="input-page-discription"><?= $specialist['page_description'] ? htmlspecialchars_decode($specialist['page_description']) : '' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <?php if ($userRole == 'superadmin'): ?>
                                <label for="input-city" class="form-label">Город</label>
                                <input type="text" name="city" class="form-control" id="input-city"
                                    placeholder="Выберите город" data-id="<?= $specialist['city_id'] ?>"
                                    value="<?= $specialist['city'] ?>">
                            <?php endif; ?>
                            <input type="hidden" id="input-city_id" name="city_id"
                                value="<?= $specialist['city_id'] ?>">
                        </div>
                        <div class="mb-3">
                            <label for="input-foundation" class="form-label"><?= $label ?></label>
                            <input type="text" name="foundation" class="form-control" id="input-foundation"
                                value="<?= $foundation ?>">
                        </div>
                        <?php if ($userRole == 'superadmin'): ?>
                            <div id="universityField" class="mb-3">
                                <label class="form-label">Образование</label>

                                <?php foreach ($universities as $index => $university): ?>
                                    <input type="text" name="university[]" class="form-control mb-2"
                                        value="<?= htmlspecialchars($university) ?>">
                                <?php endforeach; ?>

                                <!-- Пустое поле для добавления нового -->
                                <input type="text" name="university[]" class="form-control"
                                    placeholder="Добавить учебное заведение">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ссылка на страницу</label>
                                <div class="input-group">
                                    <span class="input-group-text">https://yogoort.ru/studio/</span>
                                    <input type="text" name="slug" class="form-control" id="input-slug"
                                        value="<?= htmlspecialchars($specialist['slug'] ?? '', ENT_QUOTES) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="input-meta-title" class="form-label">Title страницы</label>
                                <input type="text" name="meta-title" class="form-control" id="input-meta-title"
                                    value="">
                            </div>
                            <div class="mb-3">
                                <label for="input-meta-description" class="form-label">Desciption страницы</label>
                                <input type="text" name="meta-description" class="form-control" id="input-meta-description"
                                    value="">
                            </div>
                            <div class="mb-3">
                                <label for="input-meta-keywords" class="form-label">Ключевые слова</label>
                                <input type="text" name="meta-keywords" class="form-control" id="input-meta-keywords"
                                    value="">
                            </div>
                        <?php endif; ?>
                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="input-active"
                                <?= ($specialist['active'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="input-active">
                                Активировать специалиста
                            </label>
                        </div>
                    </div>
                </div>


                <!-- Документы -->
                <div class="tab-pane fade" id="content-documents" role="tabpanel" aria-labelledby="tab-documents">
                    <div class="border p-3 rounded" id="documents-container">
                        <?php $documents = $specialist['documents'] ?? []; ?>
                        <?php if (!empty($documents)): ?>
                            <?php foreach ($documents as $docKey => $document): ?>
                                <div class="document-wrapper mb-3">
                                    <div class="row align-items-center mb-3" data-document="<?= $docKey ?>">
                                        <!-- Название документа -->
                                        <div class="col-7">
                                            <label for="document-name-<?= $docKey ?>" class="form-label">Название акции</label>
                                            <input type="text" name="documents[<?= $docKey ?>][name]" class="form-control"
                                                id="document-name-<?= $docKey ?>" value="<?= htmlspecialchars($document['name']) ?>">
                                        </div>

                                        <!-- Изображение документа -->
                                        <div class="col-2 mt-4 d-flex align-items-center">
                                            <div class="position-relative me-3">
                                                <img src="/uploads/documents/<?= htmlspecialchars($document['file']) ?>"
                                                    class="img-thumbnail document-preview" alt="Акция"
                                                    style="max-width: 100px; max-height: 100px;">
                                            </div>
                                        </div>

                                        <!-- Кнопки -->
                                        <div class="col-3 mt-4">
                                            <button type="button" class="btn btn-sm btn-outline-secondary upload-trigger">Изменить</button>
                                            <button type="button" class="btn btn-sm btn-danger delete-document-btn">Удалить</button>
                                            <input type="file" name="documents[<?= $docKey ?>][file]" class="form-control document-file-input d-none"
                                                data-file="<?= htmlspecialchars($document['file']) ?>">
                                            <input type="hidden" name="documents[<?= $docKey ?>][file_existing]" value="<?= htmlspecialchars($document['file']) ?>">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Кнопка для добавления нового документа -->
                    <button type="button" id="add-document-btn" class="btn btn-primary mt-3">Добавить акцию</button>
                </div>

                <!-- Услуги -->
                <div class="tab-pane fade" id="content-services" role="tabpanel" aria-labelledby="tab-services">
                    <div class="border p-3 rounded" id="services-container">
                        <?php $services = $specialist['services'] ?? []; ?>
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $serviceKey => $service): ?>
                                <div class="service-wrapper mb-3">
                                    <div class="row mb-3 service-row align-items-end" data-service="<?= $serviceKey ?>">
                                        <!-- Название услуги -->
                                        <div class="col-md-5">
                                            <label for="service-name-<?= $serviceKey ?>" class="form-label">Название услуги</label>
                                            <input type="text" name="services[<?= $serviceKey ?>][name]" class="form-control"
                                                id="service-name-<?= $serviceKey ?>" value="<?= htmlspecialchars($service['name']) ?>">
                                        </div>

                                        <!-- Цена -->
                                        <div class="col-md-4">
                                            <label for="service-price-<?= $serviceKey ?>" class="form-label">Цена услуги</label>
                                            <input type="text" name="services[<?= $serviceKey ?>][price]" class="form-control"
                                                id="service-price-<?= $serviceKey ?>" value="<?= htmlspecialchars($service['price']) ?>">
                                        </div>

                                        <!-- Чекбокс -->
                                        <div class="col-md-1">
                                            <div class="d-flex flex-column">
                                                <label for="service-active-<?= $serviceKey ?>" class="form-label">От</label>
                                                <div class="form-check mt-auto">
                                                    <input type="checkbox" name="services[<?= $serviceKey ?>][is_active]" class="form-check-input"
                                                        id="service-active-<?= $serviceKey ?>" <?= $service['is_active'] ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Кнопка удаления -->
                                        <div class="col-md-2">
                                            <div class="d-flex flex-column">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger mt-auto delete-service-btn">Удалить</button>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Кнопка для добавления новой услуги -->
                    <button type="button" id="add-service-btn" class="btn btn-primary mt-3">Добавить услугу</button>
                </div>


                <div class="tab-pane fade" id="content-address" role="tabpanel" aria-labelledby="tab-additional">

                    <div class="mb-3">
                        <label for="input-phone" class="form-label">Телефон для звонков</label>
                        <input type="tel" name="phone" class="form-control phone-input-reg" id="input-phone"
                            value="<?= $specialist['phone'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="input-contacts-email" class="form-label">Публичный email</label>
                        <input type="email" name="contacts_email" class="form-control" id="input-contacts-email"
                            value="<?= $specialist['contacts_email'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="input-contacts-site" class="form-label">Сайт</label>
                        <input type="url" name="contacts_site" class="form-control" id="input-contacts-site"
                            value="<?= $specialist['contacts_site'] ?? '' ?>">
                    </div>
                    <!-- <div class="mb-3">
                        <label for="input-contacts-tg" class="form-label">Telegram</label>
                        <input type="text" name="contacts_tg" class="form-control" id="input-contacts-tg"
                            value="<?= $specialist['contacts_tg'] ?? '' ?>">
                    </div> -->
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
                        <label for="input-contacts-max" class="form-label">MAX</label>
                        <input type="url" name="contacts_max" class="form-control" id="input-contacts-max"
                            value="<?= $specialist['contacts_max'] ?? '' ?>">
                    </div>
                    <!-- Поле для адреса -->
                    <div class="row align-items-center mt-5 mb-3">
                        <div class="col-6">
                            <label for="input-type" class="form-label">Город</label>
                            <input type="text" name="city" class="form-control" id="input-city"
                                value="<?= $specialist['city'] ?>" readonly>
                        </div>
                        <div class="col-6">
                            <label for="contact-metro" class="form-label">Метро</label>
                            <input type="text" name="metro" class="form-control ui-autocomplete-input"
                                placeholder="Выберите метро" value="<?= $specialist['metro'] ?? '' ?>"
                                autocomplete="off">
                            <input type="hidden" name="metro_id" id="contact-metro"
                                value="<?= $specialist['metro_id'] ?? '' ?>">
                        </div>
                    </div>

                    <!-- Поля для улицы, дома и описания -->
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <label for="contact-street" class="form-label">Улица</label>
                            <input type="text" name="street" class="form-control" id="contact-street"
                                value="<?= htmlspecialchars($specialist['street'] ?? '') ?>">
                        </div>
                        <div class="col-4">
                            <label for="contact-house" class="form-label">Дом</label>
                            <input type="text" name="house" class="form-control" id="contact-house"
                                value="<?= htmlspecialchars($specialist['house'] ?? '') ?>">
                        </div>
                        <div class="col-4">
                            <label for="contact-coordinates" class="form-label">Координаты</label>
                            <input type="text" name="coordinates" class="form-control" id="contact-coordinates"
                                value="<?= htmlspecialchars($specialist['coordinates'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Специализация -->
                    <div class="row align-items-center mb-3">
                        <div class="col-12">
                            <label for="contact-how-to-get" class="form-label">Как добраться</label>
                            <input type="text" name="how_to_get" class="form-control" style="height: 60px;"
                                id="contact-how-to-get"
                                value="<?= htmlspecialchars($specialist['how_to_get'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Расписание -->
                    <label class="form-label mt-5">График работы</label>
                    <div class="border p-3 rounded">
                        <?php
                        $days = [
                            '1' => 'Понедельник',
                            '2' => 'Вторник',
                            '3' => 'Среда',
                            '4' => 'Четверг',
                            '5' => 'Пятница',
                            '6' => 'Суббота',
                            '7' => 'Воскресенье'
                        ];

                        // Если вообще нет данных — создаём пустое расписание
                        $schedule = $specialist['schedule_parsed'] ?? [];

                        foreach ($days as $dayKey => $dayName):
                            $day = $schedule[$dayKey] ?? [];

                            // Определим значения полей
                            $start = $day['start'] ?? '';
                            $end = $day['end'] ?? '';
                            $b_start = $day['b_start'] ?? '';
                            $b_end = $day['b_end'] ?? '';

                            $has_schedule = !empty($schedule);  // есть ли вообще расписание?
                            $vac_is_set = array_key_exists('vac', $day);

                            // Ставим галку "Выходной", если расписание есть и vac НЕ задан
                            $is_day_off = $has_schedule && !$vac_is_set;
                        ?>
                            <div class="mb-3">
                                <strong><?= $dayName ?></strong>
                                <div class="row g-2 align-items-center">
                                    <div class="col">
                                        <label>Начало работы</label>
                                        <input type="time" step="1800" name="schedule[<?= $dayKey ?>][start]" class="form-control"
                                            value="<?= $start !== false ? htmlspecialchars($start) : '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Конец работы</label>
                                        <input type="time" step="1800" name="schedule[<?= $dayKey ?>][end]" class="form-control"
                                            value="<?= $end !== false ? htmlspecialchars($end) : '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Начало перерыва</label>
                                        <input type="time" step="1800" name="schedule[<?= $dayKey ?>][b_start]" class="form-control"
                                            value="<?= $b_start !== false ? htmlspecialchars($b_start) : '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Конец перерыва</label>
                                        <input type="time" step="1800" name="schedule[<?= $dayKey ?>][b_end]" class="form-control"
                                            value="<?= $b_end !== false ? htmlspecialchars($b_end) : '' ?>">
                                    </div>
                                    <div class="col">
                                        <label>Выходной</label>
                                        <input type="checkbox" name="schedule[<?= $dayKey ?>][vac]" class="form-check-input"
                                            value="1" <?= $is_day_off ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>

                <div class="tab-pane fade" id="content-rooms" role="tabpanel" aria-labelledby="tab-rooms">
                    <div class="mb-5">
                        <div class="admin-rooms-list" id="admin-rooms-list">
                            <?php
                            $rooms = json_decode($specialist['rooms'] ?? '[]', true) ?: [];
                            $photos_per_room = 4;

                            // Если залов нет вообще — показываем один пустой блок для старта
                            if (empty($rooms)) {
                                $rooms = [['name' => '', 'photos' => []]];
                            }
                            ?>

                            <?php foreach ($rooms as $r => $room): ?>
                                <?php
                                $room_name = htmlspecialchars($room['name'] ?? '', ENT_QUOTES);
                                $room_photos = $room['photos'] ?? [];
                                ?>
                                <div class="admin-room-block" data-room-index="<?= $r ?>">

                                    <div class="admin-room-header">
                                        <div class="admin-room-field">
                                            <label class="admin-room-label">Название зала</label>
                                            <input type="text" class="form-control admin-room-name-input col-9" name="rooms[<?= $r ?>][name]"
                                                value="<?= $room_name ?>" maxlength="65" placeholder="">
                                        </div>
                                        <button type="button" class="btn btn-danger admin-room-delete-btn">Удалить</button>
                                    </div>

                                    <div class="admin-gallery-grid">
                                        <?php for ($i = 0; $i < $photos_per_room; $i++): ?>
                                            <?php
                                            $has_photo = isset($room_photos[$i]);
                                            $photo_url = $has_photo ? '/uploads/rooms/' . htmlspecialchars($room_photos[$i], ENT_QUOTES) : '';
                                            ?>
                                            <div class="admin-gallery-item gallery-slot">
                                                <div class="image-container" style="<?= $has_photo ? "background-image: url('{$photo_url}');" : '' ?>">
                                                    <div class="upload-avatar">
                                                        <button type="button" class="upload-avatar-add" title="Добавить фото"></button>
                                                        <button type="button" class="upload-avatar-delete" title="Удалить фото"></button>
                                                    </div>
                                                </div>
                                                <input type="file" style="display: none" name="rooms[<?= $r ?>][photos][<?= $i ?>]"
                                                    id="input-room-<?= $r ?>-<?= $i ?>" accept="image/jpeg, image/png">
                                                <input type="hidden" name="rooms[<?= $r ?>][existing_photos][<?= $i ?>]"
                                                    class="existing-photo-input"
                                                    value="<?= $has_photo ? htmlspecialchars($room_photos[$i], ENT_QUOTES) : '' ?>">
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>

                        </div>

                        <button type="button" class="btn btn-primary mt-3 admin-add-room-btn" id="admin-add-room-btn">
                            Добавить зал
                        </button>
                    </div>
                </div>

                <div class="tab-pane fade" id="content-gallery" role="tabpanel" aria-labelledby="tab-additional">
                    <div class="mb-5">
                        <div>
                            <div class="admin-gallery-grid">
                                <?php
                                $gallery_photos = json_decode($specialist['gallery'] ?? '[]', true) ?: [];
                                $max_slots = 12;
                                ?>
                                <?php for ($i = 0; $i < $max_slots; $i++): ?>

                                    <?php if ($i === 0): ?>
                                        <?php
                                        $has_photo = !empty($specialist['photo']);
                                        $photo_url = $has_photo ? $specialist['photo'] : '';
                                        ?>
                                        <div class="admin-gallery-item avatar-slot">
                                            <div class="image-container" style="<?= $has_photo ? "background-image: url('{$photo_url}');" : '' ?>">
                                                <div class="upload-avatar upload-avatar-specialist">
                                                    <button type="button" class="upload-avatar-add" title="Добавить фото"></button>
                                                    <button type="button" class="upload-avatar-delete" title="Удалить фото"></button>
                                                </div>
                                            </div>
                                            <input type="file" style="display: none" name="photo" id="input-photo" accept="image/jpeg, image/png">
                                            <input type="hidden" name="existing_photos[0]" class="existing-photo-input" value="<?= $has_photo ? htmlspecialchars($photo_url, ENT_QUOTES) : '' ?>">
                                        </div>

                                    <?php else: ?>
                                        <?php
                                        $gallery_index = $i - 1;  // Индекс в массиве галереи (0-10)
                                        $has_gallery_photo = isset($gallery_photos[$gallery_index]);
                                        $photo_url = $has_gallery_photo ? '/uploads/gallery/' . htmlspecialchars($gallery_photos[$gallery_index], ENT_QUOTES) : '';
                                        ?>
                                        <div class="admin-gallery-item gallery-slot">
                                            <div class="image-container" style="<?= $has_gallery_photo ? "background-image: url('{$photo_url}');" : '' ?>">
                                                <div class="upload-avatar">
                                                    <button type="button" class="upload-avatar-add" title="Добавить фото"></button>
                                                    <button type="button" class="upload-avatar-delete" title="Удалить фото" </button>
                                                </div>
                                            </div>
                                            <input type="file" style="display: none" name="gallery_files[<?= $gallery_index ?>]" id="input-gallery-<?= $gallery_index ?>" accept="image/jpeg, image/png, image/jfif, image/heic">
                                            <input type="hidden" name="existing_photos[<?= $i ?>]" class="existing-photo-input" value="<?= $has_gallery_photo ? htmlspecialchars($gallery_photos[$gallery_index], ENT_QUOTES) : '' ?>">
                                        </div>
                                    <?php endif; ?>

                                <?php endfor; ?>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="content-specialization" role="tabpanel" aria-labelledby="tab-additional">
                    <?= $specialization_html ?>
                </div>

                <div class="tab-pane fade" id="content-udobstva" role="tabpanel" aria-labelledby="tab-additional">
                    <?= $equipment_html ?>
                </div>

                <!-- Сотрудники -->
                <div class="tab-pane fade" id="content-company" role="tabpanel" aria-labelledby="tab-additional">
                    <div class="border p-3 rounded" id="employees-container">
                        <?php foreach ($specialist['employees'] as $index => $employee): ?>
                            <div class="employee-wrapper mb-3">
                                <div class="row align-items-center mb-3" data-employee="<?= $employee['id'] ?>">
                                    <!-- Основная информация -->
                                    <div class="col-md-7">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="employee-name-<?= $index ?>" class="form-label">Имя</label>
                                                <input type="text" name="employees[<?= $employee['id'] ?>][name]"
                                                    class="form-control" id="employee-name-<?= $index ?>"
                                                    value="<?= htmlspecialchars($employee['name']) ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="employee-lastname-<?= $index ?>"
                                                    class="form-label">Фамилия</label>
                                                <input type="text" name="employees[<?= $employee['id'] ?>][lastname]"
                                                    class="form-control" id="employee-lastname-<?= $index ?>"
                                                    value="<?= htmlspecialchars($employee['lastname']) ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="employee-post-<?= $index ?>"
                                                    class="form-label">Должность</label>
                                                <input type="text" name="employees[<?= $employee['id'] ?>][post]"
                                                    class="form-control" id="employee-post-<?= $index ?>"
                                                    value="<?= htmlspecialchars($employee['post']) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Фото -->
                                    <div class="col-2 mt-4 d-flex align-items-center">
                                        <?php if (!empty($employee['photo'])): ?>
                                            <img src="/uploads/employee/<?= htmlspecialchars($employee['photo']) ?>"
                                                class="img-thumbnail employee-preview" alt="Сотрудник"
                                                style="max-width:100px; max-height:100px;">
                                        <?php else: ?>
                                            <img src="/assets/img/cabinet/no-photo.svg" class="img-thumbnail employee-preview"
                                                style="max-width:100px; max-height:100px;">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Кнопки -->
                                    <div class="col-3 mt-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary upload-employee-btn">Изменить</button>
                                        <button type="button" class="btn btn-sm btn-danger remove-employee-btn">Удалить</button>
                                        <input type="file" name="employees[<?= $employee['id'] ?>][photo]" class="employee-file-input form-control d-none">
                                        <?php if (!empty($employee['photo'])): ?>
                                            <input type="hidden" name="employees[<?= $employee['id'] ?>][photo]" value="<?= htmlspecialchars($employee['photo']) ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Кнопка добавления нового сотрудника -->
                    <button type="button" id="add-employee-btn" class="btn btn-primary mt-3">Добавить сотрудника</button>
                </div>

                <div class="tab-pane fade" id="content-reviews" role="tabpanel" aria-labelledby="tab-reviews">
                    <?php if (!empty($reviews)): ?>
                        <div class="reviews-list border ps-3 pe-2 pt-3 pb-3 rounded">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review row mb-3 align-items-end">
                                    <div class="mr-3 col-md-2">
                                        <label for="reviewer-name-<?= $review['id'] ?>">Дата</label>
                                        <input type="text" class="form-control" id="reviewer-name-<?= $review['id'] ?>"
                                            value="<?= date('d.m.Y', strtotime($review['date'])) ?>" disabled>
                                    </div>
                                    <div class="mr-3 col-md-2">
                                        <label for="reviewer-name-<?= $review['id'] ?>">Автор</label>
                                        <input type="text" class="form-control" id="reviewer-name-<?= $review['id'] ?>"
                                            value="<?= htmlspecialchars($review['author']) ?>" disabled>
                                    </div>
                                    <div class="mr-3 col-md-3">
                                        <label for="reviewer-name-<?= $review['id'] ?>">Отзыв</label>
                                        <textarea class="form-control" id="review-text-<?= $review['id'] ?>" rows="1"
                                            disabled><?= htmlspecialchars($review['review']) ?></textarea>
                                    </div>
                                    <div class="mr-3 col-md-3">
                                        <label for="reviewer-name-<?= $review['id'] ?>">Контакт</label>
                                        <textarea class="form-control" id="review-contact-<?= $review['id'] ?>" rows="1"
                                            disabled><?= htmlspecialchars($review['communication']) ?></textarea>
                                    </div>
                                    <div class="mr-3 col-md-2">
                                        <button type="button" class="btn btn-danger delete-review"
                                            data-id="<?= $review['id'] ?>">
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Отзывов еще нет.</p>
                    <?php endif; ?>
                </div>

                <?php
                $promo_list = get_all_connected_promo_placements($specialist_id, 'date_start');
                $all_promotions = get_promotions();

                // вычисляем сегодняшнюю дату
                $today = date('Y-m-d');

                // активные подключения к тарифам
                $activeTariffConnections = array_filter($connectedTariffs, function ($row) use ($today) {
                    return $row['moderation'] == 0 &&
                        !empty($row['end_date']) &&
                        $today <= $row['end_date'];
                });

                $maxDiscount = 0;

                foreach ($activeTariffConnections as $conn) {
                    switch ((int) $conn['tariff_id']) {
                        case 1:
                            $maxDiscount = max($maxDiscount, 10);
                            break;
                        case 2:
                            $maxDiscount = max($maxDiscount, 15);
                            break;
                        case 3:
                            $maxDiscount = max($maxDiscount, 20);
                            break;
                    }
                }

                // активные подключения к опциям
                $activeOptionConnections = array_filter($optionsList, function ($row) use ($today) {
                    return $row['moderation'] == 0 &&
                        !empty($row['end_date']) &&
                        $today <= $row['end_date'];
                });

                $activePromo = array_filter($promo_list, function ($item) use ($today) {
                    return $item['moderation'] == 0 &&
                        !empty($item['date_start']) &&
                        !empty($item['date_expires']) &&
                        $today <= $item['date_expires'];
                });
                ?>

                <div class="tab-pane fade" id="content-promotion" role="tabpanel" aria-labelledby="tab-reviews">

                    <div class="active-connections border border-success rounded mb-4 p-4 bg-light">
                        <h4 class="mb-4">Действующие и запланированные размещения</h4>

                        <strong>Тарифы</strong>
                        <?php if (!empty($activeTariffConnections)): ?>
                            <ul class="list-group mb-4">
                                <?php
                                foreach ($activeTariffConnections as $conn):
                                    $tariff = current(array_filter(get_tariffs(), fn($t) => $t['id'] == $conn['tariff_id']));
                                    $name = $tariff['name'] ?? 'Неизвестный тариф';
                                    $months = (int) $conn['duration'];
                                    $start = date('d.m.Y', strtotime("-$months month", strtotime($conn['end_date'])));
                                    $end = date('d.m.Y', strtotime($conn['end_date']));
                                ?>
                                    <div class="mb-1 pt-2 pb-2 d-flex align-items-center" style="gap: 1rem;">
                                        <div style="width: 250px; flex-shrink: 0;">
                                            <?= htmlspecialchars($name) ?>
                                        </div>
                                        <div style="width: 200px; flex-shrink: 0;">
                                            с <?= htmlspecialchars($start) ?> по <?= htmlspecialchars($end) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <ul class="list-group mb-4">
                                <div class="mb-1 pt-2 pb-2">Подключений нет</div>
                            </ul>
                        <?php endif; ?>

                        <strong>Промо-блоки</strong>
                        <?php if (!empty($activePromo)): ?>
                            <ul class="list-group mb-4">
                                <?php
                                foreach ($activePromo as $item):
                                    $promo = $all_promotions[array_search($item['block_id'], array_column($all_promotions, 'id'))] ?? null;
                                    if (!$promo)
                                        continue;
                                    $start = date('d.m.Y', strtotime($item['date_start']));
                                    $expires = date('d.m.Y', strtotime($item['date_expires']));
                                    $placementText = $item['sort'] === 1 ? 'Размещение на 1-м месте' : 'Стандартное размещение';
                                    $isFuture = $item['date_expires'] >= $today ? '' : '';
                                ?>
                                    <div class="mb-1 pt-2 pb-2 <?= $isFuture ?>"
                                        style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 250px; flex-shrink: 0;">
                                            <?= htmlspecialchars($item['service_name']) ?>
                                        </div>
                                        <div style="width: 200px; flex-shrink: 0;">
                                            с <?= htmlspecialchars($start) ?> по <?= htmlspecialchars($expires) ?>
                                        </div>
                                        <div>
                                            <input type="checkbox" readonly checked class="ms-2">
                                            <?= htmlspecialchars($placementText) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <ul class="list-group mb-4">
                                <div class="mb-1 pt-2 pb-2">Подключений нет</div>
                            </ul>
                        <?php endif; ?>

                        <?php
                        $connectedTariffMap = [];
                        if (is_array($connectedTariffs)) {
                            foreach ($connectedTariffs as $t) {
                                $connectedTariffMap[$t['tariff_id']] = $t;
                            }
                        }
                        $firstTariff = current($connectedTariffMap) ?: [];
                        // duration могут быть 1 или 3 — если не нашли, по умолчанию 1
                        $tariffDuration = (int) ($firstTariff['duration'] ?? 1);

                        $connectedTariffIds = array_keys($connectedTariffMap);
                        $hasProExpert = in_array(2, $connectedTariffIds) || in_array(3, $connectedTariffIds);
                        $hasBasic = in_array(1, $connectedTariffIds);  // Basic тариф
                        ?>

                        <strong>Опции</strong>
                        <ul class="list-group mb-4">
                            <?php
                            $optionMap = [];
                            foreach (get_options() as $opt) {
                                $optionMap[$opt['id']] = $opt;
                            }
                            ksort($optionMap);

                            $activeMap = [];
                            foreach ($activeOptionConnections as $conn) {
                                $activeMap[$conn['option_id']] = $conn;
                            }

                            foreach ($optionMap as $id => $opt):
                                $label = $opt['name'] ?? 'Неизвестная опция';
                                $isActive = isset($activeMap[$id]);
                                $includedInTariff = ($hasProExpert && in_array($id, [1, 2, 3, 4, 5])) ||
                                    ($hasBasic && in_array($id, [1, 2, 3]));

                                if (!$isActive && !$includedInTariff) {
                                    continue;
                                }

                                if ($isActive && $includedInTariff) {
                                    $start = date('d.m.Y');
                                    $end = date('d.m.Y', strtotime("+{$tariffDuration} month"));
                                }
                                // --- Только вручную подключены ---
                                elseif ($isActive) {
                                    $endDate = $activeMap[$id]['end_date'];
                                    $start = date('d.m.Y', strtotime('-1 month', strtotime($endDate)));
                                    $end = date('d.m.Y', strtotime($endDate));
                                }
                                // --- Только по тарифу ---
                                else {
                                    $start = date('d.m.Y');
                                    $end = date('d.m.Y', strtotime("+{$tariffDuration} month"));
                                }

                                $dateText = "с {$start} по {$end}";
                            ?>

                                <div class="mb-1 pt-2 pb-2 d-flex align-items-center" style="gap:1rem;">
                                    <div style="width:250px; flex-shrink:0;"><?= htmlspecialchars($label) ?></div>
                                    <div style="width:210px; flex-shrink:0;"><?= htmlspecialchars($dateText) ?></div>
                                    <div style="width:200px; flex-shrink:0;">
                                        <?php if ($includedInTariff): ?>
                                            <span>Включено в тариф</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php
                            // --- 7. Сообщение, если ничего не попало в вывод ---
                            $hasAny = false;
                            foreach ($optionMap as $i => $_) {
                                if (
                                    isset($activeMap[$i]) || (
                                        ($hasProExpert && in_array($i, [1, 2, 3, 4, 5])) ||
                                        ($hasBasic && in_array($i, [1, 2, 3]))
                                    )
                                ) {
                                    $hasAny = true;
                                    break;
                                }
                            }
                            if (!$hasAny):
                            ?>
                                <div class="mb-1 pt-2 pb-2">Подключений нет</div>
                            <?php endif; ?>
                        </ul>

                    </div>

                    <!-- Тарифы -->
                    <div class="tariffs mb-5">
                        <h4>Тарифы</h4>
                        <?php
                        $connectedTariffMap = [];
                        if (is_array($connectedTariffs)) {
                            foreach ($connectedTariffs as $item) {
                                $connectedTariffMap[$item['tariff_id']] = $item;
                            }
                        }
                        foreach ($tariffs as $tariff):
                        ?>
                            <?php
                            $id = $tariff['id'];
                            $connected = $connectedTariffMap[$id] ?? null;
                            // Определяем, какой duration был сохранён (1 или 3)
                            $savedDuration = $connected['duration'] ?? null;
                            // Метки checked
                            $check1 = $savedDuration == 1 ? 'checked' : '';
                            $check3 = $savedDuration == 3 ? 'checked' : '';

                            // Определяем цену в зависимости от duration
                            $price = $tariff['price'];  // по умолчанию
                            if ($savedDuration == 1 && isset($tariff['price_old'])) {
                                $price = $tariff['price_old'];
                            } elseif ($savedDuration == 3) {
                                $price = $tariff['price'];
                            }
                            ?>
                            <div class="row align-items-center border-bottom py-2">
                                <div class="col-3">
                                    <strong><?= htmlspecialchars($tariff['name']) ?></strong>
                                </div>
                                <div class="col-2">
                                    <?php if ($connected): ?>
                                        <button class="btn btn-sm btn-info text-white">Подключено</button>
                                    <?php endif; ?>
                                </div>
                                <div class="col-7 text-end">
                                    <!-- Блок чекбоксов -->
                                    <div class="d-inline-flex align-items-center gap-5 mb-3">
                                        <label class="form-check-label mb-0">
                                            <input type="checkbox" class="duration-checkbox" name="duration_<?= $id ?>"
                                                value="1" <?= $check1 ?>>
                                            1 месяц
                                        </label>
                                        <label class="form-check-label mb-0">
                                            <input type="checkbox" class="duration-checkbox" name="duration_<?= $id ?>"
                                                value="3" <?= $check3 ?>>
                                            3 месяца
                                        </label>
                                    </div>

                                    <!-- Кнопки -->
                                    <div>
                                        <button type="button"
                                            class="btn btn-sm btn-success connect"
                                            data-type="tariff"
                                            data-id="<?= $tariff['id'] ?>"
                                            data-specialist-id="<?= $specialist_id ?>"
                                            data-duration-checkbox="duration_<?= $tariff['id'] ?>"
                                            data-price="<?= $tariff['price'] ?>"
                                            data-price-old="<?= $tariff['price_old'] ?>"
                                            <?= isset($connectedTariffMap[$tariff['id']]) ? 'disabled' : '' ?>>
                                            Подключить
                                        </button>
                                        <?php if ($userRole == 'superadmin'): ?>
                                            <button type="button" class="btn btn-sm btn-danger disconnect" data-type="tariff"
                                                data-id="<?= isset($connectedTariffMap[$tariff['id']]) ? $connectedTariffMap[$tariff['id']]['id'] : '' ?>"
                                                data-specialist-id="<?= $specialist_id ?>"
                                                <?= !isset($connectedTariffMap[$tariff['id']]) ? 'disabled' : '' ?>>
                                                Отключить
                                            </button>
                                        <?php endif; ?>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary send-invoice"
                                            data-type="tariff"
                                            data-id="<?= $tariff['id'] ?>"
                                            data-inn="<?= $specialist['inn'] ?>"
                                            data-specialist-id="<?= $specialist_id ?>"
                                            data-duration-checkbox="duration_<?= $tariff['id'] ?>"
                                            data-price="<?= htmlspecialchars(number_format($price, 2, '.', '')) ?>"
                                            data-email="<?= htmlspecialchars($specialist['email'] ?? '') ?>"
                                            data-order-id="<?= htmlspecialchars($firstTariff['orderId'] ?? '') ?>"
                                            data-customer-type="ip"
                                            <?= !isset($connectedTariffMap[$tariff['id']]) ? 'disabled' : '' ?>>
                                            Отправить счет
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>

                    <?php
                    $services = get_services_for_menu_with_prochie_last();
                    $connected_promo_services = get_connected_promo_placements($specialist_id);
                    $connected_slugs = array_column($connected_promo_services, 'service_slug');
                    $promo_blocks = get_promo_blocks_with_prefix();

                    $indexed_blocks = [];
                    foreach ($promo_blocks as $block) {
                        $numeric_id = (int) str_replace('p_', '', $block['id']);
                        $indexed_blocks[$numeric_id] = $block;
                    }

                    $group_one = [1, 2, 5, 6, 7];
                    $group_two = [3, 8];
                    ?>

                    <div class="services-list mb-5">
                        <h4>Промо-блоки</h4>

                        <?php
                        $pages = [];

                        if (isset($indexed_blocks[1])) {
                            $block = $indexed_blocks[1];
                            $pages[] = [
                                'title' => $block['title'],
                                'service_slug' => 'block_top_specialists',
                                'block_id' => 1,
                                'price_block1' => $block['price_block1'],
                                'price_block2' => $block['price_block2']
                            ];
                        }

                        if (isset($indexed_blocks[2])) {
                            $block = $indexed_blocks[2];
                            $pages[] = [
                                'title' => $block['title'],
                                'service_slug' => 'block_specialist_positions',
                                'block_id' => 2,
                                'price_block1' => $block['price_block1'],
                                'price_block2' => $block['price_block2']
                            ];
                        }

                        // Далее добавляем динамично по классификации
                        if (in_array($specialist_classification, [1, 2, 5, 6, 7]) && isset($indexed_blocks[3])) {
                            $block = $indexed_blocks[3];
                            $pages[] = [
                                'title' => $block['title'],
                                'service_slug' => '',
                                'block_id' => 1,
                                'price_block1' => $block['price_block1'],
                                'price_block2' => $block['price_block2']
                            ];
                        }

                        if (in_array($specialist_classification, [3, 8]) && isset($indexed_blocks[4])) {
                            $block = $indexed_blocks[4];
                            $pages[] = [
                                'title' => $block['title'],
                                'service_slug' => '',
                                'block_id' => 2,
                                'price_block1' => $block['price_block1'],
                                'price_block2' => $block['price_block2']
                            ];
                        }

                        if ($specialist_classification == 4 && isset($indexed_blocks[5])) {
                            $block = $indexed_blocks[5];
                            $pages[] = [
                                'title' => $block['title'],
                                'service_slug' => '',
                                'block_id' => 3,
                                'price_block1' => $block['price_block1'],
                                'price_block2' => $block['price_block2']
                            ];
                        }
                        ?>

                        <!-- Вывод промо-страниц -->
                        <?php foreach ($pages as $page): ?>
                            <?php
                            // Собираем все подключения для страницы
                            $serviceSlug = $page['service_slug'] ?: '';
                            $blockId = $page['block_id'] ?? null;
                            $conns = [];

                            foreach ($connected_promo_services as $promo) {
                                if ($serviceSlug !== '') {
                                    // Если у страницы есть service_slug → сверяем только его
                                    if (!empty($promo['service_slug']) && $promo['service_slug'] === $serviceSlug) {
                                        $conns[] = [
                                            'id' => $promo['id'],
                                            'sort' => (int) $promo['sort'],
                                            'date_start' => $promo['date_start'],
                                            'date_expires' => $promo['date_expires'],
                                            'specialist_id' => $specialist_id,
                                            'classifications_id' => $specialist_classification,
                                            'service_slug' => $serviceSlug,
                                            'block_id' => $blockId,
                                            'orderId' => $promo['orderId'],
                                            'moderation' => (int) $promo['moderation']
                                        ];
                                    }
                                } elseif ($blockId && isset($promo['block_id'])) {
                                    // Если service_slug пуст → сверяем по block_id
                                    if (empty($promo['service_slug']) && $promo['block_id'] == $blockId) {
                                        $conns[] = [
                                            'id' => $promo['id'],
                                            'sort' => (int) $promo['sort'],
                                            'date_start' => $promo['date_start'],
                                            'date_expires' => $promo['date_expires'],
                                            'specialist_id' => $specialist_id,
                                            'classifications_id' => $specialist_classification,
                                            'service_slug' => $serviceSlug,
                                            'block_id' => $blockId,
                                            'orderId' => $promo['orderId'],
                                            'moderation' => (int) $promo['moderation']
                                        ];
                                    }
                                }
                            }

                            // Пустой слот для нового подключения
                            $conns[] = [
                                'id' => null,
                                'sort' => 0,
                                'date_start' => '',
                                'date_expires' => '',
                                'specialist_id' => $specialist_id,
                                'classifications_id' => $specialist_classification,
                                'service_slug' => $serviceSlug,
                                'block_id' => $blockId,
                                'moderation' => (int) $promo['moderation']
                            ];

                            $jsonConns = htmlspecialchars(json_encode($conns), ENT_QUOTES, 'UTF-8');
                            $firstConn = $conns[0];
                            $isConnected = !empty($firstConn['id']) ? 1 : 0;
                            $showConnectedBtn = $isConnected && ($firstConn['moderation'] != 4);
                            $sort = (int) $firstConn['sort'];
                            $dateStart = $firstConn['date_start'] ?? '';
                            $dateExpires = $firstConn['date_expires'] ?? '';
                            $orderId = $firstConn['orderId'] ?? '';
                            ?>
                            <div class="row align-items-center border-bottom py-2">
                                <div class="col-3 service-title">
                                    <strong><?= htmlspecialchars($page['title']) ?></strong>
                                </div>
                                <div class="col-5">
                                    <span class="btn btn-sm text-white <?= $showConnectedBtn ? 'btn-info' : '' ?>">
                                        <?= $showConnectedBtn ? 'Подключено' : '' ?>
                                    </span>
                                </div>
                                <div class="col-4 text-end">
                                    <!-- Кнопка проверки -->
                                    <button type="button" style="width: 210px;" class="btn btn-sm btn-secondary check-service"
                                        data-connections="<?= $jsonConns ?>"
                                        data-id="<?= htmlspecialchars($page['service_slug'] ?: $page['block_id']) ?>"
                                        data-slug="<?= $page['title'] ?>"
                                        data-specialist-id="<?= $specialist_id ?>"
                                        data-classifications-id="<?= $specialist_classification ?>"
                                        data-service-slug="<?= htmlspecialchars($serviceSlug) ?>"
                                        data-block-id="<?= htmlspecialchars($blockId) ?>"
                                        data-user-role="<?= $userRole ?>"
                                        data-service-type="page"
                                        data-service-price-1="<?= htmlspecialchars($page['price_block1']) ?>"
                                        data-service-price-2="<?= htmlspecialchars($page['price_block2']) ?>"
                                        data-service-name="Промо '<?= htmlspecialchars($page['title']) ?>'"
                                        data-email="<?= htmlspecialchars($specialist['email']) ?>"
                                        data-order-id="<?= $orderId ?>"
                                        data-discount="<?= (int) $maxDiscount ?>">
                                        Проверить подключение
                                    </button>

                                </div>
                            </div>
                        <?php endforeach; ?>


                        <!-- Вывод промо-сервисов -->
                        <?php foreach ($services as $classification_id => $classification): ?>
                            <?php
                            // Фильтруем по классификации специалиста
                            if (
                                (in_array($specialist_classification, $group_one) && in_array($classification_id, $group_one)) ||
                                (in_array($specialist_classification, $group_two) && in_array($classification_id, $group_two))
                            ):
                            ?>
                                <?php foreach ($classification as $parent_id => $parent): ?>
                                    <?php
                                    // Если это родительская услуга (p_id = 0), выводим её
                                    if ($parent_id == 0) {
                                        foreach ($parent as $service) {
                                            $serviceSlug = $service['slug'];
                                            $conns = [];

                                            // Собираем все подключения к этой услуге
                                            foreach ($connected_promo_services as $promo) {
                                                if ($promo['service_slug'] === $serviceSlug) {
                                                    $conns[] = [
                                                        'id' => $promo['id'],
                                                        'sort' => (int) $promo['sort'],
                                                        'date_start' => $promo['date_start'],
                                                        'date_expires' => $promo['date_expires'],
                                                        'specialist_id' => $specialist_id,
                                                        'classifications_id' => $specialist_classification,
                                                        'service_slug' => $serviceSlug,
                                                        'block_id' => null,
                                                        'orderId' => $promo['orderId'],
                                                        'moderation' => (int) $promo['moderation']
                                                    ];
                                                }
                                            }

                                            // Пустой слот
                                            $conns[] = [
                                                'id' => null,
                                                'sort' => 0,
                                                'date_start' => '',
                                                'date_expires' => '',
                                                'specialist_id' => $specialist_id,
                                                'classifications_id' => $specialist_classification,
                                                'service_slug' => $serviceSlug,
                                                'block_id' => null
                                            ];

                                            $jsonConns = htmlspecialchars(json_encode($conns), ENT_QUOTES, 'UTF-8');
                                            $firstConn = $conns[0];
                                            $isConnected = !empty($firstConn['id']);
                                            $showConnectedBtn = $isConnected && ($firstConn['moderation'] != 4);
                                            $orderId = $firstConn['orderId'] ?? '';
                                    ?>
                                            <!-- Родительская услуга -->
                                            <div class="row align-items-center border-bottom py-2">
                                                <div class="col-3 service-title">
                                                    <strong><?= htmlspecialchars($service['title']) ?></strong>
                                                </div>
                                                <div class="col-5">
                                                    <span class="btn btn-sm text-white <?= $showConnectedBtn ? 'btn-info' : '' ?>">
                                                        <?= $showConnectedBtn ? 'Подключено' : '' ?>
                                                    </span>
                                                </div>
                                                <div class="col-4 text-end">
                                                    <button type="button" style="width: 210px;" class="btn btn-sm btn-secondary check-service"
                                                        data-connections="<?= $jsonConns ?>"
                                                        data-id="<?= htmlspecialchars($serviceSlug) ?>"
                                                        data-slug="<?= htmlspecialchars($service['title']) ?>"
                                                        data-specialist-id="<?= $specialist_id ?>"
                                                        data-classifications-id="<?= $specialist_classification ?>"
                                                        data-service-slug="<?= htmlspecialchars($serviceSlug) ?>"
                                                        data-block-id="<?= htmlspecialchars($blockId) ?>"
                                                        data-user-role="<?= $userRole ?>"
                                                        data-service-type="parent"
                                                        data-service-price-1="<?= htmlspecialchars($service['price_block1']) ?>"
                                                        data-service-price-2="<?= htmlspecialchars($service['price_block2']) ?>"
                                                        data-service-name="Промо '<?= htmlspecialchars($service['title']) ?>'"
                                                        data-email="<?= htmlspecialchars($specialist['email']) ?>"
                                                        data-order-id="<?= $orderId ?>"
                                                        data-discount="<?= (int) $maxDiscount ?>">
                                                        Проверить подключение
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Дочерние услуги для текущего родителя -->
                                            <?php if (isset($classification[$service['id']])): ?>
                                                <?php
                                                foreach ($classification[$service['id']] as $child_service) {
                                                    $serviceSlug = $child_service['slug'];
                                                    $conns = [];

                                                    // Собираем все подключения к дочерней услуге
                                                    foreach ($connected_promo_services as $promo) {
                                                        if ($promo['service_slug'] === $serviceSlug) {
                                                            $conns[] = [
                                                                'id' => $promo['id'],
                                                                'sort' => (int) $promo['sort'],
                                                                'date_start' => $promo['date_start'],
                                                                'date_expires' => $promo['date_expires'],
                                                                'specialist_id' => $specialist_id,
                                                                'classifications_id' => $specialist_classification,
                                                                'service_slug' => $serviceSlug,
                                                                'orderId' => $promo['orderId'],
                                                                'block_id' => null,
                                                                'moderation' => (int) $promo['moderation']
                                                            ];
                                                        }
                                                    }

                                                    // Пустой слот для нового подключения
                                                    $conns[] = [
                                                        'id' => null,
                                                        'sort' => 0,
                                                        'date_start' => '',
                                                        'date_expires' => '',
                                                        'specialist_id' => $specialist_id,
                                                        'classifications_id' => $specialist_classification,
                                                        'service_slug' => $serviceSlug,
                                                        'block_id' => null
                                                    ];

                                                    $jsonConns = htmlspecialchars(json_encode($conns), ENT_QUOTES, 'UTF-8');
                                                    $firstConn = $conns[0];
                                                    $isConnected = !empty($firstConn['id']);
                                                    $showConnectedBtn = $isConnected && ($firstConn['moderation'] != 4);
                                                    $orderId = $firstConn['orderId'] ?? '';
                                                ?>
                                                    <div class="row align-items-center border-bottom py-2">
                                                        <div class="col-3 service-title">
                                                            <div><?= htmlspecialchars($child_service['title']) ?></div>
                                                        </div>
                                                        <div class="col-5">
                                                            <span class="btn btn-sm text-white <?= $showConnectedBtn ? 'btn-info' : '' ?>">
                                                                <?= $showConnectedBtn ? 'Подключено' : '' ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-4 text-end">
                                                            <button type="button" style="width: 210px;" class="btn btn-sm btn-secondary check-service"
                                                                data-connections="<?= $jsonConns ?>"
                                                                data-id="<?= htmlspecialchars($serviceSlug) ?>"
                                                                data-slug="<?= htmlspecialchars($child_service['title']) ?>"
                                                                data-specialist-id="<?= $specialist_id ?>"
                                                                data-classifications-id="<?= $specialist_classification ?>"
                                                                data-service-slug="<?= htmlspecialchars($serviceSlug) ?>"
                                                                data-user-role="<?= $userRole ?>"
                                                                data-service-type="child"
                                                                data-service-price-1="<?= htmlspecialchars($child_service['price_block1']) ?>"
                                                                data-service-price-2="<?= htmlspecialchars($child_service['price_block2']) ?>"
                                                                data-service-name="Промо '<?= htmlspecialchars($child_service['title']) ?>'"
                                                                data-email="<?= htmlspecialchars($specialist['email']) ?>"
                                                                data-order-id="<?= $orderId ?>"
                                                                data-discount="<?= (int) $maxDiscount ?>">
                                                                Проверить подключение
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php
                                                }
                                                ?>

                                            <?php endif; ?>
                                    <?php
                                        }
                                    }
                                    ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Опции -->
                    <div class="options mb-5">
                        <h4>Опции</h4>
                        <?php
                        $hasProExpert = in_array(2, $connectedTariffIds) || in_array(3, $connectedTariffIds);

                        $hasBasic = in_array(1, $connectedTariffIds);  // Basic тариф

                        $connectedOptionMap = [];
                        if (is_array($optionsList)) {
                            foreach ($optionsList as $item) {
                                $connectedOptionMap[$item['option_id']] = $item;
                            }
                        }

                        $connectOption = current($connectedOptionMap) ?: [];
                        foreach ($options as $option):
                            // вручную подключённая?
                            $isManuallyConnected = in_array($option['id'], $connectedOptionIds);

                            // есть ли в тарифе?
                            $includedInTariff = ($hasProExpert && in_array($option['id'], [1, 2, 3, 4, 5])) ||
                                ($hasBasic && in_array($option['id'], [1, 2, 3]));

                            // Если опция входит в тариф, показываем как "Включено в тариф"
                            if ($includedInTariff) {
                                $badgeClass = 'btn-info';
                                $badgeText = 'Включено в тариф';
                                $checkStatus = 'checked';
                                $connetctButtonStatus = 'disabled';
                            } elseif ($isManuallyConnected) {
                                $badgeClass = 'btn-info';
                                $badgeText = 'Подключено';
                                $checkStatus = 'checked';
                                $connetctButtonStatus = 'disabled';
                            } else {
                                $badgeClass = '';
                                $badgeText = '';
                                $checkStatus = '';
                                $connetctButtonStatus = '';
                            }

                            $optionPrice = (float) ($option['price'] ?? 0);
                            $tariffId = $firstTariff['tariff_id'] ?? null;

                            if ($tariffId == 1) {
                                $optionPrice = $optionPrice * 0.9;  // скидка 10%
                            }

                            // Округляем до целого
                            $optionPrice = round($optionPrice, 0);
                        ?>

                            <div class="row align-items-center border-bottom py-2">
                                <div class="col-3">
                                    <strong><?= htmlspecialchars($option['name']) ?></strong>
                                </div>
                                <div class="col-3">
                                    <button
                                        class="btn btn-sm text-white text-start <?= $badgeClass ?>"><?= $badgeText ?></button>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="d-inline-flex align-items-center gap-5 mb-3">
                                        <label class="form-check-label mb-0">
                                            <input type="checkbox" class="duration-checkbox"
                                                name="duration_<?= $option['id'] ?>" value="1" <?= $checkStatus ?>>
                                            1 месяц
                                        </label>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-success connect" data-type="option"
                                            data-id="<?= $option['id'] ?>" data-specialist-id="<?= $specialist_id ?>" data-price="<?= $option['price'] ?>" data-discount="<?= (int) $maxDiscount ?>"
                                            <?= $connetctButtonStatus ?>>
                                            Подключить
                                        </button>
                                        <?php if ($userRole == 'superadmin'): ?>
                                            <button type="button" class="btn btn-sm btn-danger disconnect" data-type="option"
                                                data-id="<?= isset($connectedOptionMap[$option['id']]) ? $connectedOptionMap[$option['id']]['id'] : '' ?>"
                                                data-specialist-id="<?= $specialist_id ?>"
                                                <?= !isset($connectedOptionMap[$option['id']]) ? 'disabled' : '' ?>>
                                                Отключить
                                            </button>
                                        <?php endif; ?>
                                        <button type="button"
                                            class="btn btn-sm btn-primary send-invoice"
                                            data-type="option"
                                            data-id="<?= $option['id'] ?>"
                                            data-inn="<?= $specialist['inn'] ?>"
                                            data-specialist-id="<?= $specialist_id ?>"
                                            data-duration-checkbox="duration_<?= $option['id'] ?>"
                                            data-price="<?= htmlspecialchars(number_format($optionPrice, 2, '.', '')) ?>"
                                            data-email="<?= htmlspecialchars($specialist['email'] ?? '') ?>"
                                            data-order-id="<?= htmlspecialchars($connectOption['orderId'] ?? '') ?>"
                                            data-tariff-id="<?= htmlspecialchars($tariffId ?? '') ?>"
                                            data-customer-type="ip"
                                            <?= !isset($connectedOptionMap[$option['id']]) ? 'disabled' : '' ?>>
                                            Отправить счет
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>

                <?php
                $banners = get_banners();
                $connected_banners = get_connected_banners($specialist_id);

                // Для баннеров журнала
                $journals = get_journals();
                $connected_journals = get_connected_journals($specialist_id);

                $today = date('Y-m-d');

                // Действующие подключения для слайдер-баннеров: размещённые и текущая дата в периоде размещения
                $activeBannerConnections = array_filter($connected_banners, function ($row) use ($today) {
                    return $row['moderation'] == 0 &&
                        !empty($row['start_date']) &&
                        !empty($row['end_date']) &&
                        $today <= $row['end_date'];
                });

                // Действующие подключения для журналов: размещённые и текущая дата в периоде размещения
                $activeJournalConnections = array_filter($connected_journals, function ($row) use ($today) {
                    return $row['moderation'] == 0 &&
                        !empty($row['start_date']) &&
                        !empty($row['end_date']) &&
                        $today <= $row['end_date'];
                });

                // Функция-помощник: разбиваем заявки на видимые и скрытые (после 5)
                function split_requests(array $requests, int $limit = 5): array
                {
                    $visible = array_slice($requests, 0, $limit);
                    $hidden = array_slice($requests, $limit);
                    return ['visible' => $visible, 'hidden' => $hidden];
                }

                // Из подключённых баннеров выбираем те, где moderation == 1 (запрос)
                $bannerRequests = array_filter($connected_banners, function ($row) {
                    return in_array($row['moderation'], [0, 1, 2]);
                });

                $bannerRequestIds = array_column($bannerRequests, 'banner_id');

                // Фильтрация баннеров по разрешённым ID (например, разрешены 1..5)
                $allowedSliderBannerIds = [1, 2, 3, 4, 5];
                $filteredBanners = array_filter($banners, function ($banner) use ($allowedSliderBannerIds) {
                    return in_array($banner['id'], $allowedSliderBannerIds);
                });

                $journalRequests = array_filter($connected_journals, function ($row) {
                    return in_array($row['moderation'], [0, 1, 2]);
                });
                $journalRequestIds = array_column($journalRequests, 'journal_id');

                $journalConnected = array_filter($connected_journals, function ($row) {
                    return $row['moderation'] == 0;
                });

                $journalConnectedIds = array_column($journalConnected, 'journal_id');

                $allArchivedBanners = get_archived_banners($specialist_id);
                // Первые 5
                $archivedBanners = get_archived_banners($specialist_id, 5);

                ?>
                <div class="tab-pane fade" id="content-banners" role="tabpanel" aria-labelledby="tab-reviews">
                    <!-- Действующие подключения -->
                    <div class="active-connections border border-success rounded mb-4 p-4 pb-1 bg-light">
                        <h4 class="mb-4">Действующие и запланированные размещения</h4>
                        <!-- Подзаголовок для слайдер-баннеров -->
                        <strong>Баннеры-слайдеры на главной</strong>
                        <?php if (!empty($activeBannerConnections)): ?>
                            <ul class="list-group mb-4">
                                <?php foreach ($activeBannerConnections as $conn): ?>
                                    <?php
                                    $banner = current(array_filter($banners, fn($b) => $b['id'] == $conn['banner_id']));
                                    $name = $banner['name'] ?? 'Неизвестный баннер';
                                    $start = date('d.m.Y', strtotime($conn['start_date']));
                                    $end = date('d.m.Y', strtotime($conn['end_date']));
                                    $isFuture = ($today >= $conn['start_date'] && $today <= $conn['end_date']) ? '' : '';
                                    ?>
                                    <div class="mb-1 pt-2 pb-2 <?= $isFuture ?> rounded d-flex align-items-center"
                                        style="gap: 1rem;">
                                        <div style="width: 250px; flex-shrink: 0;">
                                            <?= htmlspecialchars($name) ?>
                                        </div>
                                        <div style="width: 200px; flex-shrink: 0;">
                                            с <?= htmlspecialchars($start) ?> по <?= htmlspecialchars($end) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <ul class="list-group mb-4">
                                <div class="mb-1 pt-2 pb-2">Подключений нет</div>
                            </ul>
                        <?php endif; ?>

                        <!-- Подзаголовок для журналов -->
                        <strong>Баннеры в разделе «Журнал»</strong>
                        <?php if (!empty($activeJournalConnections)): ?>
                            <ul class="list-group mb-4">
                                <?php foreach ($activeJournalConnections as $conn): ?>
                                    <?php
                                    $journal = current(array_filter($journals, fn($j) => $j['id'] == $conn['journal_id']));
                                    $title = $journal['name'] ?? 'Неизвестный журнал';
                                    $start = date('d.m.Y', strtotime($conn['start_date']));
                                    $end = date('d.m.Y', strtotime($conn['end_date']));
                                    $isFuture = ($today >= $conn['start_date'] && $today <= $conn['end_date']) ? '' : '';
                                    ?>
                                    <div class="mb-1 pt-2 pb-2 <?= $isFuture ?> d-flex align-items-center" style="gap: 1rem;">
                                        <div style="width: 250px; flex-shrink: 0;">
                                            <?= htmlspecialchars($title) ?>
                                        </div>
                                        <div style="width: 200px; flex-shrink: 0;">
                                            с <?= htmlspecialchars($start) ?> по <?= htmlspecialchars($end) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <ul class="list-group mb-4">
                                <div class="mb-1 pt-2 pb-2">Подключений нет</div>
                            </ul>
                        <?php endif; ?>

                    </div>
                    <!-- Слайдер‑баннеры на главной -->
                    <div class="banners-list mb-5">
                        <h4>Баннеры-слайдеры на главной</h4>

                        <?php foreach ($filteredBanners as $banner): ?>
                            <?php
                            // Все заявки по текущему баннеру
                            $requestsAll = array_filter($connected_banners, fn($req) => $req['banner_id'] == $banner['id']);
                            $countAll = count($requestsAll);

                            // Разделяем заявки на первые 5 и остальные
                            $parts = split_requests($requestsAll, 5);
                            $visibleReqs = $parts['visible'];
                            $hiddenReqs = $parts['hidden'];
                            ?>

                            <?php if (
                                (in_array($specialist_classification, ['1', '2', '5', '6', '7']) && in_array($banner['id'], [1, 2])) ||
                                (in_array($specialist_classification, ['3', '8']) && in_array($banner['id'], [1, 3])) ||
                                ($specialist_classification == '4' && in_array($banner['id'], [1, 4]))
                            ): ?>
                                <div class="banner-item border-bottom py-2" id="banner-item-<?= $banner['id'] ?>">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <strong><?= htmlspecialchars($banner['name']) ?></strong>
                                        </div>
                                        <div class="col-4 text-end">
                                            <?php if ($countAll > 0): ?>
                                                <?php
                                                $pendingRequests = array_filter($requestsAll, fn($r) => $r['moderation'] != 0);
                                                $placedRequests = array_filter($requestsAll, fn($r) => $r['moderation'] == 0);

                                                $hasPending = count($pendingRequests) > 0;

                                                if ($hasPending) {
                                                    $buttonClass = 'btn-info';
                                                    $buttonText = 'Запрос на подключение';
                                                    $countToShow = count($pendingRequests);
                                                } else {
                                                    $buttonClass = 'btn-dark';
                                                    $buttonText = 'Размещен';
                                                    $countToShow = count($requestsAll);  // == count($placedRequests)
                                                }
                                                ?>
                                                <button class="btn <?= $buttonClass ?> text-white w-100 toggle-banner-details"
                                                    data-banner-id="<?= $banner['id'] ?>">
                                                    <?= $buttonText ?> <?= $countToShow > 1 ? " ({$countToShow})" : '' ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Детали заявок -->
                                    <?php if ($countAll > 0): ?>
                                        <div class="banner-request-details-list mt-2" id="banner-details-<?= $banner['id'] ?>"
                                            style="display: none;">
                                            <?php foreach ($visibleReqs as $bannerData): ?>
                                                <?php
                                                // Декодируем описание баннера
                                                $desc = json_decode($bannerData['description'] ?? '', true) ?? [];
                                                $ad_line1 = $desc['ad_line1'] ?? '';
                                                $ad_line2 = $desc['ad_line2'] ?? '';
                                                $ad_line3 = $desc['ad_line3'] ?? '';
                                                $startDate = $bannerData['start_date'] ?? '';
                                                $endDate = $bannerData['end_date'] ?? '';
                                                ?>
                                                <div class="card p-3 mb-3">
                                                    <?php if (isset($bannerData['moderation'])): ?>
                                                        <?php
                                                        $moderation = $bannerData['moderation'];
                                                        $statusText = $moderation == 0 ? 'Размещен' : 'Ожидает модерации';
                                                        $statusClass = $moderation == 0 ? 'text-success' : 'text-warning';
                                                        ?>
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <div>
                                                                <strong>Дата запроса:
                                                                </strong><?= date('d.m.Y', strtotime($bannerData['connected_at'])) ?>
                                                            </div>
                                                            <div class="mb-2 text-start fw-bold <?= $statusClass ?>">
                                                                Статус: <?= $statusText ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="form-group mb-2">
                                                        <label>Текст баннера (строка 1):</label>
                                                        <input type="text" class="form-control ad_line1"
                                                            value="<?= htmlspecialchars($ad_line1) ?>">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label>Текст баннера (строка 2):</label>
                                                        <input type="text" class="form-control ad_line2"
                                                            value="<?= htmlspecialchars($ad_line2) ?>">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label>Текст баннера (строка 3):</label>
                                                        <input type="text" class="form-control ad_line3"
                                                            value="<?= htmlspecialchars($ad_line3) ?>">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label>Период размещения:</label>
                                                        <div class="d-flex">
                                                            <input type="date" class="form-control me-2 start_date"
                                                                style="max-width: 200px;" value="<?= htmlspecialchars($startDate) ?>">
                                                            <input type="date" class="form-control end_date" style="max-width: 200px;"
                                                                value="<?= htmlspecialchars($endDate) ?>">
                                                        </div>
                                                    </div>
                                                    <div class="text-end mt-2">
                                                        <button type="button" class="btn btn-danger manage-request"
                                                            data-id="<?= $bannerData['id'] ?>" data-type="banner"
                                                            data-action="delete">Отклонить</button>
                                                        <button type="button" class="btn btn-success manage-request"
                                                            data-id="<?= $bannerData['id'] ?>" data-type="banner"
                                                            data-action="publish">Разместить</button>
                                                        <button type="button" class="btn btn-primary send-invoice-banner-request"
                                                            data-banner-id="<?= $banner['id'] ?>">Отправить счет</button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>

                                            <?php if (!empty($hiddenReqs)): ?>
                                                <?php foreach ($hiddenReqs as $bannerData): ?>
                                                    <?php
                                                    $desc = json_decode($bannerData['description'] ?? '', true) ?? [];
                                                    $ad_line1 = $desc['ad_line1'] ?? '';
                                                    $ad_line2 = $desc['ad_line2'] ?? '';
                                                    $ad_line3 = $desc['ad_line3'] ?? '';
                                                    $startDate = $bannerData['start_date'] ?? '';
                                                    $endDate = $bannerData['end_date'] ?? '';
                                                    ?>
                                                    <div class="card p-3 mb-3 hidden-request-<?= $banner['id'] ?>" style="display: none;">
                                                        <?php if (isset($bannerData['moderation'])): ?>
                                                            <?php
                                                            $moderation = $bannerData['moderation'];
                                                            $statusText = $moderation == 0 ? 'Размещен' : 'Ожидает модерации';
                                                            $statusClass = $moderation == 0 ? 'text-success' : 'text-warning';
                                                            ?>
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <div>
                                                                    <strong>Дата запроса:
                                                                    </strong><?= date('d.m.Y', strtotime($bannerData['connected_at'])) ?>
                                                                </div>
                                                                <div class="mb-2 text-start fw-bold <?= $statusClass ?>">
                                                                    Статус: <?= $statusText ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="form-group mb-2">
                                                            <label>Текст баннера (строка 1):</label>
                                                            <input type="text" class="form-control ad_line1"
                                                                value="<?= htmlspecialchars($ad_line1) ?>">
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Текст баннера (строка 2):</label>
                                                            <input type="text" class="form-control ad_line2"
                                                                value="<?= htmlspecialchars($ad_line2) ?>">
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Текст баннера (строка 3):</label>
                                                            <input type="text" class="form-control ad_line3"
                                                                value="<?= htmlspecialchars($ad_line3) ?>">
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Период размещения:</label>
                                                            <div class="d-flex">
                                                                <input type="date" class="form-control me-2 start_date"
                                                                    style="max-width: 200px;" value="<?= htmlspecialchars($startDate) ?>">
                                                                <input type="date" class="form-control end_date" style="max-width: 200px;"
                                                                    value="<?= htmlspecialchars($endDate) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="text-end mt-2">
                                                            <button type="button" class="btn btn-danger manage-request"
                                                                data-id="<?= $bannerData['id'] ?>" data-type="banner"
                                                                data-action="delete">Отклонить</button>
                                                            <button type="button" class="btn btn-success manage-request"
                                                                data-id="<?= $bannerData['id'] ?>" data-type="banner"
                                                                data-action="publish">Разместить</button>
                                                            <button type="button" class="btn btn-primary send-invoice-banner-request"
                                                                data-banner-id="<?= $banner['id'] ?>">Отправить счет</button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>

                                                <div class="text-center">
                                                    <button class="btn btn-info text-white show-all-requests"
                                                        data-banner-id="<?= $banner['id'] ?>">
                                                        Показать все (<?= count($hiddenReqs) ?>)
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Баннеры журнала -->
                    <div class="journals-list mb-5">
                        <h4>Баннеры в разделе «Журнал»</h4>
                        <?php foreach ($journals as $journal): ?>
                            <?php
                            $allJ = array_filter($connected_journals, fn($r) => $r['journal_id'] == $journal['id']);
                            $cntJ = count($allJ);
                            $partsJ = split_requests($allJ);
                            $visJ = $partsJ['visible'];
                            $hidJ = $partsJ['hidden'];
                            ?>
                            <div class="journal-item border-bottom py-2" id="journal-item-<?= $journal['id'] ?>">
                                <div class="row align-items-center">
                                    <div class="col-8"><strong><?= htmlspecialchars($journal['name']) ?></strong></div>
                                    <div class="col-4 text-end">
                                        <?php
                                        if ($cntJ > 0):
                                            $pendingJ = array_filter($allJ, fn($r) => isset($r['moderation']) && $r['moderation'] != 0);
                                            $placedJ = array_filter($allJ, fn($r) => isset($r['moderation']) && $r['moderation'] == 0);

                                            $hasPendingJ = count($pendingJ) > 0;

                                            // Определяем текст и число для отображения
                                            if ($hasPendingJ) {
                                                $clsJ = 'btn-info';
                                                $txtJ = 'Запрос на подключение';
                                                $countToShowJ = count($pendingJ);
                                            } else {
                                                $clsJ = 'btn-dark';
                                                $txtJ = 'Размещен';
                                                $countToShowJ = count($allJ);  // или count($placedJ), это то же самое в этом случае
                                            }
                                        ?>
                                            <button class="btn <?= $clsJ ?> text-white w-100 toggle-journal-details"
                                                data-journal-id="<?= $journal['id'] ?>">
                                                <?= $txtJ ?> <?= $countToShowJ > 1 ? " ({$countToShowJ})" : '' ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($cntJ > 0): ?>
                                    <div class="journal-request-details-list mt-2" id="journal-details-<?= $journal['id'] ?>"
                                        style="display:none;border:1px solid #ddd;padding:15px;">
                                        <?php foreach ($visJ as $data): ?>
                                            <?php $d = json_decode($data['description'] ?? '[]', true) ?: []; ?>
                                            <div class="card p-3 mb-3">
                                                <?php
                                                if (isset($data['moderation'])):
                                                    $mod = $data['moderation'];
                                                    $status = $mod == 0 ? 'Размещен' : 'Ожидает модерации';
                                                    $cl = $mod == 0 ? 'text-success' : 'text-warning';
                                                ?>
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div>
                                                            <strong>Дата запроса:
                                                            </strong><?= date('d.m.Y', strtotime($data['connected_at'])) ?>
                                                        </div>
                                                        <div class="mb-2 fw-bold <?= $cl ?>">Статус: <?= $status ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="form-group mb-2"><label>Текст журнала (1):</label><input
                                                        class="form-control ad_line1"
                                                        value="<?= htmlspecialchars($d['ad_line1'] ?? '') ?>">
                                                </div>
                                                <div class="form-group mb-2"><label>Текст журнала (2):</label><input
                                                        class="form-control ad_line2"
                                                        value="<?= htmlspecialchars($d['ad_line2'] ?? '') ?>">
                                                </div>
                                                <div class="form-group mb-2"><label>Текст журнала (3):</label><input
                                                        class="form-control ad_line3"
                                                        value="<?= htmlspecialchars($d['ad_line3'] ?? '') ?>">
                                                </div>
                                                <div class="form-group mb-2"><label>Период размещения:</label>
                                                    <div class="d-flex"><input type="date" class="form-control me-2 start_date"
                                                            style="max-width:200px;"
                                                            value="<?= htmlspecialchars($data['start_date'] ?? '') ?>"
                                                            placeholder="Начало"><input type="date" class="form-control end_date"
                                                            style="max-width:200px;"
                                                            value="<?= htmlspecialchars($data['end_date'] ?? '') ?>"
                                                            placeholder="Окончание"></div>
                                                </div>
                                                <div class="text-end mt-2">
                                                    <button class="btn btn-danger manage-request" data-id="<?= $data['id'] ?>"
                                                        data-type="journal" data-action="delete">Отклонить</button>
                                                    <button class="btn btn-success manage-request" data-id="<?= $data['id'] ?>"
                                                        data-type="journal" data-action="publish">Разместить</button>
                                                    <button class="btn btn-primary send-invoice-journal-request"
                                                        data-journal-id="<?= $journal['id'] ?>">Отправить счет</button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if ($hidJ): ?>
                                            <?php foreach ($hidJ as $data): ?>
                                                <?php $d = json_decode($data['description'] ?? '[]', true) ?: []; ?>
                                                <div class="card p-3 mb-3 hidden-journal-<?= $journal['id'] ?>" style="display:none;">
                                                    <?php
                                                    if (isset($data['moderation'])):
                                                        $mod = $data['moderation'];
                                                        $status = $mod == 0 ? 'Размещен' : 'Ожидает модерации';
                                                        $cl = $mod == 0 ? 'text-success' : 'text-warning';
                                                    ?>
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <div>
                                                                <strong>Дата запроса:
                                                                </strong><?= date('d.m.Y', strtotime($data['connected_at'])) ?>
                                                            </div>
                                                            <div class="mb-2 fw-bold <?= $cl ?>">Статус: <?= $status ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="form-group mb-2"><label>Текст журнала (1):</label><input
                                                            class="form-control" value="<?= htmlspecialchars($d['ad_line1'] ?? '') ?>">
                                                    </div>
                                                    <div class="form-group mb-2"><label>Текст журнала (2):</label><input
                                                            class="form-control" value="<?= htmlspecialchars($d['ad_line2'] ?? '') ?>">
                                                    </div>
                                                    <div class="form-group mb-2"><label>Текст журнала (3):</label><input
                                                            class="form-control" value="<?= htmlspecialchars($d['ad_line3'] ?? '') ?>">
                                                    </div>
                                                    <div class="form-group mb-2"><label>Период размещения:</label>
                                                        <div class="d-flex"><input type="date" class="form-control me-2"
                                                                style="max-width:200px;"
                                                                value="<?= htmlspecialchars($data['start_date'] ?? '') ?>"><input
                                                                type="date" class="form-control" style="max-width:200px;"
                                                                value="<?= htmlspecialchars($data['end_date'] ?? '') ?>"></div>
                                                    </div>
                                                    <div class="text-end mt-2">
                                                        <button class="btn btn-danger manage-request" data-id="<?= $data['id'] ?>"
                                                            data-type="journal" data-action="delete">Отклонить</button>
                                                        <button class="btn btn-success manage-request" data-id="<?= $data['id'] ?>"
                                                            data-type="journal" data-action="publish">Разместить</button>
                                                        <button class="btn btn-primary send-invoice-journal-request"
                                                            data-journal-id="<?= $journal['id'] ?>">Отправить счет</button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="text-center"><button
                                                    class="btn btn-info text-white show-all-journal-requests"
                                                    data-journal-id="<?= $journal['id'] ?>">Показать все
                                                    (<?= count($hidJ) ?>)</button></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Архивный список -->
                    <div class="archived-banner-list mb-5">
                        <h4>
                            Архивные размещения
                            <a href="#" id="toggle-all-archive" class="ms-2"
                                style="font-size: 14px; color: #000;">(развернуть все)</a>
                        </h4>

                        <?php if (count($archivedBanners) > 0): ?>
                            <?php foreach ($archivedBanners as $archive): ?>
                                <div class="card my-3" id="archive-item-<?= $archive['id'] ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <?php
                                        $decodedDescription = json_decode($archive['description'], true);
                                        if ($archive['type'] === 'banner') {
                                            $title = get_banner_title_by_id((int) $archive['object_id']);
                                        } else {
                                            $title = get_journal_title_by_id((int) $archive['object_id']);
                                        }

                                        // Определяем статус
                                        if ($archive['rejected'] == 1) {
                                            $statusText = '<span class="text-danger fw-bold">Статус: Отклонен</span>';
                                        } else {
                                            $statusText = '<span class="text-success fw-bold">Статус: Опубликован</span>';
                                        }
                                        ?>
                                        <span>Архивный запрос: <p> <?= htmlspecialchars($title) ?></p></span>
                                        <button class="btn btn-dark bg-dark toggle-archive"
                                            data-archive-id="<?= $archive['id'] ?>">
                                            Подробнее
                                        </button>
                                    </div>
                                    <div class="card-body archive-details" id="archive-details-<?= $archive['id'] ?>"
                                        style="display: none;">
                                        <div class="d-flex justify-content-between mb-3">
                                            <div>
                                                <strong>Дата запроса:
                                                </strong><?= date('d.m.Y', strtotime($archive['connected_at'])) ?>
                                            </div>
                                            <?= $statusText ?>
                                        </div>
                                        <p><strong>Период:</strong> с
                                            <?= date('d.m.Y', strtotime($archive['start_date'])) ?> по
                                            <?= date('d.m.Y', strtotime($archive['end_date'])) ?>
                                        </p>
                                        <p><strong>Описание:</strong></p>
                                        <?php if (is_array($decodedDescription)): ?>
                                            <?php foreach ($decodedDescription as $line): ?>
                                                <p><?= htmlspecialchars($line) ?></p>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p><em>Описание отсутствует</em></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (count($allArchivedBanners) > count($archivedBanners)): ?>
                                <p class="text-center mt-4">
                                    <button id="loadFullArchiveBanners" class="btn btn-primary">Показать все</button>
                                </p>
                            <?php endif; ?>

                        <?php else: ?>
                            <p>Нет архивных размещений</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $specialistRow = get_specialist($specialist_id);
                $specialistOther = json_decode($specialistRow['other'] ?? '', true) ?: [];
                $specialistEmail = $specialistOther['email'] ?? '';
                $specialistLoginEmail = $specialistRow['email'];
                $specialistInn = get_specialist_inn($specialist_id);
                $requestPrice = get_request_price();
                $specRequests = get_specialist_requests($specialist_id);

                $monthsRu = [1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

                function spec_req_status(array $r): array
                {
                    $key = request_status_key($r);
                    [$label, $cls] = request_statuses()[$key];
                    $selectable = !in_array($key, ['new', 'spam'], true); // галочка только у заявок, по которым можно выставлять счёт
                    return [$label, $cls, $selectable, $key];
                }

                // группировка по месяцам (запросы уже отсортированы по дате DESC)
                $grouped = [];
                foreach ($specRequests as $r) {
                    $ts = strtotime($r['created_at']);
                    $key = date('Y-m', $ts);
                    $grouped[$key]['title'] = $monthsRu[(int)date('n', $ts)] . ' ' . date('Y', $ts);
                    $grouped[$key]['items'][] = $r;
                }
                ?>

                <div class="tab-pane fade" id="content-requests" role="tabpanel" aria-labelledby="tab-requests">
                    <div class="requests-section mb-5"
                        data-specialist-id="<?= (int)$specialist_id ?>"
                        data-inn="<?= htmlspecialchars($specialistInn) ?>"
                        data-email="<?= htmlspecialchars($specialistEmail) ?>"
                        data-login-email="<?= htmlspecialchars($specialistLoginEmail) ?>"
                        data-request-price="<?= $requestPrice ?>">
                        <h4>Заявки</h4>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <label for="req-period" class="mb-0">Период:</label>
                                <input type="text" id="req-period" class="form-control form-control-sm w-auto"
                                    placeholder="Выберите период" readonly>
                                <button type="button" id="req-period-clear" class="btn btn-sm btn-outline-secondary"
                                    title="Сбросить период">&times;</button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="spec-req-filter" class="mb-0">Фильтр:</label>
                                <select id="spec-req-filter" class="form-select form-select-sm w-auto">
                                    <option value="all">Все</option>
                                    <?php foreach (request_statuses() as $key => [$label, $cls]): ?>
                                        <option value="<?= $key ?>" class="<?= $cls ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                            <span class="me-2">Действие/статус:</span>
                            <button type="button" class="btn btn-sm btn-outline-primary req-invoice"
                                data-status="invoice_sent" disabled>Отправить счет</button>
                            <button type="button" class="btn btn-sm btn-outline-success req-action"
                                data-status="paid" disabled>Оплачена</button>
                            <button type="button" class="btn btn-sm btn-outline-warning req-action"
                                data-status="unpaid" disabled>Не оплачена</button>
                        </div>

                        <?php if (empty($grouped)): ?>
                            <p class="text-muted">Заявок пока нет</p>
                        <?php endif; ?>

                        <?php $firstMonth = true; ?>
                        <?php foreach ($grouped as $key => $group): ?>
                            <div class="req-month mb-4" data-month="<?= $key ?>">
                                <div class="req-month-head d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="mb-0" style="font-size: 22px;">
                                        <?= $group['title'] ?>
                                        <a href="#" style="font-size: 14px;" class="req-toggle-month small ms-2 text-dark">(развернуть все)</a>
                                    </h4>

                                    <span class="req-controls <?= $firstMonth ? '' : 'd-none' ?>">
                                        <a href="#" class="req-select-all">Выбрать все</a> /
                                        <a href="#" class="req-reset">Сбросить</a>
                                    </span>
                                </div>

                                <?php foreach ($group['items'] as $r):
                                    [$stText, $stClass, $selectable, $stKey] = spec_req_status($r);
                                    $ts = strtotime($r['created_at']);
                                    $highlight = '';
                                    $r['moderation'] == 3 ? $highlight = 'highlight' : '';
                                ?>
                                    <div class="req-item" data-date="<?= date('Y-m-d', $ts) ?>" data-status="<?= $stKey ?>" data-initial-status="<?= $stKey ?>">
                                        <div class="d-flex align-items-start gap-3 mb-2">
                                            <div class="card flex-grow-1 overflow-hidden">
                                                <div class="card-header py-2 d-flex justify-content-between align-items-center <?= $highlight ?>">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php if ($selectable): ?>
                                                            <input type="checkbox" class="form-check-input border-dark mt-0 req-check"
                                                                value="<?= (int)$r['id'] ?>">
                                                        <?php else: ?>
                                                            <input type="checkbox" class="form-check-input mt-0 invisible" disabled>
                                                        <?php endif; ?>
                                                        <span><?= date('d.m.Y', $ts) ?></span>
                                                        <span>ID <?= (int)$r['id'] ?></span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <span class="req-status <?= $stClass ?>"><?= $stText ?></span>
                                                        <button type="button" class="btn btn-dark toggle-req-details"
                                                            data-request-id="<?= (int)$r['id'] ?>">Подробнее</button>
                                                    </div>
                                                </div>

                                                <div class="card-body d-none" id="req-details-<?= (int)$r['id'] ?>">
                                                    <p><strong>Дата заявки:</strong> <?= date('d.m.Y H:i', $ts) ?></p>
                                                    <p><strong>ID заявки:</strong> <?= (int)$r['id'] ?></p>
                                                    <p><strong>Наименование специалиста:</strong> <?= htmlspecialchars($r['specialist_name'] ?? '-') ?></p>
                                                    <p><strong>ID специалиста:</strong> <?= (int)$r['specialist_id'] ?></p>
                                                    <p><strong>Имя в заявке:</strong> <?= htmlspecialchars($r['name']) ?></p>
                                                    <p><strong>Номер телефона:</strong>
                                                        <?php if (filter_var($r['phone'], FILTER_VALIDATE_EMAIL)): ?>
                                                            <a href="mailto:<?= htmlspecialchars($r['phone']) ?>"><?= htmlspecialchars($r['phone']) ?></a>
                                                        <?php else: ?>
                                                            <a href="tel:<?= htmlspecialchars(preg_replace('/[^\d+]/', '', $r['phone'])) ?>"><?= htmlspecialchars($r['phone']) ?></a>
                                                        <?php endif; ?>
                                                    </p>
                                                    <p class="mb-0"><strong>Информация о занятии:</strong>
                                                        <span class="text-break"><?= nl2br(htmlspecialchars($r['message'])) ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php $firstMonth = false; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php
                $tariffs = get_pro_photo();
                $allProPhotoRequests = get_pro_photo_list();
                $activeProPhotoRequests = array_filter($allProPhotoRequests, function ($req) use ($specialist_id) {
                    return $req['specialist_id'] == $specialist_id && in_array($req['moderation'], [1, 2]);
                });
                $archiveProPhotoRequests = array_filter($allProPhotoRequests, function ($req) use ($specialist_id) {
                    return $req['specialist_id'] == $specialist_id && $req['moderation'] == 0;
                });
                $visibleArchiveProPhoto = array_slice($archiveProPhotoRequests, 0, 5);
                $hasMoreArchiveProPhoto = count($archiveProPhotoRequests) > 5;
                $tariffTitles = [];
                foreach ($tariffs as $tariff) {
                    $tariffTitles[$tariff['id']] = strip_tags(html_entity_decode($tariff['title']));
                }
                $tariffId = 4;
                $tariffEmpPrice = array_filter($tariffs, function ($tariff) use ($tariffId) {
                    return $tariff['id'] == $tariffId;
                });
                $tariffEmpPrice = array_shift($tariffEmpPrice);

                $hasConductedPhotos = count($archiveProPhotoRequests) > 0;
                $hide_discount = false;

                if ($hasConductedPhotos) {
                    // Есть хотя бы один модераторский отчёт => скидка выключена
                    $hide_discount = true;
                } elseif (!empty($specialist['activated_at'])) {
                    // Проверяем, не прошло ли 14 дней с регистрации
                    $registeredTs = strtotime($specialist['activated_at']);
                    if (time() - $registeredTs >= 14 * 24 * 60 * 60) {
                        $hide_discount = true;
                    }
                }

                ?>
                <!-- Про-ФОТО -->
                <div class="tab-pane fade" id="content-pro-photo" role="tabpanel" aria-labelledby="tab-reviews">
                    <div class="pro-photo-section mb-5">
                        <h4>Фотосъемка</h4>
                        <div class="tariffs-list">
                            <?php foreach ($tariffs as $tariff): ?>
                                <div class="row align-items-center border-bottom py-2">
                                    <div class="col-8">
                                        <?php if ($tariff['id'] == 4): ?>
                                            <strong>Съемка сотрудников</strong>
                                        <?php else: ?>
                                            <strong><?= strip_tags(html_entity_decode($tariff['title'])) ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-4 text-end">
                                        <?php
                                        // Находим активные запросы, соответствующие текущему тарифу
                                        $proPhotoRequests = array_filter($activeProPhotoRequests, function ($req) use ($tariff) {
                                            $parts = explode('_', $req['pro_photo_id']);
                                            $main_id = $parts[0];  // Основной ID тарифа
                                            return $main_id == $tariff['id'];
                                        });

                                        // Считаем количество запросов
                                        $requestCount = count($proPhotoRequests);

                                        if ($requestCount > 0):
                                        ?>
                                            <button type="button" class="btn btn-info text-white w-100 toggle-pro-photo-details"
                                                data-request-id="<?= $tariff['id'] ?>">
                                                Запрос на фотосъемку <?= $requestCount > 1 ? "($requestCount)" : '' ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($requestCount > 0): ?>
                                    <div class="pro-photo-details mt-3" id="pro-photo-request-details-<?= $tariff['id'] ?>"
                                        style="display: none; margin-left: 20px;">

                                        <?php
                                        foreach ($proPhotoRequests as $proPhotoRequest):
                                            // Определяем вид фотосъемки (если есть доп. цифра)
                                            $photo_type = '';
                                            $parts = explode('_', $proPhotoRequest['pro_photo_id']);
                                            if (isset($parts[1])) {
                                                $type_id = $parts[1];  // Вытаскиваем номер вида съемки
                                                $photo_type = $tariffTitles[$type_id] ?? 'Неизвестный формат';
                                                if (isset($tariffs[$type_id]['price'])) {
                                                    $tariff_price = strip_tags(html_entity_decode($tariffs[$type_id]['price']));
                                                }
                                            }
                                        ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div>
                                                            <strong>Дата запроса:
                                                            </strong><?= date('d.m.Y', strtotime($proPhotoRequest['connected_at'])) ?>
                                                        </div>
                                                        <div class="mb-2 text-start fw-bold text-warning">
                                                            Статус: На рассмотрении
                                                        </div>
                                                    </div>
                                                    <?php if ($photo_type): ?>
                                                        <p><strong>Вид фотосъемки:</strong> <?= $photo_type ?></p>
                                                    <?php endif; ?>
                                                    <?php if ($proPhotoRequest['pro_photo_id'] != '4'): ?>
                                                        <?php if ($tariff_price): ?>
                                                            <p><strong>Стоимость:</strong> <?= htmlspecialchars($tariff_price) ?> ₽</p>
                                                        <?php endif; ?>
                                                        <?php if (!$tariff_price): ?>
                                                            <p><strong>Стоимость:</strong>
                                                                <?= htmlspecialchars_decode($proPhotoRequest['price']) ?></p>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if ($proPhotoRequest['emp_quantity']): ?>
                                                        <p><strong>Съемка сотрудников (кол-во человек):</strong>
                                                            <?= strip_tags(html_entity_decode($proPhotoRequest['emp_quantity'] ?? '')) ?>
                                                        </p>
                                                        <p><strong>Стоимость за сотрудников:</strong>
                                                            <?= strip_tags(html_entity_decode($tariffEmpPrice['price'] ?? '')) ?></p>
                                                    <?php endif; ?>
                                                    <p><strong>Контактный номер:</strong>
                                                        <?= htmlspecialchars_decode($proPhotoRequest['specialist_phone']) ?></p>
                                                    <button type="button" class="btn btn-primary mark-pro-photo-done"
                                                        data-request-id="<?= $proPhotoRequest['id'] ?>">
                                                        Проведена
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>

                        <?php if (!empty($visibleArchiveProPhoto)): ?>
                            <div class="active-pro-photo-requests mt-4">
                                <h4>Архивные размещения <a href="#" id="toggle-all-pro-photo"
                                        style="font-size: 14px; color: #000;" class="ms-2">(развернуть все)</a></h4>
                                <?php foreach ($visibleArchiveProPhoto as $req): ?>
                                    <div class="card my-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span>
                                                Архивный запрос:
                                                <?php
                                                if ($req['pro_photo_id'] == 4):
                                                    echo '<p>Съемка сотрудников</p>';
                                                else:
                                                    echo htmlspecialchars_decode($req['title']);
                                                endif;
                                                ?>
                                            </span>

                                            <button type="button" class="btn btn-dark bg-dark toggle-pro-photo-details"
                                                data-request-id="<?= $req['id'] ?>">
                                                Подробнее
                                            </button>
                                        </div>
                                        <div class="card-body" id="pro-photo-request-details-<?= $req['id'] ?>"
                                            style="display: none;">
                                            <div class="d-flex justify-content-between mb-3">
                                                <div>
                                                    <strong>Дата запроса:
                                                    </strong><?= date('d.m.Y', strtotime($req['connected_at'])) ?>
                                                </div>
                                                <div class="mb-2 text-start fw-bold text-success">
                                                    Статус: Проведена
                                                </div>
                                            </div>
                                            <?php if ($req['pro_photo_id'] != 4): ?>
                                                <p><strong>Стоимость:</strong> <?= htmlspecialchars_decode($req['price']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($req['emp_quantity']): ?>
                                                <p><strong>Съемка сотрудников (кол-во человек):</strong>
                                                    <?= strip_tags(html_entity_decode($req['emp_quantity'] ?? '')) ?>
                                                </p>
                                                <p><strong>Стоимость за сотрудников:</strong>
                                                    <?= strip_tags(html_entity_decode($tariffEmpPrice['price'] ?? '')) ?></p>
                                            <?php endif; ?>
                                            <p><strong>Контактный номер:</strong>
                                                <?= htmlspecialchars_decode($req['specialist_phone']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if ($hasMoreArchiveProPhoto): ?>
                                    <p class="text-center mt-4">
                                        <button id="loadFullProPhotoRequests" class="btn btn-primary">Показать все</button>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                // Получаем данные
                $promo_list = get_all_connected_promo_placements($specialist_id);
                $all_promotions = get_promotions();
                $journals = get_journals();
                $connected_journals = get_all_connected_journals($specialist_id);

                usort($connected_journals, function ($a, $b) {
                    $aValue = !empty($a['start_date']) ? strtotime($a['start_date']) : 0;
                    $bValue = !empty($b['start_date']) ? strtotime($b['start_date']) : 0;
                    return $bValue <=> $aValue;  // по возрастанию
                });

                $banner_list = get_all_connected_banners($specialist_id);

                usort($banner_list, function ($a, $b) {
                    $aValue = !empty($a['start_date']) ? strtotime($a['start_date']) : 0;
                    $bValue = !empty($b['start_date']) ? strtotime($b['start_date']) : 0;
                    return $bValue <=> $aValue;  // сортировка по возрастанию даты начала
                });

                $all_banners = [];

                $connected_tariffs = get_connected_tariffs($specialist_id);
                $connected_options = get_connected_options($specialist_id);

                // Индексируем промо-плейсменты и журналы по их ID
                $promo_map = [];
                foreach ($all_promotions as $p) {
                    $promo_map[$p['id']] = $p;
                }

                foreach ($banner_list as $item) {
                    // соберём все banner_id, чтобы одной пачкой получить записи
                    $banner_ids[] = $item['banner_id'];
                }
                if (!empty($banner_ids)) {
                    // Предположим, у тебя есть функция получения всех по массиву,
                    // или можно прямо в цикле дергать get_banner()
                    foreach (array_unique($banner_ids) as $id) {
                        $all_banners[$id] = get_banner($id);
                    }
                }

                $journal_map = [];
                foreach ($journals as $j) {
                    $journal_map[$j['id']] = $j;
                }

                $orderIds = [];
                foreach ($connected_tariffs as $item) {
                    $orderIds[] = $item['orderId'] ?? null;
                }

                foreach ($connected_options as $item) {
                    $orderIds[] = $item['orderId'] ?? null;
                }

                foreach ($promo_list as $item) {
                    $orderIds[] = $item['orderId'] ?? null;
                }

                $orderIds = array_filter(array_unique($orderIds));

                $payments = get_all_payments($specialist_id);

                usort($payments, function ($a, $b) {
                    $aValue = !empty($a['paymentDate']) ? strtotime($a['paymentDate']) : 0;
                    $bValue = !empty($b['paymentDate']) ? strtotime($b['paymentDate']) : 0;
                    return $bValue <=> $aValue;  // по возрастанию
                });

                ?>

                <div class="tab-pane fade" id="content-payment" role="tabpanel" aria-labelledby="tab-reviews">
                    <div class="pro-photo-section mb-5">
                        <h4 class="mb-4">Оплата</h4>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered align-middle w-100" style="table-layout:auto;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="col-6">Услуга</th>
                                        <th class="col-3">Дата оплаты</th>
                                        <th class="col-3">Оплаченная сумма</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    foreach ($payments as $pay):
                                    ?>
                                        <tr>
                                            <td class="d-none"><?= $sortValue ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($pay['serviceName']) ?></strong><br>
                                                <?php if (!empty($pay['date_start'])): ?>
                                                    <span class="text-muted small">
                                                        с <?= date('d-m-Y', strtotime($pay['date_start'])) ?>
                                                        <?php if (!empty($pay['date_end'])): ?>
                                                            по <?= date('d-m-Y', strtotime($pay['date_end'])) ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <?php if ($pay['sort']) { ?>
                                                        <br><span class="text-muted small"> <?= $pay['sort'] == 1 ? '1 место' : '2-8 место' ?></span>
                                                    <?php } ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= !empty($pay['paymentDate'])
                                                    ? date('d-m-Y H:i:s', strtotime($pay['paymentDate']))
                                                    : '' ?>
                                            </td>
                                            <td>
                                                <?= number_format($pay['amount'], 2, ',', ' ') ?> р
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php
                                    foreach ($banner_list as $item):
                                        $banner = $all_banners[$item['banner_id']] ?? null;
                                        if (!$banner)
                                            continue;

                                        // Даты в формате YYYY-MM-DD
                                        $start = date('d-m-Y', strtotime($item['start_date']));
                                        $expires = date('d-m-Y', strtotime($item['end_date']));
                                    ?>
                                        <tr>
                                            <td>
                                                <strong>Баннер</strong><br>
                                                <?= htmlspecialchars($banner['name']) ?><br>
                                                <span class="text-muted small">с <?= $start ?> по <?= $expires ?></span>
                                            </td>
                                            <td><?= date('d-m-Y H:i:s', strtotime($item['connected_at'])) ?></td>
                                            <td><?= number_format($banner['price'], 0, ',', ' ') ?> р</td>
                                        </tr>
                                    <?php endforeach; ?>


                                    <!-- 2) Выводим все активные подписки на журналы -->
                                    <?php
                                    foreach ($connected_journals as $item):
                                        $journal = $journal_map[$item['journal_id']] ?? null;
                                        if (!$journal)
                                            continue;

                                        $start = date('d-m-Y', strtotime($item['start_date']));
                                        $expires = date('d-m-Y', strtotime($item['end_date']));
                                    ?>
                                        <tr>
                                            <td>
                                                <strong>Журнал</strong><br>
                                                <?= htmlspecialchars($journal['name']) ?><br>
                                                <span class="text-muted small">с <?= $start ?> по <?= $expires ?></span>
                                            </td>
                                            <td><?= date('d-m-Y H:i:s', strtotime($item['connected_at'])) ?></td>
                                            <td><?= number_format($journal['price'], 0, ',', ' ') ?> р</td>
                                        </tr>
                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary button_close" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-id="<?= $specialist_id ?>"
                    id="specialistAdd"><?= isset($specialist_id) ? 'Сохранить' : 'Добавить специалиста' ?></button>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="/assets/flatpickr/dist/flatpickr.min.css">
<script src="/assets/flatpickr/dist/flatpickr.min.js"></script>
<script src="/assets/flatpickr/dist/l10n/ru.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script>
    $(document).ready(function() {
        const phoneInputs = document.querySelectorAll('.phone-input-reg');

        const maskOptions = {
            mask: '+{7} (000) 000-00-00'
        };

        phoneInputs.forEach(function(input) {
            IMask(input, maskOptions);
        });
    });
    // При открытии модального окна
    $('#modalAddSpecialist').on('show.bs.modal', function() {
        const specialistId = '<?= isset($specialist_id) ? $specialist_id : '' ?>'; // Получаем specialist_id из PHP
        const url = new URL(window.location.href); // Создаём новый URL на основе текущего

        // Добавляем параметр id в URL
        url.searchParams.set('id', specialistId);

        // Обновляем URL без перезагрузки страницы
        window.history.pushState({}, '', url);
    });

    // При переключении вкладки
    $('#specialistTab button').on('click', function() {
        const tabId = $(this).attr('id').replace('tab-', '');
        const url = new URL(window.location.href);

        url.searchParams.set('tab', tabId);

        window.history.pushState({}, '', url);
    });

    $(document).on('click', '#loadFullArchiveBanners', function() {
        $.ajax({
            type: 'POST',
            url: '',
            data: {
                action: 'load_full_archive_banners',
                specialist_id: <?= $specialist_id ?>
            },
            success: function(html) {
                $('.archived-banner-list').html(html);
            },
            error: function() {
                alert('Ошибка загрузки архивных данных');
            }
        });
    });

    $(document).on('click', '#loadFullProPhotoRequests', function() {
        $.ajax({
            type: 'POST',
            url: '',
            data: {
                action: 'load_full_pro_photo_requests',
                specialist_id: <?= $specialist_id ?>
            },
            success: function(html) {
                $('.active-pro-photo-requests').html(html);
            },
            error: function() {
                alert('Ошибка загрузки заявок');
            }
        });
    });

    $(document).ready(function() {
        $("input[name=city]").autocomplete({
            source: function(request, response) {
                if (request.term.length < 2) return; // фильтрация по длине запроса
                $.getJSON("/actions/location/get_cities.php", {
                    term: request.term
                }, function(data) {
                    // Преобразуем данные в нужный формат, если нужно
                    var transformedData = data.map(function(item) {
                        return {
                            id: item.id,
                            label: item.name, // Используем название города как label
                            value: item.name // Используем название города как value
                        };
                    });

                    response(transformedData); // Отправляем данные в autocomplete
                });
            },
            select: function(event, ui) {
                // Когда пользователь выбирает город из списка
                $('input[name=city_id]').val(ui.item.id); // Обновляем скрытое поле с id города
                $('input[name=city]').val(ui.item.value); // Обновляем поле с названием города
            },
            minLength: 2, // Минимальная длина строки для запуска поиска
            delay: 300, // Задержка перед отправкой запроса
            autoFocus: true // Автодополнение при выборе города
        });
    });

    $(document).ready(function() {
        $("input[name=metro]").autocomplete({
            source: function(request, response) {
                $.getJSON("/actions/location/get_metro.php", {
                    term: request.term,
                    city_id: $("select[name=city]").val() // Передаем city_id для фильтрации метро по городу
                }, function(data) {
                    response(data); // Отправляем данные в autocomplete
                });
            },
            search: function() {
                let term = this.value;
                if (term.length < 2) return false; // Запуск поиска только после ввода хотя бы двух символов
            },
            select: function(event, ui) {
                // Когда пользователь выбирает метро из списка
                $('input[name=metro_id]').val(ui.item.id); // Обновляем скрытое поле с id метро
                $('input[name=metro]').val(ui.item.value); // Обновляем поле с названием метро
            },
            minLength: 2, // Минимальная длина строки для запуска поиска
            delay: 300, // Задержка перед отправкой запроса
            autoFocus: true // Автодополнение при выборе метро
        });
    });

    document.querySelectorAll('.duration-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                const groupName = this.name;

                // Снимаем другие чекбоксы в той же группе
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(cb => {
                    if (cb !== this) cb.checked = false;
                });

                // Активируем кнопку "Подключить"
                const id = groupName.replace('duration_', '');
                const connectButton = document.querySelector(`.connect[data-id="${id}"]`);
                if (connectButton) {
                    connectButton.removeAttribute('disabled');
                }
            }
        });
    });
</script>