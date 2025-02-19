export default function() {
    const selectorPrefix = '.js-page-dining-tables';
    if ($(selectorPrefix).length === 0) {
        return;
    }

    $(`.js-edit-dining-table-start`).click(function (e) {
        let tableId = parseInt($(this).attr('data-dining-table-id'));
        $(`.js-edit-dining-table-id`).val(tableId);

        let tableNumber = $(this).attr('data-dining-table-number');
        $(`.js-edit-dining-table-number`).val(tableNumber);

        let tableDescription = $(this).attr('data-dining-table-description');
        $(`.js-edit-dining-table-description`).val(tableDescription);
    });
}