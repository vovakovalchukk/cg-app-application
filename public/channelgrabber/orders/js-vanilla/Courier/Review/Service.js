define(['./EventHandler.js', '../ShippingServices.js'], function(EventHandler, shippingServices)
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

        this.getShippingServices = function()
        {
            return shippingServices;
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

    //todo - remove the duplication here and eventHandler
    Service.SELECTOR_COURIER_SELECT = 'div.courier-courier-custom-select';

    Service.prototype.bulkChangeAllOrderCouriers = function(valueOfOptionToChangeTo)
    {
        $( Service.SELECTOR_COURIER_SELECT).each(function(index, selectElement){
            let selectOption = $(selectElement).find(`li[data-value=${valueOfOptionToChangeTo}]`);
            selectOption.click();
            selectElement.classList.remove('active');
        });
    };


    Service.prototype.courierChanged = function(orderId, courierId)
    {   
        console.log('courier change start...');


        this.getShippingServices().loadServicesSelectForOrder(orderId, courierId);
        this.getDataTable().cgDataTable('adjustTable');
        console.log('courier change end...');


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
