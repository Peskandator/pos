export default function() {
    const selectorPrefix = '.js-page-order';
    if ($(selectorPrefix).length === 0) {
        return;
    }

    $('#addNextOrderItemButton').click(function() {
        let firstOrderItemRow = $(`.orderItemRow`).first();
        let newOrderItemRow = firstOrderItemRow.clone();
        newOrderItemRow.show();

        newOrderItemRow.find('.deleteOrderItemButton').click(function() {
            deleteOrderItemRow($(this));
        });

        newOrderItemRow.insertAfter("div.orderItemRow:last")
    });

    $('.deleteOrderItemButton').click(function() {
        deleteOrderItemRow($(this));
    });

    function deleteOrderItemRow(buttonElement) {
        buttonElement.parent().parent().remove();
        updateOrderItemsJson()
    }

    $('#js-order-form').submit(function() {
        updateOrderItemsJson();
    });

    function updateOrderItemsJson() {
        let jsonObj = [];
        $('.orderItemRow').each(function() {
            let product = $(this).find('.orderItemSelect').val();
            let quantity = $(this).find('.orderItemQuantity').val();

            let orderItems = {product: product, quantity: quantity};

            jsonObj.push(orderItems);
        });

        let jsonString = JSON.stringify(jsonObj);
        $('#js-order-items-input').val(jsonString);
    }
}
