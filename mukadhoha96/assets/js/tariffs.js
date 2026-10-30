import {
    Helper
} from "./helper.js";

let Tariffs = {
    selectors: {
        'pageSelector': '#tariffPage',
        'modalEditRequestSelector': '#modalEditRequest',
        'modalEditTariffSelector': '#modalEditTariff',
        'modalEditPromotionSelector': '#modalEditPromotion',
        'modalEditOptionSelector': '#modalEditOption',
        'addOptionButton': '#add-option-btn',
        'optionsContainer': '#options-container',
        'deleteOptionButton': '.delete-option-btn'
    },
    start: function () {
        $('#requestPromo').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $('#tariffs').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $('#options').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $('#promo-options').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        const connectedPromoTable = $('#connected-promo-options').DataTable({
            language: Helper.dataTableLanguage(),
            iDisplayLength: 5,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 0, visible: false, searchable: false }
            ]
        });

        const connectedTariffsTable = $('#connected-tariffs').DataTable({
            language: Helper.dataTableLanguage(),
            iDisplayLength: 5,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 0, visible: false, searchable: false }
            ]
        });

        const connectedOptionsTable = $('#connected-options').DataTable({
            language: Helper.dataTableLanguage(),
            iDisplayLength: 5,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 0, visible: false, searchable: false }
            ]
        });

        // Кнопки "Показать все" для каждой таблицы
        $(this.selectors.pageSelector).on('click', '#show-all-promo', function () {
            connectedPromoTable.page.len(-1).draw();
        });

        $(this.selectors.pageSelector).on('click', '#show-all-tariffs', function () {
            connectedTariffsTable.page.len(-1).draw();
        });

        $(this.selectors.pageSelector).on('click', '#show-all-options', function () {
            connectedOptionsTable.page.len(-1).draw();
        });

        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#requestEdit', this.edit_request)
        $(this.selectors.pageSelector).on('click', '#tariffEdit, #optionEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '#promotionEdit', this.edit_promo)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', this.selectors.addOptionButton, this.addOption);
        $(this.selectors.pageSelector).on('click', this.selectors.deleteOptionButton, this.deleteOption);
    },
    addOption: function () {
        let optionCounter = $('#options-container .option-item').length; // Считаем количество существующих опций

        // Создаем новый HTML для поля ввода новой опции с полем для загрузки файла
        let newOptionHtml = `
            <div class="mb-3 option-item" data-key="${optionCounter + 1}">
                <label for="input-option-${optionCounter + 1}" class="form-label">Опция ${optionCounter + 1}</label>
                <div class="row mb-3">
                    <!-- Колонка для поля ввода и изображения -->
                    <div class="col-10">
                        <input type="text" name="options[${optionCounter + 1}][text]" class="form-control" id="input-option-${optionCounter + 1}" required>

                        <!-- Поле для выбора изображения -->
                        <input type="hidden" name="options[${optionCounter + 1}][image]" value="">
                    </div>
                    <!-- Колонка для кнопки удаления -->
                    <div class="col-2">
                        <button type="button" class="btn btn-danger btn-sm delete-option-btn">Удалить</button>
                    </div>
                </div>
            </div>
        `;


        // Добавляем новый блок в контейнер
        $(Tariffs.selectors.optionsContainer).append(newOptionHtml);
    },
    // Метод для удаления опции
    deleteOption: function () {
        $(this).closest('.option-item').remove();
    },
    initModalScripts: function () {
        // Открытие/закрытие дочерних элементов
        $(document).off('click', '.toggle-children').on('click', '.toggle-children', function () {
            const button = $(this);
            const target = $(button.data('target'));

            if (target.length) {
                target.toggleClass('d-none');
                button.text(target.hasClass('d-none') ? '+' : '-');
            } else {
                console.warn('Элемент не найден:', button.data('target'));
            }
        });

        // Изменение цены дочерних элементов при изменении родительских
        $(document).off('input', '.parent-price').on('input', '.parent-price', function () {
            const parentInput = $(this);
            const parentValue = parentInput.val();
            const childIds = parentInput.data('children');

            if (childIds) {
                childIds.split(',').forEach(id => {
                    const childInput = $(`.child-price[data-id="${id}"]`);
                    if (childInput.length && !childInput.data('updated')) {
                        childInput.val(parentValue);
                    }
                });
            }
        });

        $(document).off('input', '.child-price').on('input', '.child-price', function () {
            $(this).data('updated', true);
        });
    },
    modalEdit: function () {
        let button = $(this),
            type = button.attr('data-type'),
            price_id = button.attr('data-price-id') ?? 0,
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/tariff/get_modal.php",
            dataType: 'json',
            data: {
                id: id,
                type: type,
                price_id: price_id
            },
            beforeSend: function () {
                Helper.loadingButton(button)
                $(Tariffs.selectors.modalEditRequestSelector).remove()
                $(Tariffs.selectors.modalEditTariffSelector).remove()
                $(Tariffs.selectors.modalEditPromotionSelector).remove()
                $(Tariffs.selectors.modalEditOptionSelector).remove()
            },
            success: function (json) {
                $(Tariffs.selectors.pageSelector).append(json.code)
                $(Tariffs.selectors.modalEditRequestSelector).modal('show')
                $(Tariffs.selectors.modalEditTariffSelector).modal('show')
                $(Tariffs.selectors.modalEditPromotionSelector).modal('show')
                $(Tariffs.selectors.modalEditOptionSelector).modal('show')

                Tariffs.initModalScripts();

                Helper.loadingButton(button, buttonContent)
            }
        })
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html()

        function collectPrices() {
            let prices = [];
            $('.parent-price, .child-price').each(function () {
                let id = $(this).data('id');
                let field = $(this).data('field');
                let value = $(this).val();

                if (id && field) {
                    prices.push({ id, field, value });
                }
            });
            return prices;
        }

        let prices = collectPrices();

        $.ajax({
            type: "POST",
            url: "actions/tariff/edit_tariff.php",
            dataType: 'json',
            data: $(`${Tariffs.selectors.modalEditOptionSelector} input, ${Tariffs.selectors.modalEditTariffSelector} input, ${Tariffs.selectors.modalEditPromotionSelector} input`),
            beforeSend: function () {
                Helper.loadingButton(button)
                Helper.removeValidation(Tariffs.selectors.modalEditOptionSelector)
                Helper.removeValidation(Tariffs.selectors.modalEditTariffSelector)
                Helper.removeValidation(Tariffs.selectors.modalEditPromotionSelector)
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
        })
    }, edit_promo: function () {
        let button = $(this),
            buttonContent = button.html(),
            price_id = button.attr('data-price-id');

        // Функция для сбора данных цен
        function collectPrices() {
            let prices = [];
            $('.parent-price, .child-price').each(function () {
                let id = $(this).data('id');
                let value = $(this).val();

                if (id) {
                    prices.push({ id, value });
                }
            });
            return prices;
        }

        let prices = collectPrices();

        // Отправка данных через AJAX
        $.ajax({
            type: "POST",
            url: "actions/tariff/edit_promo.php",
            dataType: 'json',
            data: { prices, price_id },
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Tariffs.selectors.modalEditPromotionSelector);
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
            error: function () {
                Helper.loadingButton(button, buttonContent);
                Helper.fatalError('Произошла ошибка при отправке данных.');
            }
        });
    },
    edit_request: function () {
        let button = $(this),
            buttonContent = button.html(),
            modal = Tariffs.selectors.modalEditRequestSelector
     
        $.ajax({
            type: "POST",
            url: "actions/tariff/edit_request.php",
            dataType: 'json',
            data: $(`${modal} input, ${modal} textarea`),
            beforeSend: function () {
                Helper.loadingButton(button)
                Helper.removeValidation(modal)
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
            },
            error: function () {
                Helper.loadingButton(button, buttonContent)
                Helper.fatalError('Произошла ошибка при отправке данных.')
            }
        })
    }, 
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/tariff/delete.php",
                dataType: 'json',
                data: { id: id },
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
    Tariffs
}