import {
    Helper
} from "./helper.js";

let FaqOptions = {
    selectors: {
        'pageSelector': '#faqPage',
        'modalEditSelector': '#modalEditFaqPages',
        'contentSelector': '#input-content',
    },
    start: function () {
        $('#faq').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
            'order': [[0, 'asc']],
        })
        $('#faqPromo').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
            'order': [[0, 'asc']],
        })
        $('#faqProPhoto').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
            'order': [[0, 'asc']],
        })
        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#faqPagesEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '.modalEditFaqPromo', this.modalEditPromo)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', '.modalEditFaqProPhoto', this.modalEditProPhoto)
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html(),
            type = button.attr('data-type') ?? 0;
            console.log(type);
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();

        formData.append('type', type);
        
        $(`${FaqOptions.selectors.modalEditSelector} input, ${FaqOptions.selectors.modalEditSelector} textarea`).each(function() {
        formData.append($(this).attr('name'), $(this).val());
        });

        formData.append('content', tinymce.get(FaqOptions.selectors.contentSelector.replace('#', '')).getContent());
    
        $.ajax({
            type: "POST",
            url: "actions/faq/edit_faq.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(FaqOptions.selectors.modalEditSelector);
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
    initModalListeners: function () {
        const titleInput = document.getElementById('input-name');
            const slugInput = document.getElementById('input-alias');
            const metaTitleInput = document.getElementById('input-meta-title');
            const metaDescriptionInput = document.getElementById('input-meta-description');
        
            if (!titleInput) {
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

            function stripHtmlTags(html) {
                return html.replace(/<\/?[^>]+(>|$)/g, "").trim();
            }
    
            function decodeHTMLEntities(text) {
                let doc = new DOMParser().parseFromString(text, "text/html");
                return doc.documentElement.textContent;
            }
        
            // Функция для генерации slug на английском
            function generateSlug(text) {
                // Транслитерируем, если есть русские символы
                text = transliterate(text);
                return text
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')  // удаляем все не буквенно-цифровые символы
                    .replace(/\s+/g, '-')          // заменяем пробелы на дефисы
                    .replace(/-+/g, '-');          // убираем повторяющиеся дефисы
            }
        
            function stripHtmlTags(html) {
                return html.replace(/<\/?[^>]+(>|$)/g, "").trim();
            }

            function generateMetaDescription(htmlContent) {
                let decodedContent = decodeHTMLEntities(htmlContent); // Декодируем сущности
                let textContent = stripHtmlTags(decodedContent); // Убираем HTML-теги
                return textContent.length > 150 ? textContent.substring(0, 150) + '...' : textContent;
            }
        
            // Обработчик изменения текста в поле "Название" для генерации URL и мета-заголовка
            titleInput.addEventListener('input', function () {
                const title = titleInput.value;
                slugInput.value = generateSlug(title);
                metaTitleInput.value = title;
            });

            if (typeof tinymce !== 'undefined' && tinymce.get(FaqOptions.selectors.contentSelector.replace('#', ''))) {
                tinymce.get(FaqOptions.selectors.contentSelector.replace('#', '')).on('change', function () {
                    const contentHtml = tinymce.get(FaqOptions.selectors.contentSelector.replace('#', '')).getContent();
                    metaDescriptionInput.value = generateMetaDescription(contentHtml);
                });
            }
    },
    initTinyMCE: function () {
        if (typeof tinymce !== 'undefined') {
    
            tinymce.remove(`${FaqOptions.selectors.contentSelector}`);
    
            tinymce.init({
                selector: `${FaqOptions.selectors.contentSelector}`,
                plugins: 'lists link code advlist',
                relative_urls: false,
                readonly: false,
                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
                branding: false,
                extended_valid_elements: 'div[class|style],span[class|style]',
                valid_children: '+body[style]',
                content_css: 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap',
                content_style: `
                    body, p, h1, h2, h3, div, span {
                        font-family: Roboto, sans-serif !important;
                        font-size: 14px !important;
                    }
                `,
                font_family_formats: "Roboto=Roboto, sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier;",
                font_size_formats: '8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 24pt 36pt 48pt',
                block_formats: 'Header 1=h1; Header 2=h2; Header 3=h3; Paragraph:p',
                style_formats: [
                    { title: 'Header 1', block: 'h1' },
                    { title: 'Header 2', block: 'h2' },
                    { title: 'Header 3', block: 'h3' },
                    { title: 'Paragraph', block: 'p' },
                    { title: 'Custom Div', block: 'div', classes: 'custom-class' }
                ],
                setup: function (editor) {
                    let modalEl = document.querySelector(FaqOptions.selectors.modalEditSelector);
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
                }
                
            });
    
        } else {
            console.error('TinyMCE не загружен.');
        }
    },
    
    modalEdit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html();
            console.log(id);
        $.ajax({
            type: "POST",
            url: "actions/faq/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(FaqOptions.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(FaqOptions.selectors.pageSelector).append(json.code)
                $(FaqOptions.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                FaqOptions.initTinyMCE();
                FaqOptions.initModalListeners();
            }
        })
    },  
    modalEditPromo: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()
            
            console.log(id);
        $.ajax({
            type: "POST",
            url: "actions/faq/get_modal_promo.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(FaqOptions.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(FaqOptions.selectors.pageSelector).append(json.code)
                $(FaqOptions.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                FaqOptions.initTinyMCE();
            }
        })
    },
    modalEditProPhoto: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()
            
            console.log(id);
        $.ajax({
            type: "POST",
            url: "actions/faq/get_modal_pro_photo.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(FaqOptions.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(FaqOptions.selectors.pageSelector).append(json.code)
                $(FaqOptions.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                FaqOptions.initTinyMCE();
            }
        })
    }, 
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this),
        type = button.attr('data-type') ?? 0;


        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/faq/delete.php",
                dataType: 'json',
                data: {id: id, type: type},
                beforeSend: function () {
                    Helper.loadingButton(button)
                },
                success: function (json) {
                    if (json.success) location.reload()
                }
            })
        }
    }
};

export {
    FaqOptions
}