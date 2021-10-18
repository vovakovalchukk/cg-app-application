define([
    './EventHandler.js',
    'AjaxRequester',
    './Mapper.js',
    './InputData.js',
    './ItemParcelAssignment.js',
    '../ShippingServices.js',
    './Storage.js'
], function(
    EventHandler,
    ajaxRequester,
    mapper,
    inputDataService,
    ItemParcelAssignment,
    shippingServices,
    storage
) {
    // Also requires global CourierSpecificsDataTable class to be present
    function Service(dataTable, courierAccountId, ipaManager, balanceService)
    {
        var eventHandler;
        var delayedLabelsOrderIds;
        var orderMetaData;
        var orderData;
        var labelCosts;

        this.getDataTable = function()
        {
            return dataTable;
        };

        this.getBalanceService = function()
        {
          return balanceService;
        };

        this.getCourierAccountId = function()
        {
            return courierAccountId;
        };

        this.getItemParcelAssignmentManager = function()
        {
            return ipaManager;
        };

        this.getShippingServices = function()
        {
            return shippingServices;
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

        this.getInputDataService = function()
        {
            return inputDataService;
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

        this.getLabelCosts = function()
        {
            return labelCosts;
        };

        this.setLabelCosts = function(newLabelCosts)
        {
            labelCosts = newLabelCosts;
            return this;
        };

        this.getNotifications = function()
        {
            return n;
        };

        this.getStorage = function()
        {
            return storage;
        };

        this.store = function(key, value)
        {
            this.getStorage().set(key, value);
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this))
                .listenForTableLoad()
                .listenForTableDraw();
        };
        init.call(this);
    }

    Service.URI_CREATE_LABEL = '/orders/courier/label/create';
    Service.URI_EXPORT = '/orders/courier/label/export';
    Service.URI_PRINT_LABEL = '/orders/courier/label/print';
    Service.URI_CANCEL = '/orders/courier/label/cancel';
    Service.URI_DISPATCH = '/orders/courier/label/dispatch';
    Service.URI_READY_CHECK = '/orders/courier/label/readyCheck';
    Service.URI_FETCH_RATES = '/orders/courier/label/fetchRates';
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
        $(CourierSpecificsDataTable.SELECTOR_NAV_FORM).attr('action', courierUrl).submit();
    };

    Service.prototype.serviceChanged = function(orderId, service)
    {
        var uri = Service.URI_SERVICE_REQ_OPTIONS.replace('{accountId}', this.getCourierAccountId());
        var data = {"order": orderId, "service": service};
        this.getAjaxRequester().sendRequest(uri, data, function(response)
        {
            var table = $(CourierSpecificsDataTable.SELECTOR_SERVICE_PREFIX + orderId).closest('table');
            for (var name in response.requiredFields) {
                var selector = 'input[name="orderData['+orderId+']['+name+']"]'
                    + ', input[name^="parcelData['+orderId+']"][name$="['+name+']"]'
                    + ', input[name^="itemData['+orderId+']"][name$="['+name+']"]';
                var elements = table.find(selector);
                if (response.requiredFields[name].show) {
                    elements.removeAttr('disabled').removeClass('disabled').addClass('required');
                    if (elements.parent().hasClass('custom-select')) {
                        elements.parent().removeClass('disabled');
                    }
                    if (response.requiredFields[name].required) {
                        elements.addClass('required');
                    } else {
                        elements.removeClass('required');
                    }
                    elements.each(function()
                    {
                        if ($(this).data('placeholder')) {
                            $(this).attr('placeholder', $(this).data('placeholder'));
                        }
                    });
                } else {
                    elements.attr('disabled', 'disabled').removeClass('required').addClass('disabled');
                    if (elements.parent().hasClass('custom-select')) {
                        elements.parent().addClass('disabled');
                    }
                    elements.each(function()
                    {
                        $(this).data('placeholder', $(this).attr('placeholder'));
                        $(this).attr('placeholder', 'N/A');
                    });
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
        var inputData = this.getInputDataService().getInputData('#datatable td input', false);
        // Using one() instead of on() as this data will change each time
        this.getDataTable().one("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
        {
            for (var count in inputData) {
                aoData.push(inputData[count]);
            }
        });
        this.getDataTable().cgDataTable('redraw');
    };

    Service.prototype.refreshRowsWithData = function(records)
    {
        this.getDataTable().trigger('fnPreRowsUpdatedCallback');
        for (var count in records) {
            var record = records[count];
            var rowId = this.getRowIdFromRecord(record);
            if (!rowId) {
                continue;
            }
            var tr = $('#'+rowId).get(0);
            var dataTable = this.getDataTable().dataTable();
            var position = dataTable.fnGetPosition(tr);
            dataTable.fnUpdate(record, position, undefined, false, false);
            // fnUpdate() doesnt automatically trigger fnRowCallback which some of our other code depends on
            this.getDataTable().trigger('fnRowCallback', [tr, record]);
        }
        this.getDataTable().trigger('fnRowsUpdatedCallback');
    };

    Service.prototype.getRowIdFromRecord = function(record)
    {
        if (record.orderRow) {
            return 'courier-order-row_'+record.orderId;
        }
        if (record.itemRow) {
            return 'courier-item-row_'+record.itemId;
        }
        if (record.parcelRow) {
            return 'courier-parcel-row_'+record.orderId+'_'+record.parcelNumber;
        }
        // Unexpected
        return null;
    };

    Service.prototype.getInputDataForOrdersOfLabelStatuses = function(labelStatuses, idsOnly, cancellableOnly)
    {
        var data = this.getInputDataService().getInputDataForOrdersOfLabelStatuses(labelStatuses, idsOnly, cancellableOnly);
        data.account = this.getCourierAccountId();
        return data;
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
        var inputData = this.getInputDataService().getInputDataForOrder(orderId);
        if (!inputData) {
            return;
        }
        $(button).addClass('disabled');
        this.getNotifications().notice('Creating label');
        var data = this.getInputDataService().convertInputDataToAjaxData(inputData);
        data.account = this.getCourierAccountId();
        data.order = [orderId];
        this.sendCreateLabelsRequest(data, button);
    };

    Service.prototype.exportOrder = function(orderId, button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var inputData = this.getInputDataService().getInputDataForOrder(orderId);
        if (!inputData) {
            return;
        }
        $(button).addClass('disabled');
        this.getNotifications().notice('Exporting', true);
        var data = this.getInputDataService().convertInputDataToAjaxData(inputData);
        data.account = this.getCourierAccountId();
        data.order = [orderId];
        this.sendExportRequest(data);
    };

    Service.prototype.printLabelForOrder = function(orderId)
    {
        this.getNotifications().notice('Generating label', true);
        this.printLabelsForOrders([orderId]);
    };

    Service.prototype.printLabelsForOrders = function(orderIds)
    {
        $(CourierSpecificsDataTable.SELECTOR_LABEL_FORM + ' input[name="order[]"]').remove();
        for (var count in orderIds) {
            $('<input type="hidden" name="order[]" value="' + orderIds[count] + '" />').appendTo(CourierSpecificsDataTable.SELECTOR_LABEL_FORM);
        }
        $(CourierSpecificsDataTable.SELECTOR_LABEL_FORM).submit();
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
        this.getAjaxRequester().sendRequest(Service.URI_CANCEL, data, function(response)
        {
            notifications.success('Shipping order cancelled successfully');
            self.refreshRowsWithData(response.Records);
        }, function(response)
        {
            $(button).removeClass('disabled');
            notifications.ajaxError(response);
        });
    };

    Service.prototype.dispatchForOrder = function(orderId, button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        $(button).addClass('disabled');
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Dispatching');

        var data = {"account": this.getCourierAccountId(), "order": [orderId]};
        this.getAjaxRequester().sendRequest(Service.URI_DISPATCH, data, function(response)
        {
            notifications.success('Shipping order dispatched successfully');
            self.refreshRowsWithData(response.Records);
        }, function(response)
        {
            $(button).removeClass('disabled');
            notifications.ajaxError(response);
        });
    };

    Service.prototype.fetchRatesForOrder = function(orderId, button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var inputData = this.getInputDataService().getInputDataForOrder(orderId);
        if (!inputData) {
            return;
        }
        $(button).addClass('disabled');
        this.getNotifications().notice('Fetching rates', true);
        var data = this.getInputDataService().convertInputDataToAjaxData(inputData);
        data.account = this.getCourierAccountId();
        data.order = [orderId];
        this.sendFetchRatesRequest(data);
    };

    Service.prototype.createAllLabels = function(button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var data = this.getInputDataForOrdersOfLabelStatuses(['', 'cancelled', 'rates fetched']);
        if (!data) {
            return;
        }
        $(button).addClass('disabled');
        $(EventHandler.SELECTOR_CREATE_LABEL_BUTTON).addClass('disabled');
        this.getNotifications().notice('Creating all labels');
        this.sendCreateLabelsRequest(data, button);
    };

    Service.prototype.exportAll = function(button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var data = this.getInputDataForOrdersOfLabelStatuses(['', 'exported']);
        if (!data) {
            return;
        }
        $(button).addClass('disabled');
        $(EventHandler.SELECTOR_EXPORT_LABEL_BUTTON).addClass('disabled');
        this.getNotifications().notice('Exporting all', true);
        this.sendExportRequest(data);
        this.exportRefresh();
    };

    Service.prototype.exportRefresh = function ()
    {
        let self = this;
        let i = 0;
        let maxAttempts = 1;

        $(document).ajaxStop(function() {
            if (i > maxAttempts) {
                return;
            }
            i++;
            self.refresh();
        });
    }

    Service.prototype.sendCreateLabelsRequest = function(data, button)
    {
        var self = this;
        this.getAjaxRequester().sendRequest(Service.URI_CREATE_LABEL, data, function(response)
        {
            if (response.Records) {
                self.refreshRowsWithData(response.Records);
            }
            self.processCreateLabelsResponse(response, button);
        }, function(response)
        {
            $(EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON).removeClass('disabled');
            $(EventHandler.SELECTOR_CREATE_LABEL_BUTTON).removeClass('disabled');
            self.getNotifications().ajaxError(response);
            // Refresh the table in case some orders did process to prevent double creation
            self.refresh();
        });
    };

    Service.prototype.sendExportRequest = function(data)
    {
        let self = this;
        var formHtml = '<form method="POST" action="' + Service.URI_EXPORT + '">';
        for (var name in data) {
            if (!data.hasOwnProperty(name)) {
                continue;
            }

            var values = data[name];
            if (values instanceof Array) {
                for (var value in values) {
                    if (!values.hasOwnProperty(value)) {
                        continue;
                    }
                    formHtml += '<input name="' + name + '[]" value="' + values[value] + '">';
                }
            } else {
                formHtml += '<input name="' + name + '" value="' + values + '">';
            }
        }
        formHtml += '</form>';
        $(formHtml).appendTo('body').submit().remove();
        self.refresh();
    };

    Service.prototype.processCreateLabelsResponse = function(response, button)
    {
        if (!response || (response.notReadyCount == 0 && response.errorCount == 0)) {
            this.updateBalance(response);
            this.getNotifications().success('Label(s) created successfully');
        } else {
            this.handleNotReadysAndErrors(response, button);
        }
        $(EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON).removeClass('disabled');
        $(EventHandler.SELECTOR_CREATE_LABEL_BUTTON).removeClass('disabled');
    };

    Service.prototype.handleNotReadysAndErrors = function(response, button)
    {
        if (response.topupRequired) {
            this.showBalanceTopUpPopUp(button);
            return;
        }

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
        var select = $(CourierSpecificsDataTable.SELECTOR_SERVICE_PREFIX + orderId);
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

    Service.prototype.markOrderLabelAsReady = function(orderId, labelStatus)
    {
        labelStatus = labelStatus || CourierSpecificsDataTable.LABEL_STATUS_DEFAULT;

        var actionAvailability = CourierSpecificsDataTable.getActionsAvailabilityFromLabelStatus(orderId, labelStatus);

        var actionsForOrder = CourierSpecificsDataTable.getActionsFromLabelStatus(
            labelStatus, actionAvailability.exportable, actionAvailability.cancellable, actionAvailability.dispatchable, actionAvailability.rateable, actionAvailability.creatable
        );
        var actionHtml = CourierSpecificsDataTable.getButtonsHtmlForActions(actionsForOrder, orderId);
        $(CourierSpecificsDataTable.SELECTOR_ACTIONS_PREFIX + orderId).html(actionHtml);
    };

    Service.prototype.fetchAllRates = function(button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var data = this.getInputDataForOrdersOfLabelStatuses(['', 'cancelled']);
        if (!data) {
            return;
        }
        $(button).addClass('disabled');
        $(EventHandler.SELECTOR_FETCH_ALL_RATES_BUTTON).addClass('disabled');
        this.getNotifications().notice('Fetching all rates', true);
        this.sendFetchRatesRequest(data);
    };

    Service.prototype.sendFetchRatesRequest = function(data)
    {
        var self = this;
        this.getAjaxRequester().sendRequest(Service.URI_FETCH_RATES, data, function(response)
        {
            if (response.Records) {
                self.refreshRowsWithData(response.Records);
            }
            self.processFetchRatesResponse(response);
        }, function(response)
        {
            $(EventHandler.SELECTOR_FETCH_ALL_RATES_BUTTON).removeClass('disabled');
            $(EventHandler.SELECTOR_FETCH_RATES_BUTTON).removeClass('disabled');
            self.getNotifications().ajaxError(response);
        });
    };

    Service.prototype.processFetchRatesResponse = function(response)
    {
        if (!response.rates || response.rates.length == 0) {
            return this.handleRatesError(response);
        }
        for (var orderId in response.rates) {
            var orderRates = response.rates[orderId];
            var select = $(CourierSpecificsDataTable.SELECTOR_SERVICE_PREFIX + orderId);
            var input = select.find('input[type=hidden]');
            var selectedService = input.val();
            var serviceOptions = this.mapShippingRatesToShippingOptions(orderRates, selectedService);
            this.getShippingServices().loadServicesSelectForOrderAndServices(orderId, serviceOptions, input.attr('name'));

            var showServiceWarning = false;
            if (serviceOptions.length > 1) {
                showServiceWarning = true;
                for (key in serviceOptions) {
                    if (serviceOptions[key].selected === true) {
                        showServiceWarning = false;
                        break;
                    }
                }
            }

            if (showServiceWarning) {
                this.getNotifications().notice('The service you requested is unavailable, please select an alternative');
            }

            $(CourierSpecificsDataTable.SELECTOR_ORDER_CREATABLE_TPL.replace('_orderId_', orderId)).val(1);
            this.markOrderLabelAsReady(orderId, CourierSpecificsDataTable.LABEL_STATUS_RATES_FETCHED);
            $(EventHandler.SELECTOR_CREATE_ALL_LABELS_BUTTON).show();
            $(EventHandler.SELECTOR_FETCH_ALL_RATES_BUTTON).hide();
        }
        this.recordLabelCostsFromRatesResponse(response.rates);
        $(EventHandler.SELECTOR_FETCH_ALL_RATES_BUTTON).removeClass('disabled');
        $(EventHandler.SELECTOR_FETCH_RATES_BUTTON).removeClass('disabled');

        if (serviceOptions.length === 1) {
            this.handleSingleRateResponse(orderId, serviceOptions);
        }
    };

    Service.prototype.mapShippingRatesToShippingOptions = function(orderRates, selectedService)
    {
        var serviceOptions = [];
        for (var index in orderRates) {
            serviceOptions.push({
                value: orderRates[index].id,
                title: orderRates[index].name,
                selected: orderRates[index].serviceCode == selectedService
            });
        }
        return serviceOptions;
    };

    Service.prototype.handleRatesError = function(response)
    {
        var error = 'There was a problem fetching the shipping rates. Please contact support if this continues.';
        if (response.errors && response.errors.length > 0) {
            error = '<p>Some problems were encountered when fetching the rates:</p>';
            error += '<ul><li>' + response.errors.join('</li><li>') + '</li></ul>';
        }
        this.getNotifications().error(error);
        $(EventHandler.SELECTOR_FETCH_ALL_RATES_BUTTON).removeClass('disabled');
        $(EventHandler.SELECTOR_FETCH_RATES_BUTTON).removeClass('disabled');
    };

    Service.prototype.recordLabelCostsFromRatesResponse = function(rates)
    {
        var labelCosts = {};
        for (var orderId in rates) {
            labelCosts[orderId] = {};
            var orderRates = rates[orderId];
            for (var index in orderRates) {
                var rate = orderRates[index];
                labelCosts[orderId][rate.id] = {
                    cost: rate.cost,
                    currencyCode: rate.currencyCode
                };
            }
        }
        this.store('labelCosts', labelCosts);
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
        var data = this.getInputDataForOrdersOfLabelStatuses(['not printed', 'printed', 'exported'], true, true);
        if (!data) {
            return;
        }
        $(button).addClass('disabled');
        $(EventHandler.SELECTOR_CANCEL_BUTTON).addClass('disabled');
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Cancelling all');

        this.getAjaxRequester().sendRequest(Service.URI_CANCEL, data, function(response)
        {
            notifications.success('Shipping orders cancelled successfully');
            self.refreshRowsWithData(response.Records);
            $(button).removeClass('disabled');
        }, function(response)
        {
            $(button).removeClass('disabled');
            $(EventHandler.SELECTOR_CANCEL_BUTTON).removeClass('disabled');
            notifications.ajaxError(response);
            self.refresh();
        });
    };

    Service.prototype.dispatchAll = function(button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var data = this.getInputDataForOrdersOfLabelStatuses(['not printed', 'printed'], true, true);
        if (!data) {
            return;
        }
        $(button).addClass('disabled');
        $(EventHandler.SELECTOR_DISPATCH_BUTTON).addClass('disabled');
        var self = this;
        var notifications = this.getNotifications();
        notifications.notice('Dispatching all');

        this.getAjaxRequester().sendRequest(Service.URI_DISPATCH, data, function(response)
        {
            notifications.success('Shipping orders dispatched successfully');
            self.refreshRowsWithData(response.Records);
            $(button).removeClass('disabled');
        }, function(response)
        {
            $(button).removeClass('disabled');
            $(EventHandler.SELECTOR_CANCEL_BUTTON).removeClass('disabled');
            notifications.ajaxError(response);
            self.refresh();
        });
    };

    Service.prototype.showBalanceTopUpPopUp = function(button)
    {
        this.getNotifications().clearNotifications();
        var additionalPopupSettings = {
            "title": "Insufficient Funds",
            "labelCreateButtonClicked": $(button).attr('id')
        };
        this.getBalanceService().renderPopup(additionalPopupSettings);
    };

    Service.prototype.updateBalance = function(data)
    {
        if (data.balance !== undefined) {
            $(CourierSpecificsDataTable.SELECTOR_ACCOUNT_BALANCE_FIGURE).text(data.balance.toFixed(2));
        }
    }

    Service.prototype.handleSingleRateResponse = function(orderId, serviceOptions)
    {
        var orderRow = CourierSpecificsDataTable.SELECTOR_ORDER_ROW_TPL.replace('{orderId}', orderId);
        var labelCosts = this.getStorage().get("labelCosts");
        var selectedService = serviceOptions[0].value;
        var labelCost = labelCosts[orderId][selectedService].cost;
        $(orderRow).val(labelCost);
        $(CourierSpecificsDataTable.SELECTOR_CURRENCY_SYMBOL_DISPLAY).removeClass('hidden');
        $(CourierSpecificsDataTable.SELECTOR_TOTAL_ORDER_LABEL_COST).text(labelCost.toFixed(2));
        $(orderRow).find($(CourierSpecificsDataTable.SELECTOR_COST_COLUMN_INPUT)).val(labelCost);
    }

    return Service;
});