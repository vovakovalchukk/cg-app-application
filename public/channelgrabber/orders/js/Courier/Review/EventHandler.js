define([], function()
{
    function EventHandler(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.listenForCourierChange();
        };
        init.call(this);
    }

    EventHandler.SELECTOR_COURIER_SELECT = '.courier-courier-custom-select';

    EventHandler.prototype.listenForCourierChange = function()
    {
        var service = this.getService();
        $(document).on('change', EventHandler.SELECTOR_COURIER_SELECT, function(event, element, value)
        {
            var orderId = $(element).attr('data-element-name').split('_').pop();
            service.courierChanged(orderId, value);
        });
        return this;
    };

    return EventHandler;
});