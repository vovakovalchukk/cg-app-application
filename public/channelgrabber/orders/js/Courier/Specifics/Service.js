define(['./EventHandler.js', 'AjaxRequester'], function(EventHandler, ajaxRequester)
{
    function Service(dataTable, courierAccountId)
    {
        var eventHandler;

        this.getDataTable = function()
        {
            return dataTable;
        };

        this.getCourierAccountId = function()
        {
            return courierAccountId;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.setEventHandler = function(newEventHandler)
        {
            eventHandler = newEventHandler;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getNotifications = function()
        {
            return n;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
        };
        init.call(this);
    }

    Service.SELECTOR_NAV_FORM = '#courier-specifics-nav-form';
    Service.SELECTOR_LABEL_FORM = '#courier-specifics-label-form';
    Service.SELECTOR_ORDER_ID_INPUT = '#datatable input[name="order[]"]';
    Service.URI_CREATE_LABEL = '/orders/courier/label/create';
    Service.URI_PRINT_LABEL = '/orders/courier/label/print';
    Service.URI_CANCEL = '/orders/courier/label/cancel';

    Service.prototype.courierLinkChosen = function(courierUrl)
    {
        $(Service.SELECTOR_NAV_FORM).attr('action', courierUrl).submit();
    };

    Service.prototype.refresh = function()
    {
        var inputData = this.getInputData('#datatable td input');
        // Using one() instead of on() as this data will change each time
        this.getDataTable().one("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
        {
            for (var count in inputData) {
                aoData.push(inputData[count]);
            }
        });
        this.getDataTable().cgDataTable('redraw');
    };

    Service.prototype.getInputData = function(selector)
    {
        var inputData = [];
        $(selector).each(function()
        {
            var input = this;
            var name = $(input).attr('name');
            if (!name || (!name.match(/^orderData/) && !name.match(/^parcelData/))) {
                return true; // continue
            }
            var value = $(input).val();
            if ($(input).attr('type') == 'checkbox') {
                value = ($(input).is(':checked') ? 1 : 0);
            }
            inputData.push({
                name: name,
                value: value
            });
        });
        return inputData;
    };

    Service.prototype.getInputDataForOrder = function(orderId)
    {
        var inputDataSelector = '#datatable td input[name^="orderData['+orderId+']"], #datatable td input[name^="parcelData['+orderId+']"]';
        return this.getInputData(inputDataSelector);
    };

    Service.prototype.convertInputDataToAjaxData = function(inputData)
    {
        var ajaxData = {};
        for (var count in inputData) {
            var name = inputData[count].name;
            var value = inputData[count].value;
            ajaxData[name] = value;
        }
        return ajaxData;
    };

    Service.prototype.orderWeightChanged = function(weightElement)
    {
        var prefix = EventHandler.SELECTOR_ITEM_WEIGHT_INPUT.replace('.', '') + '_';
        var prefixRegex = new RegExp('^'+prefix);
        var cssClasses = weightElement.className.split(' ');
        var orderClass = '';
        for (var count in cssClasses) {
            if (!cssClasses[count].match(prefixRegex)) {
                continue;
            }
            orderClass = cssClasses[count];
        }
        if (!orderClass) {
            return;
        }
        var sum = 0;
        $('.'+orderClass).each(function()
        {
            var weightElement = this;
            sum += parseFloat($(weightElement).val()) || 0;
        });
        var orderId = orderClass.replace(prefix, '');
        $(EventHandler.SELECTOR_ORDER_WEIGHT_INPUT_PREFIX + orderId + '-1').val(sum);
    };

    Service.prototype.createLabelForOrder = function(orderId)
    {
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Creating label');

        var inputData = this.getInputDataForOrder(orderId);
        var data = this.convertInputDataToAjaxData(inputData);
        data.account = this.getCourierAccountId();
        data.order = [orderId];

        this.getAjaxRequester().sendRequest(Service.URI_CREATE_LABEL, data, function()
        {
            notifications.success('Label created successfully');
            self.refresh();
        });
    };

    Service.prototype.printLabelForOrder = function(orderId)
    {
        this.getNotifications().notice('Generating label', true);

        $(Service.SELECTOR_LABEL_FORM + ' input[name="order"]').val(orderId);
        $(Service.SELECTOR_LABEL_FORM + ' input[name="account"]').val(this.getCourierAccountId());
        $(Service.SELECTOR_LABEL_FORM).submit();
    };

    Service.prototype.cancelForOrder = function(orderId)
    {
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Cancelling');

        var data = {"account": this.getCourierAccountId(), "order": orderId};
        this.getAjaxRequester().sendRequest(Service.URI_CANCEL, data, function()
        {
            notifications.success('Shipping order cancelled successfully');
            self.refresh();
        });
    };

    Service.prototype.createAllLabels = function()
    {
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Creating labels');

        var data = {"account": this.getCourierAccountId(), "order": []};
        $(Service.SELECTOR_ORDER_ID_INPUT).each(function()
        {
            var element = this;
            var orderId = $(element).val();
            data.order.push(orderId);
            var orderInputData = self.getInputDataForOrder(orderId);
            var orderData = self.convertInputDataToAjaxData(orderInputData);
            for (var key in orderData) {
                data[key] = orderData[key];
            }
        });

        this.getAjaxRequester().sendRequest(Service.URI_CREATE_LABEL, data, function()
        {
            notifications.success('Labels created successfully');
            self.refresh();
        });
    };

    return Service;
});