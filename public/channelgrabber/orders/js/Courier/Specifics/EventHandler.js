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
            this.listenToNavLinkClicks()
                .listenToParcelsChange()
                .listenToItemWeightKeypress()
                .listenToCreateLabelButtons()
                .listenToPrintLabelButtons()
                .listenToCancelButtons();
        };
        init.call(this);
    }

    EventHandler.SELECTOR_NAV_LINKS = 'a.courier-specifics-nav-link';
    EventHandler.SELECTOR_PARCEL_INPUT = '.courier-parcels .courier-parcels-input';
    EventHandler.SELECTOR_ITEM_WEIGHT_INPUT = '.courier-item-weight';
    EventHandler.SELECTOR_ORDER_WEIGHT_INPUT_PREFIX = '#courier-order-weight-';
    EventHandler.SELECTOR_CREATE_LABEL_BUTTON = '.courier-create-label-button';
    EventHandler.SELECTOR_PRINT_LABEL_BUTTON = '.courier-print-label-button';
    EventHandler.SELECTOR_CANCEL_BUTTON = '.courier-cancel-label-button';

    EventHandler.prototype.listenToNavLinkClicks = function()
    {
        var service = this.getService();
        $(EventHandler.SELECTOR_NAV_LINKS).click(function(event)
        {
            event.preventDefault();
            var element = this;
            service.courierLinkChosen($(element).attr('href'));
        });
        return this;
    };

    EventHandler.prototype.listenToParcelsChange = function()
    {
        var service = this.getService();
        $(document).on('save', EventHandler.SELECTOR_PARCEL_INPUT, function(event, value, element)
        {
            service.refresh();
        });
        return this;
    };

    EventHandler.prototype.listenToItemWeightKeypress = function()
    {
        var service = this.getService();
        $(document).on('keyup change', EventHandler.SELECTOR_ITEM_WEIGHT_INPUT, function()
        {
            service.orderWeightChanged(this);
        });
        return this;
    };

    EventHandler.prototype.listenToCreateLabelButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_CREATE_LABEL_BUTTON, function()
        {
            var orderId = $(this).attr('id').replace('-shadow', '').split('_').pop();
            service.createLabelForOrder(orderId);
        });
        return this;
    };

    EventHandler.prototype.listenToPrintLabelButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_PRINT_LABEL_BUTTON, function()
        {
            var orderId = $(this).attr('id').replace('-shadow', '').split('_').pop();
            service.printLabelForOrder(orderId);
        });
        return this;
    };

    EventHandler.prototype.listenToCancelButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_CANCEL_BUTTON, function()
        {
            var orderId = $(this).attr('id').replace('-shadow', '').split('_').pop();
            service.cancelForOrder(orderId);
        });
        return this;
    };

    return EventHandler;
});