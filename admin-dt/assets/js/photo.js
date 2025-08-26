import {
    Helper
} from "./helper.js";

let Photo = {
    selectors: {
        'pageSelector': '#photoPage',
        'modalEditSelector': '#modalEditPhoto',
        'nameSelector': '#input-name',
        'contentSelector': '#input-content',
        'priceSelector': '#input-price',
    },
    start: function () {
        $('#photoTable').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
            order: [[4, "asc"]],
        })

        /* $('#photoTable3').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
            order: [[0, "desc"]],
        }) */

            $('#photoTableFull').dataTable({
                language: Helper.dataTableLanguage(),
                iDisplayLength: 50,
                order: [[0, "desc"]],
            });

        $(this.selectors.pageSelector).on('click', '.modalAdd', this.modalAdd)
        $(this.selectors.pageSelector).on('click', '#photoEdit', this.edit)
    },
    initTinyMCE: function () {
        if (typeof tinymce !== 'undefined') {
    
            // Удаляем старые редакторы
            tinymce.remove(`${Photo.selectors.contentSelector}, ${Photo.selectors.priceSelector}, ${Photo.selectors.nameSelector}`);
    
            // Инициализация для каждого поля отдельно
            [
                Photo.selectors.contentSelector,
                Photo.selectors.priceSelector,
                Photo.selectors.nameSelector
            ].forEach(selector => {
                const el = document.querySelector(selector);
                if (!el) return;
    
                // Проверяем, если это input, то задаём высоту 200
                const isInput = el.tagName.toLowerCase() === 'input';
    
                tinymce.init({
                    target: el,
                    plugins: 'lists autolink', // без 'link'
                    toolbar: 'undo redo | formatselect | fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright | bullist numlist',
                    branding: false,
                    height: isInput ? 200 : undefined, // только для input
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
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: false,
                    readonly: false,
                    color_map: [
                        '7B9CCF', 'Inactive Blue Button',
                        '4F77B4', 'Active Blue Button',
                        'FF9E68', 'Inactive Orange Button',
                        'FF8139', 'Active Orange Button',
                        '0F86F3', 'Clickable Text Hover',
                        '222222', 'Heading Color (Semibold, Size 18)',
                        '000000', 'Subheading Color (Semibold, Size 14)',
                        'E6E6E6', 'Banner Hover 10% Darker',
                        '333333', 'Default Dark Text',
                        '0F86F3', 'Border Hover for Text with Outline',
                        'FF8139', 'Sidebar Hover',
                        'FFFFFF', 'White',
                        '000000', 'Black'
                    ],
                    color_picker_callback: function(callback, value) {
                        callback(value);
                    },
                    setup: function (editor) {
                        let modalEl = document.querySelector(Photo.selectors.modalEditSelector);
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
            });
    
        } else {
            console.error('TinyMCE не загружен.');
        }
    },    
    edit: function () {
        let button = $(this),
            buttonContent = button.html();
        
        let formData = new FormData();
        
        $(`${Photo.selectors.modalEditSelector} input, ${Photo.selectors.modalEditSelector} textarea`).each(function() {
            if ($(this).attr('type') === 'file' && $(this)[0].files[0]) {
                formData.append($(this).attr('name'), $(this)[0].files[0]);
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        });
        
        formData.append('description', tinymce.get(Photo.selectors.contentSelector.replace('#', '')).getContent());
        formData.append('price', tinymce.get(Photo.selectors.priceSelector.replace('#', '')).getContent());
        formData.append('title', tinymce.get(Photo.selectors.nameSelector.replace('#', '')).getContent());
        
        $.ajax({
            type: "POST",
            url: "actions/photo/edit_photo.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Photo.selectors.modalEditSelector);
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
            }
        });
    },
    modalAdd: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/photo/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(Photo.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(Photo.selectors.pageSelector).append(json.code)
                $(Photo.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)

                Photo.initTinyMCE();
            }
        })
    }
};

export {
    Photo
}