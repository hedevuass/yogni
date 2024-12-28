import {
    Helper
} from "./helper.js";

let banners = {
    selectors: {
        'pageSelector': '#bannersPage',
        'modalEditSelector': '#modalEditbanner',
        'modalEditJournalBannerSelector': '#modalEditJournalbanner',
        'slidesContainer': '#modal-body',
        'photoSelector': '#input-img'
    },
    start: function () {
        $('#banners').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $('#journal_banners').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#bannerEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '#bannerJournalEdit', this.editJournalBanner)
        $(this.selectors.pageSelector).on('click', '#addSlide', this.addSlide);
        $(this.selectors.pageSelector).on('click', '.upload-avatar', banners.file)
        $(this.selectors.pageSelector).on('change', this.selectors.photoSelector, e => Helper.uploadImage('.avatar', e))
    },
    file: function () {
        $(banners.selectors.photoSelector).click()
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html();
    
        let formData = new FormData();
    
        $(`${banners.selectors.modalEditSelector} select, ${banners.selectors.modalEditSelector} input, ${banners.selectors.modalEditSelector} textarea`).each(function() {
            if ($(this).attr('type') === 'file') {
                let files = $(this)[0].files;
                const slideId = $(this).data('id'); // Получаем ID слайда из атрибута data-id
        
                if (files.length > 0) {
                    // Добавляем новое изображение с привязкой к ID слайда
                    formData.append(`image[${slideId}]`, files[0]);
                }
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        });
        
        $.ajax({
            type: "POST",
            url: "actions/banner/edit_banner.php",
            dataType: 'json',
            processData: false,
            contentType: false,
            data: formData,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(banners.selectors.modalEditSelector);
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent);
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value));
                    });
                }
                if (json.fatal) Helper.fatalError(json.fatal);
                if (json.success) location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Ошибка AJAX-запроса:", textStatus, errorThrown);
            }
        });
    },   
    editJournalBanner: function () {
        let button = $(this),
            buttonContent = button.html();
    
        let formData = new FormData();
    
        $(`${banners.selectors.modalEditJournalBannerSelector} select, ${banners.selectors.modalEditJournalBannerSelector} input, ${banners.selectors.modalEditJournalBannerSelector} textarea`).each(function() {
            if ($(this).attr('type') === 'file') {
                if ($(this)[0].files[0]) {
                    formData.append('photo', $(this)[0].files[0]);
                }
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        });
    
        $.ajax({
            type: "POST",
            url: "actions/banner/edit_journal_banner.php",
            dataType: 'json',
            processData: false,
            contentType: false,
            data: formData,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(banners.selectors.modalEditJournalBannerSelector);
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent);
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value));
                    });
                }
                if (json.fatal) Helper.fatalError(json.fatal);
                if (json.success) location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Ошибка AJAX-запроса:", textStatus, errorThrown);
            }
        });
    },     
    modalEdit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            type = button.attr('data-type') ?? 0,
            buttonContent = button.html()
        console.log(type);

        $.ajax({
            type: "POST",
            url: "actions/banner/get_modal.php",
            dataType: 'json',
            data: {id: id, type: type},
            beforeSend: function () {
                Helper.loadingButton(button);
                if (type === 'banner') {
                    $(banners.selectors.modalEditSelector).remove();
                } else if (type === 'journal-banner') {
                    $(banners.selectors.modalEditJournalBannerSelector).remove();
                }
            },
            success: function (json) {
                $(banners.selectors.pageSelector).append(json.code);
                
                if (type === 'banner') {
                    $(banners.selectors.modalEditSelector).modal('show');
                } else if (type === 'journal-banner') {
                    $(banners.selectors.modalEditJournalBannerSelector).modal('show');
                }
                
                Helper.loadingButton(button, buttonContent);
            }
        })
    },
    addSlide: function () {
        const slidesContainer = $(banners.selectors.slidesContainer);
        const slideCount = slidesContainer.children('.mb-3').length; // Подсчитываем текущие слайды
        
        // Извлекаем banner_id из кнопки
        const bannerId = $('#addSlide').data('banner-id');
    
        // Создаем HTML для нового слайда
        const newSlideHtml = `
            <div class="mb-3">
                <h5 class="mb-3">Слайд №${slideCount + 1}</h5>
    
                <input type="hidden" name="id[]" value="${slideCount + 1}"> <!-- ID для нового слайда -->
                <input type="hidden" name="banner_id[]" value="${bannerId}"> <!-- ID баннера из кнопки -->
    
                <label for="input-image-${slideCount}" class="form-label">Изображение слайда</label>
                <input type="file" name="image[]" class="form-control" id="input-image-${slideCount}" accept="image/*">
    
                <label for="input-title-${slideCount}" class="form-label">Заголовок слайда</label>
                <input type="text" name="title[]" class="form-control" id="input-title-${slideCount}" required>
    
                <label for="input-description-line1-${slideCount}" class="form-label">Описание строка 1</label>
                <input type="text" name="description[${slideCount}][line1]" class="form-control" id="input-description-line1-${slideCount}">
    
                <label for="input-description-line2-${slideCount}" class="form-label">Описание строка 2</label>
                <input type="text" name="description[${slideCount}][line2]" class="form-control" id="input-description-line2-${slideCount}">
    
                <label for="input-description-line3-${slideCount}" class="form-label">Описание строка 3</label>
                <input type="text" name="description[${slideCount}][line3]" class="form-control" id="input-description-line3-${slideCount}">
    
                <label for="input-specialist-name-${slideCount}" class="form-label">Имя специалиста</label>
                <input type="text" name="specialist_name[]" class="form-control" id="input-specialist-name-${slideCount}">
    
                <label for="input-category-${slideCount}" class="form-label">Категория специалиста</label>
                <input type="text" name="classification[]" class="form-control" id="input-category-${slideCount}">
    
                <label for="input-link-${slideCount}" class="form-label">Ссылка на баннер</label>
                <input type="url" name="link[]" class="form-control" id="input-link-${slideCount}">
    
                <label for="input-button-text-${slideCount}" class="form-label">Текст на кнопке</label>
                <input type="text" name="button_text[]" class="form-control" id="input-button-text-${slideCount}">
    
                <label for="input-button-link-${slideCount}" class="form-label">Ссылка на кнопку</label>
                <input type="url" name="button_link[]" class="form-control" id="input-button-link-${slideCount}">
            </div>
        `;
    
        // Добавляем новый HTML слайд в контейнер
        slidesContainer.append(newSlideHtml);
    }
    
};

export {
    banners
}