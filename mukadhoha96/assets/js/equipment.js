let EquipmentPage = {
    selectors: {
        pageSelector: '#equipmentPage'
    },
    start: function () {
        let p = this.selectors.pageSelector;
        
        $(p).on('click', '.add-row-btn', this.addRow);
        $(p).on('click', '.trigger-file', this.triggerFileInput);
        $(p).on('change', '.upload-input-normal, .upload-input-pink', this.previewImage);
        $(p).on('click', '.remove-img-btn', this.clearNormalImg);
        $(p).on('click', '.remove-pink-btn', this.clearPinkImg);
        $(p).on('click', '.save-row', this.save);
        $(p).on('click', '.delete-row', this.delete);
    },

    addRow: function () {
        let category = $(this).data('category');
        let tbody = $('#table-' + category + ' tbody');
        
        let newRow = `
            <tr data-id="new" data-category="${category}">
                <td>
                    <input type="text" class="form-control item-name" value="" placeholder="Введите название...">
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-preview-box rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid #dee2e6; overflow: hidden;"></div>
                        <input type="file" class="d-none upload-input-normal" accept="image/*">
                        <button type="button" class="btn btn-primary btn-sm trigger-file">Загрузить</button>
                        <button type="button" class="btn btn-danger btn-sm remove-img-btn">Удалить</button>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-preview-box rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid #dee2e6; overflow: hidden;"></div>
                        <input type="file" class="d-none upload-input-pink" accept="image/*">
                        <button type="button" class="btn btn-primary btn-sm trigger-file">Загрузить</button>
                        <button type="button" class="btn btn-danger btn-sm remove-pink-btn">Удалить</button>
                    </div>
                </td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm save-row">Сохранить</button>
                        <button type="button" class="btn btn-danger btn-sm delete-row">Удалить</button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(newRow);
    },

    triggerFileInput: function () {
        $(this).siblings('input[type=file]').trigger('click');
    },

    previewImage: function (e) {
        let file = e.target.files[0];
        let previewBox = $(this).siblings('.icon-preview-box');
        if (file) {
            let reader = new FileReader();
            reader.onload = function (event) {
                previewBox.html(`<img src="${event.target.result}" class="img-fluid" style="max-height: 100%;">`);
            };
            reader.readAsDataURL(file);
        }
    },

    clearNormalImg: function () {
        let parent = $(this).closest('.d-flex');
        parent.find('input[type=file]').val('');
        parent.find('.icon-preview-box').empty();
        $(this).closest('tr').attr('data-delete-normal', '1');
    },

    clearPinkImg: function () {
        let parent = $(this).closest('.d-flex');
        parent.find('input[type=file]').val('');
        parent.find('.icon-preview-box').empty();
        $(this).closest('tr').attr('data-delete-pink', '1');
    },

    save: function () {
        let row = $(this).closest('tr');
        let id = row.data('id');
        let category = row.data('category');
        let name = row.find('.item-name').val().trim();
        
        // Пункт 3 инструкции: если имя пустое — пресекаем сохранение
        if (name === '') {
            row.find('.item-name').addClass('is-invalid');
            return false;
        }
        row.find('.item-name').removeClass('is-invalid');

        let formData = new FormData();
        formData.append('id', id);
        formData.append('category', category);
        formData.append('name', name);

        let normalFile = row.find('.upload-input-normal')[0].files[0];
        if (normalFile) formData.append('img_normal', normalFile);

        let pinkFile = row.find('.upload-input-pink')[0].files[0];
        if (pinkFile) formData.append('img_pink', pinkFile);

        if (row.attr('data-delete-normal')) formData.append('delete_normal', '1');
        if (row.attr('data-delete-pink')) formData.append('delete_pink', '1');

        let saveBtn = $(this);
        saveBtn.prop('disabled', true).text('Сохранено');

        $.ajax({
            type: "POST",
            url: "actions/equipment/save_equipment.php",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function (response) {
                // Пункт 2 инструкции: после сохранения перезагружаем страницу для алфавитного рендеринга
                if (response.success) {
                    /* location.reload(); */
                } else {
                    saveBtn.prop('disabled', false).text('Сохранить');
                    alert(response.error || 'Ошибка при сохранении');
                }
            }
        });
    },

    delete: function () {
        let row = $(this).closest('tr');
        let id = row.data('id');

        if (id === 'new') {
            row.remove();
            return;
        }

        if (confirm('Вы уверены, что хотите удалить этот элемент?')) {
            $.ajax({
                type: "POST",
                url: "actions/equipment/delete_equipment.php",
                data: { id: id },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        row.remove();
                    } else {
                        alert(response.error || 'Ошибка при удалении');
                    }
                }
            });
        }
    }
};

export {
    EquipmentPage
}