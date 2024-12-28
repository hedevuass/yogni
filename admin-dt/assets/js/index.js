import {
    Helper
} from "./helper.js";

let MainPage = {
    selectors: {
        'pageSelector': '#mainPage',
        'modalEditSelector': '#modalEditDocument',
        'togglePasswordButton': '.toggle-password',
        'contentSelector': '#input-content',
    },
    start: function () {
        $('#mainTable').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $(this.selectors.pageSelector).on('click', this.selectors.togglePasswordButton, function () {
            const target = $(this).data('target');
            const input = $(target);
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            $(this).text(type === 'password' ? 'Показать' : 'Скрыть');
        });

        $(this.selectors.pageSelector).on('click', '.modalAdd', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#documentEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
    },
    initTinyMCE: function () {
        if (typeof tinymce !== 'undefined') {
            
            tinymce.remove(`${MainPage.selectors.contentSelector}`);

            tinymce.init({
                selector: `${MainPage.selectors.contentSelector}`,
                plugins: 'lists link',
                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link',
                
                branding: false,
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                block_formats: 'Header 1=h1; Header 2=h2; Header 3=h3; Paragraph=p',
                style_formats: [
                    { title: 'Header 1', block: 'h1' },
                    { title: 'Header 2', block: 'h2' },
                    { title: 'Header 3', block: 'h3' },
                    { title: 'Paragraph', block: 'p' }
                ],
                setup: function (editor) {
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
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/documents/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(MainPage.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(MainPage.selectors.pageSelector).append(json.code)
                $(MainPage.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                MainPage.initTinyMCE();
            }
        })
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html();
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();
        
        $(`${MainPage.selectors.modalEditSelector} input, ${MainPage.selectors.modalEditSelector} textarea`).each(function() {
        formData.append($(this).attr('name'), $(this).val());
        });

        formData.append('content', tinymce.get(MainPage.selectors.contentSelector.replace('#', '')).getContent());
    
        $.ajax({
            type: "POST",
            url: "actions/documents/edit_doc.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(MainPage.selectors.modalEditSelector);
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
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/documents/delete.php",
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
    MainPage
}