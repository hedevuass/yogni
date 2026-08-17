import { Helper } from "./helper.js";

let Request = {
    selectors: {
        pageSelector: '#requestsPage',
        tableSelector: '#requests',
        filterSelect: '#filterSelect'
    },
    start: function () {
        // Инициализация DataTable
        $(this.selectors.tableSelector).DataTable({
            language: Helper.dataTableLanguage(),
            iDisplayLength: 50,
            order: [[0, 'asc']]
        });
        $(this.selectors.pageSelector).on('change', '#filterSelect', function () {
            let filterValue = $(this).val();

            $('#requests tbody tr').each(function () {
                let row = $(this);
                let moderation = Number(row.data('moderation'));

                let show =
                    filterValue === 'all' ||
                    (filterValue === 'published' && moderation === 1) ||
                    (filterValue === 'spam' && moderation === 2) ||
                    (filterValue === 'new' && moderation === 3);

                row.toggle(show);
            });
        });

        $(this.selectors.pageSelector).on('click', '.publish', this.publish)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
    },
    publish: function () {
        let button = $(this);
        let buttonContent = button.html();
        let row = button.closest('tr');

        let id = button.data('id');

        let mailData = {
            email: button.data('specialist-email'),
            specialist_name: button.data('specialist-name'),
            name: row.find('.input-name').data('name'),
            contactInfo: row.find('.input-phone').data('phone'),
            message: row.find('.input-message').data('message')
        };

        let status = '1';

        if (!confirm('Вы уверены, что хотите отправить запрос?')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: '/actions/mail/send_message.php',
            dataType: 'json',
            data: mailData,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Request.selectors.pageSelector);
            },
            success: function (json) {

                if (json.error) {
                    Helper.loadingButton(button, buttonContent);
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`)
                            .addClass('is-invalid')
                            .after(Helper.invalidFeedback(value));
                    });
                    return;
                }

                if (json.fatal) {
                    Helper.loadingButton(button, buttonContent);
                    Helper.fatalError(json.fatal);
                    return;
                }

                // письмо ушло → обновляем статус
                $.ajax({
                    type: "POST",
                    url: "actions/request/update.php",
                    dataType: 'json',
                    data: { id: id, status: status },
                    beforeSend: function () {
                        Helper.loadingButton(button)
                    },
                    success: function (json) {
                        if (json.success) location.reload()
                    }
                })
            }
        });
    },
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this);

        let status = '2';

        if (confirm('Вы уверены, что хотите отметить запрос как "спам"?')) {
            $.ajax({
                type: "POST",
                url: "actions/request/update.php",
                dataType: 'json',
                data: { id: id, status: status },
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

export { Request };
