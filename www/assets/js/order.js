export default function() {
    const selectorPrefix = '.js-page-order';
    if ($(selectorPrefix).length === 0) {
        return;
    }

    function addNewOrderItemRow() {
        let firstOrderItemRow = $('.orderItemRow').first();
        let newOrderItemRow = firstOrderItemRow.clone();
        newOrderItemRow.show();

        newOrderItemRow.find('.deleteOrderItemButton').click(function() {
            deleteOrderItemRow($(this));
        });

        newOrderItemRow.find('.orderItemSelect').change(function() {
            shouldAddNewOrderItemRow();
        });

        newOrderItemRow.insertAfter("div.orderItemRow:last");
    }

    $('#addNextOrderItemButton').click(function() {
        addNewOrderItemRow();
    });

    $('.orderItemSelect').change(function() {
        shouldAddNewOrderItemRow();
    });

    function shouldAddNewOrderItemRow() {
        if ($('.orderItemSelect').filter(function() {
            return $(this).val() === "0";
        }).length < 2) {
            addNewOrderItemRow();
        }
    }


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
