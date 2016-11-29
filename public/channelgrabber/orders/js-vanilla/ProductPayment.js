define([
], function(
) {
    function ProductPayment(order) {
        var orderId = order;

        this.getOrderId = function ()
        {
            return orderId;
        };

        this.init = function() {
            this.listenForZeroRatedVATCheckboxCheck();
            this.listenForSubmitZeroRatedVATForm();
        }.call(this);
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

    ProductPayment.prototype.listenForSubmitZeroRatedVATForm = function () {
        var self = this;
        $('#zero-rated-vat-submit').click(function () {
            if (! $('input[name="zeroRatedVatCode"]').val() || ! $('input[name="zeroRatedVatNumber"]').val()) {
                return;
            }
            var vatCode = $('input[name="zeroRatedVatCode"]').val() + $('input[name="zeroRatedVatNumber"]').val();
            n.notice("Adding Zero-Rate VAT to the order.");
            $.ajax({
                url: '/order/markZeroRatedVat',
                data: {
                    order: self.getOrderId(),
                    recipientVatCode: vatCode
                },
                context: this,
                type: "POST",
                dataType: 'json',
                success : function(data, textStatus, request) {
                    if (data.error) {
                        var itid = request.getResponseHeader('ITID-Response');
                        return n.error("Failed to add Zero-Rate VAT to the order. Please contact support and provide the following reference code:\n"+itid);
                    }
                    n.success("Successfully added Zero-Rate VAT to the order.");
                    location.reload();
                },
                error: function(request, textStatus, errorThrown) {
                    return n.ajaxError(request, textStatus, errorThrown);
                }
            });
        });
    };

    return ProductPayment;
});