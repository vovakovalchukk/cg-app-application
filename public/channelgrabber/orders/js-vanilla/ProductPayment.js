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
            this.listenForVatCodeSelectboxChange();
            this.listenForVatNumberInputChange();
            this.listenForSubmitZeroRatedVATForm();
        }.call(this);
    }

    ProductPayment.prototype.CHECKBOX_SELECTOR = '#zero-rated-vat-checkbox';
    ProductPayment.prototype.FORM_SELECTOR = '#zero-rated-vat-form';
    ProductPayment.prototype.FORM_SUBMIT_SELECTOR = '#zero-rated-vat-submit';
    ProductPayment.prototype.FORM_VAT_CODE_DROPDOWN_SELECTOR = '.zero-rated-vat-code-select';
    ProductPayment.prototype.FORM_VAT_CODE_DROPDOWN_INPUT_SELECTOR = 'input[name="zeroRatedVatCode"]';
    ProductPayment.prototype.FORM_VAT_NUMBER_INPUT_SELECTOR = 'input[name="zeroRatedVatNumber"]';

    ProductPayment.prototype.listenForZeroRatedVATCheckboxCheck = function () {
        $(ProductPayment.prototype.CHECKBOX_SELECTOR).change(function() {
            if($(this).is(":checked")) {
                $(ProductPayment.prototype.FORM_SELECTOR).show();
            } else {
                $(ProductPayment.prototype.FORM_SELECTOR).hide();
            }
        });
    };

    ProductPayment.prototype.listenForVatCodeSelectboxChange = function () {
        $(ProductPayment.prototype.FORM_VAT_CODE_DROPDOWN_SELECTOR).change(function() {
            $(ProductPayment.prototype.FORM_SUBMIT_SELECTOR).removeClass('disabled');
        });
    };

    ProductPayment.prototype.listenForVatNumberInputChange = function () {
        $(ProductPayment.prototype.FORM_VAT_NUMBER_INPUT_SELECTOR).change(function() {
            $(ProductPayment.prototype.FORM_SUBMIT_SELECTOR).removeClass('disabled');
        });
    };

    ProductPayment.prototype.listenForSubmitZeroRatedVATForm = function () {
        var self = this;
        $(ProductPayment.prototype.FORM_SUBMIT_SELECTOR).click(function () {
            if (! $(ProductPayment.prototype.FORM_VAT_CODE_DROPDOWN_INPUT_SELECTOR).val()
                || ! $(ProductPayment.prototype.FORM_VAT_NUMBER_INPUT_SELECTOR).val()
                || $(ProductPayment.prototype.FORM_SUBMIT_SELECTOR).hasClass('disabled')) {
                return;
            }

            $(ProductPayment.prototype.FORM_SUBMIT_SELECTOR).addClass('disabled');
            var url = '/orders/'+self.getOrderId()+'/markZeroRatedVat';

            n.notice("Adding Zero-Rate VAT to the order.");
            $.ajax({
                url: url,
                data: {
                    order: self.getOrderId(),
                    countryCode: $(ProductPayment.prototype.FORM_VAT_CODE_DROPDOWN_INPUT_SELECTOR).val(),
                    vatNumber: $(ProductPayment.prototype.FORM_VAT_NUMBER_INPUT_SELECTOR).val()
                },
                context: this,
                type: "POST",
                dataType: 'json',
                success : function(data, textStatus, request) {
                    if (!data.success) {
                        var itid = request.getResponseHeader('ITID-Response');
                        var errorMessage = data.error ? data.error : "Error: Please contact support and provide the following reference code: "+itid;
                        return n.error(errorMessage);
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