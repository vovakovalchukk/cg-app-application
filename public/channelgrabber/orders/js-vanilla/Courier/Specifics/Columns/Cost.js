define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function Cost()
    {
        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    Cost.SELECTOR_ORDER_LABEL_COST_INPUT_PREFIX = '#courier-parcel-cost-';
    Cost.SELECTOR_COST_COLUMN_INPUT = '.courier-label-cost';
    Cost.SELECTOR_TOTAL_ORDER_LABEL_COST = '.order-total-label-cost';
    Cost.SELECTOR_CURRENCY_SYMBOL_DISPLAY = '.total-cost .currency';

    Cost.prototype.listenForServiceChanges = function()
    {
        var self = this;
        $(document).on('change', ServiceDependantOptionsAbstract.SELECTOR_SERVICE_SELECT, function(event, element, value)
        {
            var orderId = $(element).data('elementName').match(/^orderData\[(.+?)\]/)[1];
            self.updateShippingLabelCost(orderId, value, element);
        });
        return this;
    };

    Cost.prototype.updateShippingLabelCost = function(orderId, value, element)
    {
        var labelCosts = $(Cost.SELECTOR_ORDER_LABEL_COST_INPUT_PREFIX + orderId + '-' + 1).data('label-costs');
        if (labelCosts === undefined) {
            return;
        }
        var currentCostColumn = element.parents('tr').find(Cost.SELECTOR_COST_COLUMN_INPUT);
        currentCostColumn.val(labelCosts[orderId][value].cost);
        this.updateTotalShippingCost();
        return this;
    };

    Cost.prototype.updateTotalShippingCost = function()
    {
        $(Cost.SELECTOR_CURRENCY_SYMBOL_DISPLAY).removeClass('hidden');

        var totalLabelCost = 0;
        $(Cost.SELECTOR_COST_COLUMN_INPUT).each(function() {
           totalLabelCost += Number($(this).val());
        });
        $(Cost.SELECTOR_TOTAL_ORDER_LABEL_COST).text(totalLabelCost.toFixed(2));
    }

    return Cost;
});
