define([
], function(
) {
    function ProductPayment() {
        var init = function() {
            this.listenForZeroRatedVATCheckboxCheck();
        };

        init.call(this);
    }

    ProductPayment.prototype.listenForZeroRatedVATCheckboxCheck = function () {
        $('#zero-rated-vat-checkbox').change(function() {
            if($(this).is(":checked")) {
                $('#zero-rated-vat-form').show();
            } else {
                $('#zero-rated-vat-form').hide();
            }
        });
    };

    ProductPayment.prototype.submitZeroRatedVATForm = function () {
        this.getNotificationHandler().notice("Submitting the Zero Rated VAT values.");
        $.ajax({
            url: this.getElement().data("url"),
            data: {
                "orders": orders
            },
            context: this,
            type: "POST",
            dataType: 'json',
            success : function(data, textStatus, request) {
                if (data.error) {
                    var itid = request.getResponseHeader('ITID-Response');
                    return this.getNotificationHandler().error("Failed to mark order as paid. Please contact support and provide the following reference code:\n"+itid);
                }
                this.getNotificationHandler().success("Successfully marked order as paid");
                location.reload();
            },
            error: function(request, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            }
        });
    };

    return ProductPayment;
});