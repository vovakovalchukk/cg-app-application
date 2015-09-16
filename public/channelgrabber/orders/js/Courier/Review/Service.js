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

    Service.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-review-service-select-';
    Service.SELECTOR_ORDER_SERVICE_CONTAINER_PREFIX = '#courier-service-options-';

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

    return Service;
});