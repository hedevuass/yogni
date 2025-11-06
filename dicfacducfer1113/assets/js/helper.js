let Helper = {
    selectors: {
        'modalError': '#modalError'
    },
    loadingButton: function (button, buttonVal = '') {
        if (!buttonVal) $(button).attr('disabled', 'disabled').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="visually-hidden">Loading...</span>')
        if (buttonVal) $(button).attr('disabled', false).html(buttonVal)
    },
    validationPhone: function () {
        this.value = this.value.replace(/[^0-9+]/g, '')
    },
    validationPassword: function () {
        this.value = this.value.replace(/[^\w\d]*/gi, '')
    },
    uploadImage: function (selector, e) {
        if (!e.target.files) return
        const file = e.target.files[0]
        if (!file.type.match('image')) return

        const reader = new FileReader()
        reader.onload = e => {
            const src = e.target.result
            $(selector).attr('src', src)
        }
        reader.readAsDataURL(file)
    },
    invalidFeedback: function (content) {
        return `<div class="invalid-feedback">${content}</div>`
    },
    removeValidation: function (selector) {
        $(selector).find('.invalid-feedback').remove()
        $(selector).find('input').removeClass('is-invalid')
    },
    fatalError: function (content) {
        $('.modal').modal('hide')
        $(this.selectors.modalError).remove()

        $('body').append(`
            <div class="modal fade" id="modalError" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Критическая ошибка</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ${content}
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                  </div>
                </div>
              </div>
            </div>
        `)

        $(this.selectors.modalError).modal('show')
    },
    dataTableLanguage: function () {
        return {
            lengthMenu: 'Показывать _MENU_',
            zeroRecords: 'Нет результатов',
            info: 'Страница _PAGE_ из _PAGES_',
            infoEmpty: 'Нет результатов',
            infoFiltered: '',
            search: 'Поиск:',
            paginate: {
                first: "Первая",
                previous: "Предыдущая",
                next: "Следующая",
                last: "Последняя"
            }
        }
    }
}

$(document).ready(function() {
    setTimeout(function() {
        $('input[type="search"]').attr('autocomplete', 'off');
    }, 100);
});

export {
    Helper
}