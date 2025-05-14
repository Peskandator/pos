export default function () {
    const selectorPrefix = '.js-page-order';
    if ($(selectorPrefix).length === 0) {
        return;
    }

    function addNewOrderItemRow() {
        let templateRow = $('.orderItemRow.d-none').first();
        let newOrderItemRow = templateRow.clone(false, false);

        newOrderItemRow.removeClass('d-none');
        newOrderItemRow.find('select.orderItemSelect').val("0");
        newOrderItemRow.find('input.orderItemQuantity').val("");
        newOrderItemRow.find('.is-invalid').removeClass('is-invalid');
        newOrderItemRow.find('.form-error').remove();

        const lastRow = $('.orderItemRow:not(.d-none):last');
        if (lastRow.length > 0) {
            newOrderItemRow.insertAfter(lastRow);
        } else {
            $('#orderItemRows').append(newOrderItemRow);
        }
    }

    $('#addNextOrderItemButton').click(function () {
        addNewOrderItemRow();
    });

    $(document).on('click', '.deleteOrderItemButton', function () {
        deleteOrderItemRow($(this));
    });

    $(document).on('change', '.orderItemSelect', function () {
        shouldAddNewOrderItemRow();
    });

    function shouldAddNewOrderItemRow() {
        if ($('.orderItemSelect').filter(function () {
            return $(this).val() === "0";
        }).length < 2) {
            addNewOrderItemRow();
        }
    }

    function deleteOrderItemRow(buttonElement) {
        buttonElement.closest('.orderItemRow').remove();
        updateOrderItemsJson();
    }

    $('#js-order-form').submit(function () {
        updateOrderItemsJson();
    });

    function updateOrderItemsJson() {
        let jsonObj = [];
        $('.orderItemRow:not(.d-none)').each(function () {
            let product = $(this).find('.orderItemSelect').val();
            let quantity = $(this).find('.orderItemQuantity').val();

            jsonObj.push({ product, quantity });
        });

        $('#js-order-items-input').val(JSON.stringify(jsonObj));
    }
}
