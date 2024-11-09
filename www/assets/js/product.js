export default function() {
    const selectorPrefix = '.js-page-products';
    if ($(selectorPrefix).length === 0) {
        return;
    }


    let isGroupCheckbox = $('#isGroupCheckbox');

    checkIsGroupCheckBox();
    isGroupCheckbox.change(function() {
        checkIsGroupCheckBox()
    });

    function checkIsGroupCheckBox () {
        if(isGroupCheckbox.is(":checked")) {
            $(`#productGroupBox`).show();
        } else {
            $(`#productGroupBox`).hide();
        }
    }

    $('#addNextProductToGroupButton').click(function() {
        let firstProductRow = $(`.productGroupRow`).first();
        let newProductRow = firstProductRow.clone();
        newProductRow.show();

        newProductRow.find('.deleteProductButton').click(function() {
            deleteProductRow($(this));
        });

        newProductRow.insertAfter("div.productGroupRow:last")
    });

    $('.deleteProductButton').click(function() {
        deleteProductRow($(this));
    });

    function deleteProductRow (buttonElement) {
        buttonElement.parent().parent().remove();
        updateProductsInGroupsJson()
    }

    $('#js-product-form').submit(function() {
        updateProductsInGroupsJson();
    });

    function updateProductsInGroupsJson() {
        let jsonObj = [];
        $('.productGroupRow').each(function() {
            let product = $(this).find('.productGroupItemSelect').val();
            let quantity = $(this).find('.productGroupItemQuantity').val();

            let productInGroup = {product: product, quantity: quantity};

            jsonObj.push(productInGroup);
        });

        let jsonString = JSON.stringify(jsonObj);
        $('#js-products-in-group-input').val(jsonString);
    }
}
