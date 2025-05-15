import './datatable-czech.js';
import './tables.js';

import category from './category.js';
import product from './product.js';
import order from './order.js';
import diningTable from './diningTable.js';
$(document).ready(function(){
    category();
    product();
    diningTable();
    order();

    setTimeout(flashMessage, 4200);
    function flashMessage() {
        $(".flash-message-alert").alert('close');
    }

    $(`.js-edit-start`).click(function () {
        let recordId = $(this).attr('data-record-id');
        $(`.js-edit-text`).show();
        $(`.js-edit-input`).hide();
        $(`.js-input-` + recordId).show();
        $(`.js-text-` + recordId).hide();
        $(this).toggle();
        $(this).siblings(".js-edit-input").toggle();
    });
});

