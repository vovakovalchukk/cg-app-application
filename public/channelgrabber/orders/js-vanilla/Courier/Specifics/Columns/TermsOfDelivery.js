define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function TermsOfDelivery()
    {
        var init = function()
        {
            // this.listenForServiceChanges();
        };
        init.call(this);
    }

    TermsOfDelivery.SELECTOR_BULK_ACTION_TOD_CHECKBOX_ID = '#courier-terms-of-delivery-checkbox';
    TermsOfDelivery.SELECTOR_BULK_ACTION_TOD_CHECKBOX_CLASS = '.courier-terms-of-delivery-checkbox';
    TermsOfDelivery.SELECTOR_TOD_CHECKBOX_CLASS = '.courier-order-termsOfDelivery';

    TermsOfDelivery.prototype.listenForServiceChanges = function()
    {
    //     var self = this;
        $(TermsOfDelivery.SELECTOR_BULK_ACTION_TOD_CHECKBOX_ID).on('change', function(event, element, value)
        {
            if (this.checked) {
                console.log('ELEMENT CHECKED');
            } else {
                console.log('ELEMENT UNCHECKED');
            }
    //         if (value === undefined || value === "") {
    //             return;
    //         }
    //         var orderId = $(element).data('elementName').match(/^orderData\[(.+?)\]/)[1];
    //         self.updateShippingLabelCost(orderId, value, element);
        });

        return this;
    };
    //
    // TermsOfDelivery.prototype.updateShippingLabelCost = function(orderId, shippingService, element)
    // {
    //     var currentCostColumn = element.parents('tr').find(TermsOfDelivery.SELECTOR_COST_COLUMN_INPUT);
    //     var labelCosts = this.getStorage().get("labelCosts");
    //
    //     if (labelCosts === undefined) {
    //         return;
    //     }
    //
    //     currentCostColumn.val(labelCosts[orderId][shippingService].cost);
    //     this.updateTotalShippingCost();
    //     return this;
    // };
    //
    // TermsOfDelivery.prototype.updateTotalShippingCost = function()
    // {
    //     $(TermsOfDelivery.SELECTOR_CURRENCY_SYMBOL_DISPLAY).removeClass('hidden');
    //
    //     var totalLabelCost = 0;
    //     $(TermsOfDelivery.SELECTOR_COST_COLUMN_INPUT).each(function() {
    //         totalLabelCost += Number($(this).val());
    //     });
    //     $(TermsOfDelivery.SELECTOR_TOTAL_ORDER_LABEL_COST).text(totalLabelCost.toFixed(2));
    // };
    return TermsOfDelivery;
});
