define(['./EventHandler.js'], function(EventHandler)
{
    function Service(dataTable)
    {
        var eventHandler;

        this.getDataTable = function()
        {
            return dataTable;
        };

        this.setEventHandler = function(newEventHandler)
        {
            eventHandler = newEventHandler;
            return this;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
        };
        init.call(this);
    }

    Service.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-service-select-';
    Service.SELECTOR_ORDER_SERVICE_CONTAINER_PREFIX = '#courier-service-options-';
    Service.SELECTOR_ORDER_ROWS = '#datatable tbody tr';
    Service.SELECTOR_ORDER_FORM = '#continue-form';

    Service.prototype.courierChanged = function(orderId, courierId)
    {
        var name = 'service_'+orderId;
        $('div[data-element-name="'+name+'"]').remove();

        var serviceSelectCopy = $(Service.SELECTOR_SERVICE_SELECT_PREFIX+courierId).clone();
        serviceSelectCopy.removeAttr('id').attr('data-element-name', name).addClass('courier-service-custom-select');
        $('input[type=hidden]', serviceSelectCopy).attr('name', name);

        $(Service.SELECTOR_ORDER_SERVICE_CONTAINER_PREFIX+orderId).append(serviceSelectCopy);
        return this;
    };

    Service.prototype.continue = function()
    {
        if (!this.checkAllCouriersAndServicesSet()) {
            n.error('Please select a courier and service for each order', true);
            return;
        }

        $(Service.SELECTOR_ORDER_FORM).submit();
    };

    Service.prototype.checkAllCouriersAndServicesSet = function()
    {
        var allSet = true;
        $(Service.SELECTOR_ORDER_ROWS).each(function()
        {
            var row = this;
            var courierSelect = $(EventHandler.SELECTOR_COURIER_SELECT, row);
            if (courierSelect.length > 0 && !$('input', courierSelect).val()) {
                allSet = false;
                return false;
            }
            var serviceSelect = $(EventHandler.SELECTOR_SERVICE_SELECT, row);
            if (serviceSelect.length > 0 && !$('input', serviceSelect).val()) {
                allSet = false;
                return false;
            }
        });
        return allSet;
    };

    return Service;
});