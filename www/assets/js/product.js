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

    function addNewProductRow() {
        let firstProductRow = $('.productGroupRow').first();
        let newProductRow = firstProductRow.clone();
        newProductRow.show();

        newProductRow.find('.deleteProductButton').click(function() {
            deleteProductRow($(this));
        });

        newProductRow.find('.productGroupItemSelect').change(function() {
            shouldAddNewProductRow();
        });

        newProductRow.insertAfter("div.productGroupRow:last");
    }

    $('#addNextProductToGroupButton').click(function() {
        addNewProductRow();
    });


    $('.productGroupItemSelect').change(function() {
        shouldAddNewProductRow();
    });

    function shouldAddNewProductRow() {
        if ($('.productGroupItemSelect').filter(function() {
            return $(this).val() === "0";
        }).length < 2) {
            addNewProductRow();
        }
    }


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
