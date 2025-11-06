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
            order: [[0, 'desc']] // сортировка по дате (6-й столбец)
        });
    }
};

export { Request };
