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
        })

        $(this.selectors.pageSelector).on('click', '.modalAdd', this.modalAdd)
        $(this.selectors.pageSelector).on('click', '#photoEdit', this.edit)
    },
    initTinyMCE: function () {
        if (typeof tinymce !== 'undefined') {

            tinymce.remove(`${Photo.selectors.contentSelector}, ${Photo.selectors.priceSelector}, ${Photo.selectors.nameSelector}`);

            tinymce.init({
                selector: `${Photo.selectors.contentSelector}, ${Photo.selectors.priceSelector}, ${Photo.selectors.nameSelector}`,
                plugins: 'lists link textcolor', 
                toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | forecolor backcolor',
                menubar: false,
                branding: false,
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
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                setup: function (editor) {
                    editor.on('init', function () {
                        
                    });
                }
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