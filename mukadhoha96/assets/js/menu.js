import {
    Helper
} from "./helper.js";

let Menu = {
    isIconRemoved: false,

    selectors: {
        'pageSelector': '#menuPage',
        'modalAddSelector': '#modalAddMenu',
        'photoSelector': '#input-img',
        'iconSelector': '#input-icon',  
        'contentSelector': '#input-content',  
        'alternativeContentSelector': '.alternative-description-input',  
        originalSelector: '#original-page-selector',
        childSelector: '#child-items-selector',
        alternativesContainer: '#alternatives-container'
    },
    start: function () {
        $('#menu').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        /* $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit) */
        $(this.selectors.pageSelector).on('click', '.modalAdd', this.modalAdd)
        $(this.selectors.pageSelector).on('click', '#menuEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '#menuEditParent', this.editParent)
        $(this.selectors.pageSelector).on('click', '#menuAdd', this.add)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', '.upload-avatar', Menu.file)
        $(this.selectors.pageSelector).on('click', '.upload-icon', Menu.icon)
        $(this.selectors.pageSelector).on('click', '.remove-icon', Menu.removeIcon);
        $(this.selectors.pageSelector).on('change', this.selectors.photoSelector, e => Helper.uploadImage('.avatar', e))
        $(this.selectors.pageSelector).on('change', this.selectors.iconSelector, e => Helper.uploadImage('.icon', e))

        $(this.selectors.pageSelector).on('click', '.edit-alternative', this.editAlternative);
        $(this.selectors.pageSelector).on('click', '.add-alternative', this.addAlternative);
        $(this.selectors.pageSelector).on('click', '.save-alternative', this.saveAlternative);
        $(this.selectors.pageSelector).on('click', '.close-alternative', this.closeAlternative);
        $(this.selectors.pageSelector).on('click', '.delete-alternative', this.deleteAlternative);
        $(this.selectors.pageSelector).on('change', this.selectors.originalSelector, function() {
            let selectedOption = $(this).find('option:selected');
            let parentId = selectedOption.data('p-id');

            console.log("original_id изменен, p_id родителя:", parentId);

            // Если выбран родитель, устанавливаем p_id для дочерней страницы
            if (parentId && parentId != 0) {
                $(Menu.selectors.childSelector).val(parentId).trigger('change');
            } else {
                $(Menu.selectors.childSelector).val(0).trigger('change');
            }
        });
    },
    editAlternative: function () {
        let alternativeRow = $(this).closest('.alternative-row');
        let editForm = alternativeRow.next('.alternative-edit-form');
        editForm.toggleClass('d-none');
        Menu.initModalListeners();
        Menu.initTinyMCE();
    },

    // При клике на "Закрыть" скрываем форму редактирования
    closeAlternative: function () {
        $(this).closest('.alternative-edit-form').addClass('d-none');
    },

    saveAlternative: function () {
        let form = $(this).closest('.alternative-edit-form');
        let id = $(this).data('id');
        let type = $(this).data('type');
        let title = form.find('.alternative-title-input').val();
        let slug = form.find('.alternative-slug-input').val();
        let metaTitle = form.find('.alternative-meta-title-input').val();
        let metaDescription = form.find('.alternative-meta-description-input').val();
        let metaKeywords = form.find('.alternative-meta-keywords-input').val();
        let originalId = form.find('.alternative-original-id').val();
        let classification = form.find('.alternative-classification').val();
        let p_id = form.find('.alternative-p_id').val();
    
        // Создаём FormData
        let data = new FormData();
        data.append('id', id);
        data.append('type', type);
        data.append('title', title);
        data.append('slug', slug);
        data.append('meta_title', metaTitle);
        data.append('meta_description', metaDescription);
        data.append('meta_keywords', metaKeywords);
        data.append('original_id', originalId);
        data.append('classification_id', classification);
        data.append('p_id', p_id);
    
        const textarea = form.find('.alternative-description-input')[0];
        if (textarea) {
            const editor = tinymce.get(textarea.id);
            if (editor) {
                data.append('content', editor.getContent());
            } else {
                console.log('Editor not initialized');
            }
        } else {
            console.log('Content element not found');
        }
    
        // Отправляем данные через AJAX
        $.ajax({
            type: "POST",
            url: "actions/menu/edit_alternative.php",
            data: data,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    form.prev('.alternative-row').find('span.fw-bold').text(title);
                    form.addClass('d-none');
                } else {
                    console.log("Ошибка при сохранении:", response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log("AJAX ошибка:", textStatus, errorThrown);
            }
        });
    },
    
    // Добавление новой альтернативной страницы
    addAlternative: function () {
        let menuId = $('#menu-id').val();
        let classification = $('#menu-classification').val();
        let pId = $('#menu-p-id').val();
        let type = $('#type').val();
        
        // Генерируем уникальный id для альтернативного текстового редактора, например, используя timestamp
        let uniqueId = 'alternative-description-' + Date.now();
    
        let newAlternativeHtml = `
            <div class="alternative-row bg-light border rounded p-3 mb-2 d-flex justify-content-between align-items-center w-100" data-id="new">
                <span class="fw-bold">Новая страница</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary edit-alternative" data-id="new">Редактировать</button>
                    <button type="button" class="btn btn-sm btn-danger delete-alternative" data-id="new">Удалить</button>
                </div>
            </div>
            <div class="alternative-edit-form mt-2 p-2 border rounded bg-white w-100" data-parent-id="new">
                <input type="hidden" class="alternative-original-id" value="${menuId}"> 
                <input type="hidden" class="alternative-classification" value="${classification}">
                <input type="hidden" class="alternative-p_id" value="${pId}">
                    <div class="mb-2">
                        <label>Заголовок</label> 
                        <input type="text" class="form-control alternative-title-input" value="">
                    </div>
                    <div class="mb-2">
                        <label>Описание</label>
                        <textarea id="${uniqueId}" class="form-control alternative-description-input"></textarea>
                    </div>
                    <div class="mb-2">
                        <label>Ссылка</label>
                        <input type="text" class="form-control alternative-slug-input" value="">
                    </div>
                    <div class="mb-2">
                        <label>Title страницы</label>
                        <input type="text" class="form-control alternative-meta-title-input" value="">
                    </div>
                    <div class="mb-2">
                        <label>Description страницы</label>
                        <input type="text" class="form-control alternative-meta-description-input" value="" placeholder="">
                    </div>
                    <div class="mb-2">
                        <label>Ключевые слова</label>
                        <input type="text" class="form-control alternative-meta-keywords-input" value="" placeholder="">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-secondary btn-sm close-alternative">Закрыть</button>
                        <button class="btn btn-success btn-sm ms-2 save-alternative" data-type="${type}" data-id="new">Сохранить</button>
                    </div>
                </div>
            `;
        $(Menu.selectors.alternativesContainer).append(newAlternativeHtml);
        Menu.initModalListeners();
        Menu.initTinyMCE();
    },
    
    deleteAlternative: function () {
        let alternativeRow = $(this).closest('.alternative-row');
        let button = $(this);
        let form = button.closest('.alternative-edit-form');
        let id = button.data('id');
        let type = form.length ? form.find('.save-alternative').data('type') : button.data('type'); // если кнопка вне формы
        if (!type) {
            console.error('Не указан тип элемента для удаления');
            return;
        }
    
        if (!confirm('Вы уверены, что хотите удалить эту альтернативу?')) {
            return;
        }
    
        $.ajax({
            type: "POST",
            url: "actions/menu/delete_alternative.php",
            data: {
                id: id,
                type: type
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Удаляем блок из DOM
                    button.closest('.alternative-container').remove();
                    console.log(`Удаление альтернативы с ID: ${alternativeRow.data('id')}`);
                    // Удаляем связанную форму, если она открыта
                    alternativeRow.next('.alternative-edit-form').remove();
                    alternativeRow.remove();
                } else {
                    console.log("Ошибка при удалении:", response.error || response.fatal);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log("AJAX ошибка:", textStatus, errorThrown);
            }
        });
    },    
    file: function () {
        $(Menu.selectors.photoSelector).click()
    },
    icon: function () {
        $(Menu.selectors.iconSelector).click()
    },
    removeIcon: function () {
        Menu.isIconRemoved = true
        $(Menu.selectors.iconSelector).val('');
        $('.icon').attr('src', '');
    },
    initTinyMCE: function () {
        if (typeof tinymce !== 'undefined') {
            
            tinymce.remove(`${Menu.selectors.contentSelector}, ${Menu.selectors.contentSelector}`);

            tinymce.init({
                selector: `${Menu.selectors.contentSelector}, ${Menu.selectors.contentSelector}, ${Menu.selectors.alternativeContentSelector}`,
                plugins: 'lists autolink',
                toolbar: 'undo redo | formatselect | fontselect fontsizeselect | bold italic | alignleft aligncenter alignright | bullist numlist | link',
                branding: false,
                content_css: 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap',
                content_style: `
                    body, p, h1, h2, h3, div, span {
                        font-family: Roboto, sans-serif !important;
                        font-size: 14px !important;
                    }
                `,
            
                font_family_formats: "Roboto=Roboto, sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier;",
                font_size_formats: '8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 24pt 36pt 48pt',
                block_formats: 'Header 1=h1; Header 2=h2; Header 3=h3; Paragraph=p',
                style_formats: [
                    { title: 'Header 1', block: 'h1' },
                    { title: 'Header 2', block: 'h2' },
                    { title: 'Header 3', block: 'h3' },
                    { title: 'Paragraph', block: 'p' }
                ],
                force_br_newlines: true,
                force_p_newlines: false,
                forced_root_block: 'div',
                link_context_toolbar: true,
                link_list: false,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                setup: function (editor) {
                    let modalEl = document.querySelector(Menu.selectors.modalEditSelector);
                    let modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                
                    function disableFocusTrap() {
                        if (modalInstance && modalInstance._focustrap) {
                            modalInstance._focustrap.deactivate();
                        }
                    }
                
                    function enableFocusTrap() {
                        if (modalInstance && modalInstance._focustrap) {
                            modalInstance._focustrap.activate();
                        }
                    }
                
                    editor.on('OpenWindow', function () {
                        disableFocusTrap();
                        setTimeout(() => {
                            const input = document.querySelector('.tox-dialog input');
                            if (input) input.focus();
                        }, 50);
                    });
                
                    editor.on('CloseWindow', function () {
                        enableFocusTrap();
                    });
                
                    editor.on('init', function () {
                        console.log('Editor initialized');
                    });
                },
            });
            
        } else {
            console.error('TinyMCE не загружен.');
        }
    },
    initModalListeners: function () {
        // Основные поля (основная форма)
        const mainTitleInput = document.getElementById('input-title');
        const mainSlugInput = document.getElementById('input-alias');
        const mainMetaTitleInput = document.getElementById('input-meta-title');
        const mainMetaDescriptionInput = document.getElementById('input-meta-description');
    
        // Если основной формы нет, выходим
        if (!mainTitleInput) {
            return;
        }
    
        // Функция транслитерации из русского в латиницу
        function transliterate(text) {
            const ru = {
                'А':'A','Б':'B','В':'V','Г':'G','Д':'D','Е':'E','Ё':'YO','Ж':'ZH','З':'Z','И':'I','Й':'J','К':'K','Л':'L','М':'M','Н':'N','О':'O','П':'P','Р':'R','С':'S','Т':'T','У':'U','Ф':'F','Х':'H','Ц':'TS','Ч':'CH','Ш':'SH','Щ':'SCH','Ъ':'','Ы':'Y','Ь':'','Э':'E','Ю':'YU','Я':'YA',
                'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'yo','ж':'zh','з':'z','и':'i','й':'j','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'ts','ч':'ch','ш':'sh','щ':'sch','ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya'
            };
            return text.split('').map(char => ru[char] !== undefined ? ru[char] : char).join('');
        }
    
        // Функция для генерации slug на английском
        function generateSlug(text) {
            text = transliterate(text);
            return text
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')  // удаляем все не буквенно-цифровые символы
                .replace(/\s+/g, '-')          // заменяем пробелы на дефисы
                .replace(/-+/g, '-');          // убираем повторяющиеся дефисы
        }
    
        // Удаление HTML-тегов
        function stripHtmlTags(html) {
            return html.replace(/<\/?[^>]+(>|$)/g, "").trim();
        }
    
        // Декодирование HTML-сущностей
        function decodeHTMLEntities(text) {
            let doc = new DOMParser().parseFromString(text, "text/html");
            return doc.documentElement.textContent;
        }
    
        // Функция для генерации meta описания из HTML-контента
        function generateMetaDescription(htmlContent) {
            let decodedContent = decodeHTMLEntities(htmlContent);
            let textContent = stripHtmlTags(decodedContent);
            return textContent.length > 150 ? textContent.substring(0, 150) + '...' : textContent;
        }
    
        // ---------------------------
        // Обработчики для основной формы
        // ---------------------------
        mainTitleInput.addEventListener('input', function () {
            const title = mainTitleInput.value;
            if (mainSlugInput) {
                mainSlugInput.value = generateSlug(title);
            }
            if (mainMetaTitleInput) {
                mainMetaTitleInput.value = title;
            }
        });

        const altTitleInputs = document.querySelectorAll('.alternative-title-input');
        altTitleInputs.forEach(function(altTitleInput) {
            altTitleInput.addEventListener('input', function () {
                const title = altTitleInput.value;
                const container = altTitleInput.closest('.alternative-edit-form');
                if (!container) {
                    return;
                }
                const altSlugInput = container.querySelector('.alternative-slug-input');
                const altMetaTitleInput = container.querySelector('.alternative-meta-title-input');
                if (altSlugInput) {
                    altSlugInput.value = generateSlug(title);
                }
                if (altMetaTitleInput) {
                    altMetaTitleInput.value = title;
                }
            });
        });
    
        if (typeof tinymce !== 'undefined') {
            const mainEditorId = Menu.selectors.contentSelector.replace('#', '');
            const mainEditor = tinymce.get(mainEditorId);
            if (mainEditor) {
                mainEditor.on('change', function () {
                    const contentHtml = mainEditor.getContent();
                    const metaDesc = generateMetaDescription(contentHtml);
                    if (mainMetaDescriptionInput) {
                        mainMetaDescriptionInput.value = metaDesc;
                    }
                });
            }
            
            const altTextareas = document.querySelectorAll(Menu.selectors.alternativeContentSelector);
            altTextareas.forEach(function (textarea) {
                const altEditor = tinymce.get(textarea.id);
                if (altEditor) {
                    altEditor.on('change', function () {
                        const altContentHtml = altEditor.getContent();
                        const altMetaDesc = generateMetaDescription(altContentHtml);
                        const form = textarea.closest('.alternative-edit-form');
                        if (form) {
                            const input = form.querySelector('.alternative-meta-description-input');
                            if (input) {
                                input.value = altMetaDesc;
                            }
                        }
                    });
                }
            });
        }
        if (typeof tinymce !== 'undefined') {
            tinymce.EditorManager.on('AddEditor', function(e) {
                // Если редактор относится к альтернативным полям (например, его id начинается с "alternative-description-")
                if (e.editor && e.editor.id.indexOf('alternative-description-') === 0) {
                    e.editor.on('change', function () {
                        const altContentHtml = e.editor.getContent();
                        const altMetaDesc = generateMetaDescription(altContentHtml);
                        const container = e.editor.getElement().closest('.alternative-edit-form');
                        if (container) {
                            const input = container.querySelector('.alternative-meta-description-input');
                            if (input) {
                                input.value = altMetaDesc;
                            }
                        }
                    });
                }
            });
        }        
    },
    
    modalAdd: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            category = button.attr('data-category'), 
            slug = button.attr('data-slug'), 
            page_id = button.attr('data-page-id'), 
            buttonContent = button.html();
    
        $.ajax({
            type: "POST",
            url: "actions/menu/get_modal.php",
            dataType: 'json',
            data: { id: id, category: category, slug: slug, page_id: page_id }, 
            beforeSend: function () {
                Helper.loadingButton(button);
                $(Menu.selectors.modalAddSelector).remove();
            },
            success: function (json) {
                $(Menu.selectors.pageSelector).append(json.code);
                $(Menu.selectors.modalAddSelector).modal('show');
                Helper.loadingButton(button, buttonContent);
                Menu.initTinyMCE();
                Menu.initModalListeners();
            }
        });
    },    
    edit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html();
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();
        $(`${Menu.selectors.modalAddSelector} input, ${Menu.selectors.modalAddSelector} select, ${Menu.selectors.modalAddSelector} textarea`).each(function() {
            if ($(this).attr('type') === 'file') {
                if ($(this)[0].files[0]) {
                    let fieldName = $(this).attr('name'); // например, "photo" или "icon"
                    formData.append(fieldName, $(this)[0].files[0]);
                }
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        });
        formData.append('content', tinymce.get(Menu.selectors.contentSelector.replace('#', '')).getContent());

        let originalPageId = $('#original-page-selector').val();
        if (originalPageId && originalPageId !== "0") {
            formData.append('original_id', originalPageId);
        }

        formData.append('meta_title', $('#input-meta-title').val());
        formData.append('meta_description', $('#input-meta-description').val());
        formData.append('meta_keywords', $('#input-meta-keywords').val());

        if (Menu.isIconRemoved) {
            formData.append('remove_icon', '1');
        }

        $.ajax({
            type: "POST",
            url: "actions/menu/edit_menu.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Menu.selectors.pageSelector);
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent)
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value))
                    });
                }
                if (json.fatal) Helper.fatalError(json.fatal)
                if (json.success) location.reload()
            }
        });
    }, 
    editParent: function () {
        let button = $(this),
            slug = button.attr('data-slug') ?? 0,
            buttonContent = button.html();
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();

        $(`${Menu.selectors.modalAddSelector} input, ${Menu.selectors.modalAddSelector} select, ${Menu.selectors.modalAddSelector} textarea`).each(function() {
            if ($(this).attr('type') === 'file') {
                if ($(this)[0].files[0]) {
                    let fieldName = $(this).attr('name'); // например, "photo" или "icon"
                    formData.append(fieldName, $(this)[0].files[0]);
                }
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        });

        formData.append('description', tinymce.get(Menu.selectors.contentSelector.replace('#', '')).getContent());
        
        formData.append('slug', slug);

        $.ajax({
            type: "POST",
            url: "actions/menu/edit_menu_parent.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Menu.selectors.pageSelector);
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent)
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value))
                    });
                }
                if (json.fatal) Helper.fatalError(json.fatal)
                if (json.success) location.reload()
            }
        });
    }, 
    add: function() {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html(),
            formData = new FormData()

        if (id) formData.append('id', id);

        formData.append('name', $('#input-name').val())
        formData.append('lastname', $('#input-lastname').val())

        if ($(Menu.selectors.photoSelector)[0].files[0]) {
            formData.append('photo', $(Menu.selectors.photoSelector)[0].files[0])
        }

        formData.append('city_id', $('#input-city_id').val())
        formData.append('email', $('#input-email').val())
        formData.append('phone', $('#input-phone').val())
        formData.append('password', $('#input-password').val())
        formData.append('type', $('#input-type').val())
        formData.append('p_id', $('#input-parent').val())
        formData.append('classification_id', $('#input-classification').val())

        if ($('#input-company').css('display') !== 'none') {
            formData.append('company[name]', $('#input-company_name').val())
            formData.append('company[id]', $('#input-company_id').val())
        }

        if ($('#input-active:checked').length) formData.append('active', $('#input-active:checked').val())



        $.ajax({
            type: "POST",
            url: "actions/specialist/add_specialist.php",
            dataType: 'json',
            contentType: false,
            processData: false,
            data: formData,
            beforeSend: function () {
                Helper.loadingButton(button)
                Helper.removeValidation(Menu.selectors.modalAddSelector)
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent)
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value))
                    });
                }

                if (json.fatal) {
                    Helper.fatalError(json.fatal)
                }

                if (json.success) {
                    location.reload()
                }
            }
        })
    },
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить запись?')) {
            $.ajax({
                type: "POST",
                url: "actions/menu/delete.php",
                dataType: 'json',
                data: {id: id},
                beforeSend: function () {
                    Helper.loadingButton(button)
                },
                success: function (json) {
                    if (json.success) location.reload()
                }
            })
        }
    },
};

export {
    Menu
}