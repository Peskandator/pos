import csLang from './datatable-lang-cs.js';

$(document).ready(function() {

    $('.js-table').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'czech', targets: "_all"},
            ],
            paging: false
        }
    );

    $('.js-table-products').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 2]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: -1}
            ],
            paging: true
        }
    );

    $('.js-table-products-filtered').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 2, 3, 4]},
                {type: 'czech', targets: "_all"},
            ],
            paging: true
        }
    );

    $('.js-table-dials').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: -1}
            ],
            paging: true
        }
    );

    $('.js-table-orders').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 1, 4, 5]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: [-1, 3]}
            ],
            paging: true
        }
    );

    $('.js-table-orders-filtered').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 3, 4, 5, 6]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: -1}
            ],
            paging: true
        }
    );

    $('.js-table-payment-items').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [2, 3]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: 0}
            ],
            paging: false,
            order: []
        }
    );

    $('.js-table-order-item-states').DataTable(
        {
            language: csLang,
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [1, 2, 3]},
                {type: 'czech', targets: "_all"},
            ],
            paging: false,
            order: []
        }
    );

});