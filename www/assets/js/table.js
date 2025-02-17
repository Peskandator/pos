export default function() {
    const selectorPrefix = '.js-page-tables';
    if ($(selectorPrefix).length === 0) {
        return;
    }

    $(`.js-edit-table-start`).click(function (e) {
        let tableId = parseInt($(this).attr('data-table-id'));
        $(`.js-edit-table-id`).val(tableId);

        let tableNumber = $(this).attr('data-table-number');
        $(`.js-edit-table-number`).val(tableNumber);

        let tableDescription = $(this).attr('data-table-description');
        $(`.js-edit-table-description`).val(tableDescription);
    });
}