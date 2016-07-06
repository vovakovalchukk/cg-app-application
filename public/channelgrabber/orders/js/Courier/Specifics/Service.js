define([
    './EventHandler.js',
    'AjaxRequester',
    './Mapper.js',
    './ItemParcelAssignment.js'
], function(
    EventHandler,
    ajaxRequester,
    mapper,
    ItemParcelAssignment
) {
    // Also requires global CourierSpecificsDataTable class to be present
    function Service(dataTable, courierAccountId, ipaManager)
    {
        var eventHandler;
        var delayedLabelsOrderIds;
        var orderMetaData;
        var orderData;

        this.getDataTable = function()
        {
            return dataTable;
        };

        this.getCourierAccountId = function()
        {
            return courierAccountId;
        };

        this.getItemParcelAssignmentManager = function()
        {
            return ipaManager;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.setEventHandler = function(newEventHandler)
        {
            eventHandler = newEventHandler;
            return this;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getMapper = function()
        {
            return mapper;
        };

        this.getDelayedLabelsOrderIds = function()
        {
            return delayedLabelsOrderIds;
        };

        this.setDelayedLabelsOrderIds = function(newDelayedLabelsOrderIds)
        {
            delayedLabelsOrderIds = newDelayedLabelsOrderIds;
            return this;
        };

        this.getOrderMetaData = function()
        {
            return orderMetaData;
        };

        this.setOrderMetaData = function(newOrderMetaData)
        {
            orderMetaData = newOrderMetaData;
            return this;
        };

        this.getOrderData = function()
        {
            return orderData;
        };

        this.setOrderData = function(newOrderData)
        {
            orderData = newOrderData;
            return this;
        };

        this.getNotifications = function()
        {
            return n;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this))
                .listenForTableLoad()
                .listenForTableDraw();
        };
        init.call(this);
    }

    Service.SELECTOR_NAV_FORM = '#courier-specifics-nav-form';
    Service.SELECTOR_LABEL_FORM = '#courier-specifics-label-form';
    Service.SELECTOR_ORDER_ID_INPUT = '#datatable input[name="order[]"]';
    Service.SELECTOR_ORDER_LABEL_STATUS_TPL = '#datatable input[name="orderInfo[_orderId_][labelStatus]"]';
    Service.SELECTOR_ORDER_CANCELLABLE_TPL = '#datatable input[name="orderInfo[_orderId_][cancellable]"]';
    Service.SELECTOR_ACTIONS_PREFIX = '#courier-actions-';
    Service.SELECTOR_SERVICE_PREFIX = '#courier-service-options-';
    Service.URI_CREATE_LABEL = '/orders/courier/label/create';
    Service.URI_PRINT_LABEL = '/orders/courier/label/print';
    Service.URI_CANCEL = '/orders/courier/label/cancel';
    Service.URI_READY_CHECK = '/orders/courier/label/readyCheck';
    Service.URI_SERVICE_REQ_OPTIONS = '/orders/courier/specifics/{accountId}/options';
    Service.DELAYED_LABEL_POLL_INTERVAL_MS = 5000;

    Service.prototype.listenForTableLoad = function()
    {
        var self = this;
        this.getDataTable().on('xhr', function(event, settings, response)
        {
            self.setOrderMetaData(response.metadata)
                .setOrderData(self.getMapper().dataTableRecordsToOrderData(response.Records));
        });
        return this;
    };

    Service.prototype.getMetaDataForOrder = function(orderId)
    {
        return this.getOrderMetaData()[orderId];
    };

    Service.prototype.getDataForOrder = function(orderId)
    {
        return this.getOrderData()[orderId];
    };

    Service.prototype.listenForTableDraw = function()
    {
        var self = this;
        this.getDataTable().on('fnDrawCallback', function()
        {
            self.setUpComplexElements();
        });
        return this;
    };

    Service.prototype.setUpComplexElements = function()
    {
        this.getItemParcelAssignmentManager().createInstances(this.getDataTable(), this);
        return this;
    };

    Service.prototype.courierLinkChosen = function(courierUrl)
    {
        $(Service.SELECTOR_NAV_FORM).attr('action', courierUrl).submit();
    };

    Service.prototype.serviceChanged = function(orderId, service)
    {
        var uri = Service.URI_SERVICE_REQ_OPTIONS.replace('{accountId}', this.getCourierAccountId());
        var data = {"order": orderId, "service": service};
        this.getAjaxRequester().sendRequest(uri, data, function(response)
        {
            var table = $(Service.SELECTOR_SERVICE_PREFIX + orderId).closest('table');
            for (var name in response.requiredFields) {
                var selector = 'input[name="orderData['+orderId+']['+name+']"]'
                    + ', input[name^="parcelData['+orderId+']"][name$="['+name+']"]'
                    + ', input[name^="itemData['+orderId+']"][name$="['+name+']"]';
                var elements = table.find(selector);
                if (response.requiredFields[name]) {
                    elements.removeAttr('disabled').removeClass('disabled').addClass('required');
                    if (elements.parent().hasClass('custom-select')) {
                        elements.parent().removeClass('disabled');
                    }
                } else {
                    elements.attr('disabled', 'disabled').removeClass('required').addClass('disabled');
                    if (elements.parent().hasClass('custom-select')) {
                        elements.parent().addClass('disabled');
                    }
                }
            }
        });
    };

    Service.prototype.parcelsChangedForOrder = function(orderId)
    {
        this.getItemParcelAssignmentManager().clearForOrder(orderId);
        this.refresh();
    };

    Service.prototype.refresh = function()
    {
        var inputData = this.getInputData('#datatable td input', false);
        // Using one() instead of on() as this data will change each time
        this.getDataTable().one("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
        {
            for (var count in inputData) {
                aoData.push(inputData[count]);
            }
        });
        this.getDataTable().cgDataTable('redraw');
    };

    Service.prototype.getInputData = function(selector, validate)
    {
        if (validate === undefined) {
            validate = true;
        }
        var self = this;
        var inputData = [];
        var valid = true;
        var invalidInput = null;
        $(selector).each(function()
        {
            var input = this;
            var name = $(input).attr('name');
            if (!name || (!name.match(/^orderData/) && !name.match(/^parcelData/) && !name.match(/^itemData/))) {
                return true; // continue
            }
            var value = $(input).val();
            if ($(input).attr('type') == 'checkbox') {
                value = ($(input).is(':checked') ? 1 : 0);
            }
            if (validate && !self.isInputValid(input)) {
                valid = false;
                invalidInput = input;
                return false; // break
            }
            inputData.push({
                name: name,
                value: value
            });
        });
        if (!valid) {
            this.getNotifications().error('Please complete all required fields in the correct format', true);
            this.highlightInvalidInput(invalidInput);
            return false;
        }
        return inputData;
    };

    Service.prototype.isInputValid = function(input)
    {
        var value = $(input).val();
        if ($(input).hasClass('required') && !value) {
            return false;
        }
        if (($(input).hasClass('number') || $(input).attr('type') == 'number') && parseFloat(value) === NaN) {
            return false;
        }
        if ($(input).hasClass('courier-order-collectionDate') && !value.match(/\d{2}\/\d{2}\/\d{4}/)) {
            return false;
        }
        return true;
    };

    Service.prototype.highlightInvalidInput = function(input)
    {
        var offsetTop = $(input).closest('td').get(0).offsetTop;
        document.querySelector('.dataTables_scrollBody').scrollTop = offsetTop;
        input.focus();
        return this;
    };

    Service.prototype.getInputDataForOrder = function(orderId)
    {
        var inputDataSelector = '#datatable td input[name^="orderData['+orderId+']"], ';
        inputDataSelector +=    '#datatable td input[name^="parcelData['+orderId+']"], ';
        inputDataSelector +=    '#datatable td input[name^="itemData['+orderId+']"]';
        return this.getInputData(inputDataSelector);
    };

    Service.prototype.getInputDataForOrdersOfLabelStatuses = function(labelStatuses, idsOnly, cancellableOnly)
    {
        var self = this;
        var data = {"account": this.getCourierAccountId(), "order": []};
        $(Service.SELECTOR_ORDER_ID_INPUT).each(function()
        {
            var element = this;
            var orderId = $(element).val();
            var labelStatusSelector = Service.SELECTOR_ORDER_LABEL_STATUS_TPL.replace('_orderId_', orderId);
            var labelStatus = $(labelStatusSelector).val();
            if (!labelStatuses[labelStatus] && labelStatuses.indexOf(labelStatus) == -1) {
                return true; // continue
            }
            if (cancellableOnly) {
                var cancellableSelector = Service.SELECTOR_ORDER_CANCELLABLE_TPL.replace('_orderId_', orderId);
                if (!$(cancellableSelector).val()) {
                    return true; // continue
                }
            }
            data.order.push(orderId);
            if (idsOnly) {
                return true; // continue
            }
            var orderInputData = self.getInputDataForOrder(orderId);
            if (!orderInputData) {
                data = false;
                return false; // break
            }
            var orderData = self.convertInputDataToAjaxData(orderInputData);
            for (var key in orderData) {
                data[key] = orderData[key];
            }
        });
        return data;
    };

    Service.prototype.convertInputDataToAjaxData = function(inputData)
    {
        var ajaxData = {};
        for (var count in inputData) {
            var name = inputData[count].name;
            var value = inputData[count].value;
            if (name.match(/\[\]$/)) {
                name = name.replace(/\[\]$/, '');
                if (!ajaxData.hasOwnProperty(name)) {
                    ajaxData[name] = [];
                }
                ajaxData[name].push(value);
            } else {
                ajaxData[name] = value;
            }
        }
        return ajaxData;
    };

    Service.prototype.orderWeightChanged = function(weightElement)
    {
        var orderId = this.getOrderIdFromInputElement(weightElement);
        var metaData = this.getMetaDataForOrder(orderId);
        // If there's multiple parcels we don't know how to distribute the weight
        if (metaData.parcelRowCount > 1) {
            return;
        }
        var orderClass = EventHandler.SELECTOR_ITEM_WEIGHT_INPUT.replace('.', '') + '_' + orderId;
        var sum = 0;
        $('.'+orderClass).each(function()
        {
            var weightElement = this;
            sum += parseFloat($(weightElement).val()) || 0;
        });
        // Set the summed weight as the first parcel weight
        var orderId = orderClass.split('_').pop();
        $(EventHandler.SELECTOR_ORDER_WEIGHT_INPUT_PREFIX + orderId + '-1').val(sum);
    };

    Service.prototype.getOrderIdFromInputElement = function(element)
    {
        // Most input names look like "xData[{orderId}][{item/parcelId}][{thing}]"
        return element.name.split(/[\[\]]/)[1];
    };

    Service.prototype.createLabelForOrder = function(orderId, button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var inputData = this.getInputDataForOrder(orderId);
        if (!inputData) {
            return;
        }
        $(button).addClass('disabled');
        this.getNotifications().notice('Creating label');
        var data = this.convertInputDataToAjaxData(inputData);
        data.account = this.getCourierAccountId();
        data.order = [orderId];
        this.sendCreateLabelsRequest(data);
    };

    Service.prototype.printLabelForOrder = function(orderId)
    {
        this.getNotifications().notice('Generating label', true);
        this.printLabelsForOrders([orderId]);
    };

    Service.prototype.printLabelsForOrders = function(orderIds)
    {
        $(Service.SELECTOR_LABEL_FORM + ' input[name="order[]"]').remove();
        for (var count in orderIds) {
            $('<input type="hidden" name="order[]" value="' + orderIds[count] + '" />').appendTo(Service.SELECTOR_LABEL_FORM);
        }
        $(Service.SELECTOR_LABEL_FORM).submit();
    };

    Service.prototype.cancelForOrder = function(orderId, button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        $(button).addClass('disabled');
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Cancelling');

        var data = {"account": this.getCourierAccountId(), "order": [orderId]};
        this.getAjaxRequester().sendRequest(Service.URI_CANCEL, data, function()
        {
            notifications.success('Shipping order cancelled successfully');
            self.refresh();
        }, function(response)
        {
            $(button).removeClass('disabled');
            notifications.ajaxError(response);
        });
    };

    Service.prototype.createAllLabels = function(button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var data = this.getInputDataForOrdersOfLabelStatuses(['', 'cancelled']);
        if (!data) {
            return;
        }
        $(button).addClass('disabled');
        $(EventHandler.SELECTOR_CREATE_LABEL_BUTTON).addClass('disabled');
        this.getNotifications().notice('Creating all labels');
        this.sendCreateLabelsRequest(data);
    };

    Service.prototype.sendCreateLabelsRequest = function(data)
    {
        var self = this;
        this.getAjaxRequester().sendRequest(Service.URI_CREATE_LABEL, data, function(response)
        {
            // Process the response after the table has refreshed
            self.getDataTable().one('fnDrawCallback', function()
            {
                self.processCreateLabelsResponse(response);
            });
            self.refresh();
        }, function(response)
        {
            $(EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON).removeClass('disabled');
            $(EventHandler.SELECTOR_CREATE_LABEL_BUTTON).removeClass('disabled');
            self.getNotifications().ajaxError(response);
        });
    };

    Service.prototype.processCreateLabelsResponse = function(response)
    {
        if (!response || (response.notReadyCount == 0 && response.errorCount == 0)) {
            this.getNotifications().success('Label(s) created successfully');
        } else {
            this.handleNotReadysAndErrors(response);
        }
        $(EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON).removeClass('disabled');
        $(EventHandler.SELECTOR_CREATE_LABEL_BUTTON).removeClass('disabled');
    };

    Service.prototype.handleNotReadysAndErrors = function(response)
    {
        var message = '';
        message += this.getLabelsNotReadyMessageForResponse(response);
        message += this.getLabelsErroredMessageForResponse(response, message);
        var notificationType = this.getNotificationTypeForResponse(response);
        var notifications = this.getNotifications();
        notifications[notificationType](message);
        this.updateOrderServicesFromResponse(response);
        return this;
    };

    Service.prototype.getLabelsNotReadyMessageForResponse = function(response)
    {
        var message = '';
        if (response.notReadyCount == 0) {
            return message;
        }
        if (response.readyCount == 0 && response.errorCount == 0) {
            message = 'Label create requests sent successfully, your label(s) will be ready soon, please wait.';
        } else {
            message = 'Label create requests sent successfully, ' + response.readyCount + ' label(s) are ready now, ' + response.notReadyCount + ' labels will be ready soon, please wait.';
        }
        this.setupDelayedLabelPoll(response.readyStatuses);
        return message;
    };

    Service.prototype.getLabelsErroredMessageForResponse = function(response, existingMessage)
    {
        var message = '';
        if (response.errorCount == 0) {
            return message;
        }
        if (existingMessage != '') {
            message += '<br /><br />';
        }
        message += response.partialErrorMessage;
        return message;
    };

    Service.prototype.getNotificationTypeForResponse = function(response)
    {
        var type = 'notice';
        if (response.errorCount > 0) {
            type = 'error';
        }
        return type;
    };

    Service.prototype.setupDelayedLabelPoll = function(orderLabelReadyStatuses)
    {
        var self = this;
        var delayedOrderIds = [];
        for (var orderId in orderLabelReadyStatuses) {
            if (orderLabelReadyStatuses[orderId]) {
                continue;
            }
            delayedOrderIds.push(orderId);
        }
        this.setDelayedLabelsOrderIds(delayedOrderIds);
        // Let the DataTable refresh first else if it takes a while the poll will kick in before its ready
        this.getDataTable().one('fnDrawCallback', function()
        {
            self.pollForDelayedLabels();
        });
        return this;
    };

    Service.prototype.pollForDelayedLabels = function()
    {
        var self = this;
        var delayedOrderIds = this.getDelayedLabelsOrderIds();
        var data = {"order": delayedOrderIds};
        this.getAjaxRequester().sendRequest(Service.URI_READY_CHECK, data, function(response)
        {
            var readyOrderIds = response.readyOrders;
            for (var count in readyOrderIds) {
                var orderId = readyOrderIds[count];
                var index = delayedOrderIds.indexOf(orderId);
                if (index < 0) {
                    continue;
                }
                delayedOrderIds.splice(index, 1);
                self.markOrderLabelAsReady(orderId);
            }
            self.setDelayedLabelsOrderIds(delayedOrderIds);
            if (delayedOrderIds.length == 0) {
                self.getNotifications().success('All labels are now ready');
                return;
            }
            // Still more not ready yet, set up the next poll
            setTimeout(
                function() { self.pollForDelayedLabels(); },
                Service.DELAYED_LABEL_POLL_INTERVAL_MS
            );
        });
        return this;
    };

    Service.prototype.updateOrderServicesFromResponse = function(response)
    {
        if (!response.orderServices) {
            return;
        }
        for (var orderId in response.orderServices) {
            this.updateOrderServices(orderId, response.orderServices[orderId]);
        }
    };

    Service.prototype.updateOrderServices = function(orderId, services)
    {
        var select = $(Service.SELECTOR_SERVICE_PREFIX + orderId);
        select.find('ul li').each(function()
        {
            var serviceOption = this;
            var code = serviceOption.dataset.value;
            if (services[code]) {
                return true; // continue
            }
            $(serviceOption).remove();
        });
        select.find('input[type="hidden"]').val('');
        select.find('.selected-content').text('');
    };

    Service.prototype.markOrderLabelAsReady = function(orderId)
    {
        var labelStatus = 'not printed';
        var labelStatusSelector = Service.SELECTOR_ORDER_LABEL_STATUS_TPL.replace('_orderId_', orderId);
        $(labelStatusSelector).val(labelStatus);
        var cancellableSelector = Service.SELECTOR_ORDER_CANCELLABLE_TPL.replace('_orderId_', orderId);
        var cancellable = $(cancellableSelector).val();
        var actionsForOrder = CourierSpecificsDataTable.getActionsFromLabelStatus(labelStatus, cancellable);
        var actionHtml = CourierSpecificsDataTable.getButtonsHtmlForActions(actionsForOrder, orderId);
        $(Service.SELECTOR_ACTIONS_PREFIX + orderId).html(actionHtml);
    };

    Service.prototype.printAllLabels = function()
    {
        this.getNotifications().notice('Generating all labels', true);

        var data = this.getInputDataForOrdersOfLabelStatuses(['not printed', 'printed'], true, false);
        if (!data) {
            return;
        }
        this.printLabelsForOrders(data.order);
    };

    Service.prototype.cancelAll = function(button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var data = this.getInputDataForOrdersOfLabelStatuses(['not printed', 'printed'], true, true);
        if (!data) {
            return;
        }
        $(button).addClass('disabled');
        $(EventHandler.SELECTOR_CANCEL_BUTTON).addClass('disabled');
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Cancelling all');

        this.getAjaxRequester().sendRequest(Service.URI_CANCEL, data, function()
        {
            notifications.success('Shipping orders cancelled successfully');
            self.refresh();
            $(button).removeClass('disabled');
        }, function(response)
        {
            $(button).removeClass('disabled');
            $(EventHandler.SELECTOR_CANCEL_BUTTON).removeClass('disabled');
            notifications.ajaxError(response);
        });
    };

    return Service;
});