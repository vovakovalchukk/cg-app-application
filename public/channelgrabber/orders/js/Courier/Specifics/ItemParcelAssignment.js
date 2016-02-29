define([], function()
{
    function ItemParcelAssignment(element, orderData)
    {
        this.getElement = function()
        {
            return element;
        };

        this.getOrderData = function()
        {
            return orderData;
        };

        var init = function()
        {
            if (orderData.parcels.length == 1) {
                this.setDataForSingleParcel();
            } else {
                this.preparePopup();
            }
        };
        init.call(this);
    }

    ItemParcelAssignment.SELECTOR_INPUT = 'input.courier-order-itemParcelAssignment';
    ItemParcelAssignment.SELECTOR_PARENT = 'td';
    ItemParcelAssignment.SELECTOR_BUTTON = 'div.button';

    ItemParcelAssignment.instances = [];

    ItemParcelAssignment.setUp = function(dataTable, service)
    {
        ItemParcelAssignment.reset();
        if ($(ItemParcelAssignment.SELECTOR_INPUT, dataTable).length == 0) {
            return;
        }
        $(ItemParcelAssignment.SELECTOR_INPUT, dataTable).each(function()
        {
            var element = this;
            var orderId = element.dataset.orderId;
            var orderData = service.getDataForOrder(orderId);
            var instance = new ItemParcelAssignment(element, orderData);
            ItemParcelAssignment.instances.push(instance);
        });
    };

    ItemParcelAssignment.reset = function()
    {
        delete ItemParcelAssignment.instances;
        ItemParcelAssignment.instances = [];
    };

    ItemParcelAssignment.prototype.setDataForSingleParcel = function()
    {
        var data = {};
        var items = this.getOrderData().items;
        for (var index in items) {
            var item = items[index];
            data[item.id] = item.quantity;
        }
        $(this.getElement()).val(JSON.stringify(data));
        this.markAsAssigned(true);
    };

    ItemParcelAssignment.prototype.markAsAssigned = function(disable)
    {
        var button = $(this.getElement()).closest(ItemParcelAssignment.SELECTOR_PARENT).find(ItemParcelAssignment.SELECTOR_BUTTON);
        button.find('.title').html('Assigned');
        if (disable) {
            button.addClass('disabled');
        }
    };

    ItemParcelAssignment.prototype.preparePopup = function()
    {
        // TODO
    };

    return ItemParcelAssignment;
});