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
                .listenToExportLabelButtons()
                .listenToPrintLabelButtons()
                .listenToCancelButtons()
                .listenToDispatchButtons()
                .listenToFetchRatesButtons()
                .listenToCreateAllLabelsButtons()
                .listenToExportAllLabelsButtons()
                .listenToPrintAllLabelsButtons()
                .listenToCancelAllLabelsButtons()
                .listenToDispatchAllLabelsButtons()
                .listenToFetchAllRatesButtons()
                .listenToNextCourierButton()
                .listenForServiceChange();
        };
        init.call(this);
    }

    EventHandler.SELECTOR_NAV_LINKS = 'a.courier-specifics-nav-link';
    EventHandler.SELECTOR_PARCEL_INPUT = '.courier-parcels .courier-parcels-input';
    EventHandler.SELECTOR_ITEM_WEIGHT_INPUT = '.courier-item-weight';
    EventHandler.SELECTOR_ORDER_WEIGHT_INPUT_PREFIX = '#courier-order-weight-';
    EventHandler.SELECTOR_ORDER_LABEL_COST_INPUT_PREFIX = '#courier-parcel-cost-';
    EventHandler.SELECTOR_CREATE_LABEL_BUTTON = '.courier-create-label-button';
    EventHandler.SELECTOR_EXPORT_LABEL_BUTTON = '.courier-export-label-button';
    EventHandler.SELECTOR_PRINT_LABEL_BUTTON = '.courier-print-label-button';
    EventHandler.SELECTOR_CANCEL_BUTTON = '.courier-cancel-label-button';
    EventHandler.SELECTOR_DISPATCH_BUTTON = '.courier-dispatch-label-button';
    EventHandler.SELECTOR_FETCH_RATES_BUTTON = '.courier-fetch-rates-button';
    EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON = '#create-all-labels-button-shadow';
    EventHandler.SELECTOR_EXPORT_ALL_LABELS_BUTTON = '#export-all-labels-button-shadow';
    EventHandler.SELECTOR_PRINT_ALL_LABELS_BUTTON = '#print-all-labels-button-shadow';
    EventHandler.SELECTOR_CANCEL_ALL_LABELS_BUTTON = '#cancel-all-labels-button-shadow';
    EventHandler.SELECTOR_DISPATCH_ALL_LABELS_BUTTON = '#dispatch-all-labels-button-shadow';
    EventHandler.SELECTOR_FETCH_ALL_RATES_BUTTON = '#fetchrates-all-labels-button-shadow';
    EventHandler.SELECTOR_NEXT_COURIER_BUTTON = '#next-courier-button';
    EventHandler.SELECTOR_SERVICE_SELECT = '.courier-service-select';

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
            var orderId = $(element).attr('id').split('_').pop();
            service.parcelsChangedForOrder(orderId);
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
            var button = this;
            var orderId = $(button).attr('id').replace('-shadow', '').split('_').pop();
            service.createLabelForOrder(orderId, button);
        });
        return this;
    };

    EventHandler.prototype.listenToExportLabelButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_EXPORT_LABEL_BUTTON, function()
        {
            var button = this;
            var orderId = $(button).attr('id').replace('-shadow', '').split('_').pop();
            service.exportOrder(orderId, button);
        });
        return this;
    };

    EventHandler.prototype.listenToPrintLabelButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_PRINT_LABEL_BUTTON, function()
        {
            var button = this;
            var orderId = $(button).attr('id').replace('-shadow', '').split('_').pop();
            service.printLabelForOrder(orderId);
        });
        return this;
    };

    EventHandler.prototype.listenToCancelButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_CANCEL_BUTTON, function()
        {
            var button = this;
            var orderId = $(button).attr('id').replace('-shadow', '').split('_').pop();
            service.cancelForOrder(orderId, button);
        });
        return this;
    };

    EventHandler.prototype.listenToDispatchButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_DISPATCH_BUTTON, function()
        {
            var button = this;
            var orderId = $(button).attr('id').replace('-shadow', '').split('_').pop();
            service.dispatchForOrder(orderId, button);
        });
        return this;
    };

    EventHandler.prototype.listenToFetchRatesButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_FETCH_RATES_BUTTON, function()
        {
            var button = this;
            var orderId = $(button).attr('id').replace('-shadow', '').split('_').pop();
            service.fetchRatesForOrder(orderId, button);
        });
        return this;
    };

    EventHandler.prototype.listenToCreateAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON, function()
        {
            var button = this;
            service.createAllLabels(button);
        });
        return this;
    };

    EventHandler.prototype.listenToExportAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_EXPORT_ALL_LABELS_BUTTON, function()
        {
            var button = this;
            service.exportAll(button);
        });
        // service.refresh();
        return this;
    };

    EventHandler.prototype.listenToPrintAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_PRINT_ALL_LABELS_BUTTON, function()
        {
            var button = this;
            service.printAllLabels();
        });
        return this;
    };

    EventHandler.prototype.listenToCancelAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_CANCEL_ALL_LABELS_BUTTON, function()
        {
            var button = this;
            service.cancelAll(button);
        });
        return this;
    };

    EventHandler.prototype.listenToDispatchAllLabelsButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_DISPATCH_ALL_LABELS_BUTTON, function()
        {
            var button = this;
            service.dispatchAll(button);
        });
        return this;
    };

    EventHandler.prototype.listenToFetchAllRatesButtons = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_FETCH_ALL_RATES_BUTTON, function()
        {
            var button = this;
            service.fetchAllRates(button);
        });
        return this;
    };

    EventHandler.prototype.listenToNextCourierButton = function()
    {
        var service = this.getService();
        $(document).on('click', EventHandler.SELECTOR_NEXT_COURIER_BUTTON, function()
        {
            var button = this;
            service.courierLinkChosen(button.dataset.action);
        });
        return this;
    };

    EventHandler.prototype.listenForServiceChange = function()
    {
        var service = this.getService();
        $(document).on('change', EventHandler.SELECTOR_SERVICE_SELECT, function(event, element, value)
        {
            if (value === undefined) {
                return;
            }
            var orderId = $(element).data('elementName').match(/^orderData\[(.+?)\]/)[1];
            service.serviceChanged(orderId, value);
        });
        return this;
    };

    return EventHandler;
});