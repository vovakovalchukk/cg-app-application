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
                .listenToCancelButtons()
                .listenToCreateAllLabelsButtons()
                .listenToPrintAllLabelsButtons()
                .listenToCancelAllLabelsButtons();
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
    EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON = '#create-all-labels-button';
    EventHandler.SELECTOR_PRINT_ALL_LABELS_BUTTON = '#print-all-labels-button';
    EventHandler.SELECTOR_CANCEL_ALL_LABELS_BUTTON = '#cancel-all-labels-button';

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

    EventHandler.prototype.listenToCreateAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON, function()
        {
            service.createAllLabels();
        });
        return this;
    };

    EventHandler.prototype.listenToPrintAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_PRINT_ALL_LABELS_BUTTON, function()
        {
            service.printAllLabels();
        });
        return this;
    };

    EventHandler.prototype.listenToCancelAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_CANCEL_ALL_LABELS_BUTTON, function()
        {
            service.cancelAll();
        });
        return this;
    };

    return EventHandler;
});