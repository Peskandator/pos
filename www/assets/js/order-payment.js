$(document).ready(function () {
    const currentCompanyId = window.currentCompanyId;
    const orderId = window.orderId;

    function calculateSelectedAmount() {
        var total = 0;
        $("input[id^='quantities-']").each(function() {
            var price = parseFloat($(this).data("price")) || 0;
            var quantity = parseInt($(this).val()) || 0;
            total += price * quantity;
        });
        return total.toFixed(2);
    }

    function calculateSelectedQuantity() {
        var totalQuantity = 0;
        $("input[id^='quantities-']").each(function() {
            var quantity = parseInt($(this).val()) || 0;
            totalQuantity += quantity;
        });
        return totalQuantity;
    }

    function updateQRCode() {
        var paymentMethod = $("#paymentMethod").val();
        if (paymentMethod === "qr") {
            var selectedAmount = calculateSelectedAmount();
            $("#selectedAmount").text(selectedAmount);
            if (selectedAmount <= 0) {
                $("#qrCodeContainer").hide();
                return;
            }
            $.ajax({
                url: "/admin/order-payment/generate-qr-code",
                method: "GET",
                data: {
                    orderId: orderId,
                    currentCompanyId: currentCompanyId,
                    amount: selectedAmount
                },
                success: function(response) {
                    if (response.qrCode) {
                        $("#qrCodeContainer").show();
                        $("#qrCodeImage").attr("src", response.qrCode);
                    } else {
                        alert("Nastala chyba při generování QR kódu.");
                    }
                },
                error: function() {
                    alert("Nastala chyba při generování QR kódu.");
                }
            });
        } else {
            $("#qrCodeContainer").hide();
        }
    }

    function updatePaymentButtonState() {
        var selectedQuantity = calculateSelectedQuantity();
        if (selectedQuantity > 0) {
            $("#paymentMethod").prop("disabled", false);
            $("button[type='submit']").prop("disabled", false);
        } else {
            $("#paymentMethod").prop("disabled", true);
            $("button[type='submit']").prop("disabled", true);
        }
    }

    $(document).on("input", "input[id^='quantities-']", function() {
        var selectedAmount = calculateSelectedAmount();
        $("#selectedAmount").text(selectedAmount);
        updatePaymentButtonState();
        updateQRCode();
    });

    $(".btn-qty-minus, .btn-qty-plus").on("click", function() {
        var itemId = $(this).data("item-id");
        var inputElement = $("#quantities-" + itemId);
        var currentValue = parseInt(inputElement.val()) || 0;
        var newValue = $(this).hasClass("btn-qty-plus") ? currentValue + 1 : currentValue - 1;
        var maxQuantity = parseInt(inputElement.attr("max"));
        var minQuantity = parseInt(inputElement.attr("min"));
        if (newValue >= minQuantity && newValue <= maxQuantity) {
            inputElement.val(newValue);
            inputElement.trigger("input");
        }
    });

    $("#paymentMethod").on("change", function() {
        updateQRCode();
    });

    updateQRCode();
    $("button[type='submit']").prop("disabled", true);
    $("#paymentMethod").prop("disabled", true);

    $("form").on("submit", function(e) {
        e.preventDefault();
        const $tableBody = $("#confirmPaymentItems");
        $tableBody.empty();
        let totalToPay = 0;
        let selected = false;

        $("input[id^='quantities-']").each(function() {
            const quantity = parseInt($(this).val()) || 0;
            if (quantity > 0) {
                selected = true;
                const price = parseFloat($(this).data("price")) || 0;
                const total = quantity * price;
                const itemId = $(this).attr("id").split("-")[1];
                const itemName = $(this).closest("tr").find("td:nth-child(2)").text();

                $tableBody.append(`
                                    <tr>
                                        <td>${ itemName }</td>
                                        <td>${ quantity }</td>
                                        <td>${ price.toFixed(2) } Kč</td>
                                        <td>${ total.toFixed(2) } Kč</td>
                                    </tr>
                                `);

                totalToPay += total;
            }
        });

        if (!selected) {
            alert("Musíte vybrat alespoň jednu položku k platbě.");
            return;
        }

        const paymentMethodText = $("#paymentMethod option:selected").text();
        $("#confirmPaymentMethod").text(paymentMethodText);
        $("#selectedAmountModal").text(totalToPay.toFixed(2));

        const paymentMethodValue = $("#paymentMethod").val();
        if (paymentMethodValue === "qr") {
            $.ajax({
                url: "/admin/order-payment/generate-qr-code",
                method: "GET",
                data: {
                    orderId: orderId,
                    currentCompanyId: currentCompanyId,
                    amount: totalToPay.toFixed(2)
                },
                success: function(response) {
                    if (response.qrCode) {
                        $("#confirmQrCodeImage").attr("src", response.qrCode);
                        $("#confirmQrCodeContainer").show();
                    }
                },
                error: function() {
                    alert("Chyba při načítání QR kódu.");
                }
            });
        } else {
            $("#confirmQrCodeContainer").hide();
        }

        const modal = new bootstrap.Modal(document.getElementById("confirmPaymentModal"));
        modal.show();
    });

    $("#confirmPaymentModalSubmit").on("click", function () {
        $("form").off("submit").submit();
    });
});