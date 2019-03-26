define([
    'AjaxRequester',
    'cg-mustache',
    'element/loadingIndicator'
],
function(
    ajaxRequester,
    CGMustache,
    loadingIndicator
) {
    function DeliveryExperience(templatePaths, disabledMessage, noServicesMessage)
    {
        var templates;

        this.getInputDataService = function()
        {
            return inputDataService;
        };

        this.getTemplatePaths = function()
        {
            return templatePaths;
        };

        this.getDisabledMessage = function()
        {
            return disabledMessage;
        };

        this.getNoServicesMessage = function()
        {
            return noServicesMessage;
        };

        this.getTemplates = function()
        {
            return templates;
        };

        this.setTemplates = function(newTemplates)
        {
            template = newTemplates;
            return this;
        };

        this.getCourierAccountId = function()
        {
            // courierAccountId from global scope
            return courierAccountId;
        }

        var init = function()
        {
            var self = this;
            $(DeliveryExperience.SELECTOR_TABLE).on('fnDrawCallback fnRowsUpdatedCallback', function()
            {
                self.replaceBlankServicesWithRequestButtons()
                    .listenForRequestButtonClicks()
                    .listenForOptionChanges();
            });
            this.addRequestAllServicesButton();
        };
        init.call(this);
    }

    DeliveryExperience.SELECTOR_TABLE = '#datatable';
    DeliveryExperience.SELECTOR_SERVICE_CONTAINER = '.courier-service-options';
    DeliveryExperience.SELECTOR_SERVICE_CONTAINER_ID = '#courier-service-options-##orderId##';
    DeliveryExperience.SELECTOR_SERVICE_INPUT = 'input.courier-service-select';
    DeliveryExperience.SELECTOR_SERVICE_BUTTON = '.courier-service-options-request-button';
    DeliveryExperience.SELECTOR_DEL_EXP_SELECT = '.courier-delivery-experience-select';
    DeliveryExperience.SELECTOR_COURIER_PICKUP_INPUT = '.courier-pickup input[type=checkbox]';
    DeliveryExperience.SELECTOR_INSURANCE_INPUT = '.courier-insurance input[type=checkbox]';
    DeliveryExperience.SELECTOR_ORDER_INPUT = 'input[name^="orderData[##orderId##]"]';
    DeliveryExperience.SELECTOR_PARCEL_INPUT = 'input[name^="parcelData[##orderId##]"]';
    DeliveryExperience.SELECTOR_BULK_ACTIONS = '#courier-specifics-bulk-actions';
    DeliveryExperience.SELECTOR_ALL_SERVICES_BUTTON = '#request-all-services-button';
    DeliveryExperience.BLANK_SERVICE = '-';
    DeliveryExperience.LOADER = `<div class="indicator-sizer -default u-margin-center">
                                    ${loadingIndicator.getIndicator()}
                               </div>`;
    DeliveryExperience.POLL_TIMEOUT_MS = 2000;

    DeliveryExperience.prototype.replaceBlankServicesWithRequestButtons = function()
    {
        var self = this;
        $(DeliveryExperience.SELECTOR_SERVICE_INPUT).each(function ()
        {
            var input = this;
            if ($(input).val() != DeliveryExperience.BLANK_SERVICE) {
                return true; // continue
            }

            var serviceContainer = $(input).closest(DeliveryExperience.SELECTOR_SERVICE_CONTAINER);
            self.renderRequestButton(serviceContainer);
        });

        return this;
    };

    DeliveryExperience.prototype.renderRequestButton = function(serviceContainer)
    {
        var self = this;
        var orderId = this.getOrderIdFromServiceContainer(serviceContainer);
        var className = DeliveryExperience.SELECTOR_SERVICE_BUTTON.replace(/^\./, '');
        this.fetchButtonTemplate().then(function(result)
        {
            var buttonHtml = result.cgMustache.renderTemplate(result.template, {
                "buttons": [{
                    "title": "Request services",
                    "type": "button",
                    "id": className + '_' + orderId,
                    "class": className,
                    "disabled": !self.isServiceRequestAvailable(serviceContainer)
                }]
            });
            var inputHtml = self.getBlankServiceInput(orderId);
            serviceContainer.html(buttonHtml + inputHtml);
        });
    };

    DeliveryExperience.prototype.getBlankServiceInput = function(orderId)
    {
        return '<input type="hidden" name="orderData['+orderId+'][service]" class="required courier-service-select" value="" />';
    };

    DeliveryExperience.prototype.fetchButtonTemplate = function()
    {
        var self = this;
        return new Promise(function(resolve, reject)
        {
            self.fetchTemplates().then(function(result)
            {
                resolve({template: result.templates.buttons, cgMustache: result.cgMustache});
            });
        });
    };

    DeliveryExperience.prototype.fetchSelectTemplate = function()
    {
        var self = this;
        return new Promise(function(resolve, reject)
        {
            self.fetchTemplates().then(function(result)
            {
                resolve({template: result.templates.select, cgMustache: result.cgMustache});
            });
        });
    };

    DeliveryExperience.prototype.fetchTemplates = function()
    {
        var self = this;
        return new Promise(function(resolve, reject)
        {
            var templates = self.getTemplates();
            if (templates) {
                resolve({templates: templates, cgMustache: CGMustache.get()});
                return;
            }
            CGMustache.get().fetchTemplates(self.getTemplatePaths(), function(templates, cgMustache)
            {
                resolve({templates: templates, cgMustache: cgMustache});
            });
        });
    };

    DeliveryExperience.prototype.listenForRequestButtonClicks = function()
    {
        var self = this;
        $(document).on('click', DeliveryExperience.SELECTOR_SERVICE_BUTTON, function()
        {
            if ($(this).hasClass('disabled')) {
                n.notice(self.getDisabledMessage(), true);
                return;
            }
            var serviceContainer = $(this).closest(DeliveryExperience.SELECTOR_SERVICE_CONTAINER);
            self.fetchAndRenderServices(serviceContainer);
        });

        return this;
    };

    DeliveryExperience.prototype.listenForOptionChanges = function()
    {
        var self = this;
        $(document).on(
            'change',
            DeliveryExperience.SELECTOR_DEL_EXP_SELECT+', '+DeliveryExperience.SELECTOR_COURIER_PICKUP_INPUT+','+DeliveryExperience.SELECTOR_INSURANCE_INPUT+', '+DeliveryExperience.SELECTOR_TABLE + ' tr input.required',
            function()
        {
            var orderId = $(this).closest('tr').attr('id').split('_')[1];
            var serviceContainer = $(DeliveryExperience.SELECTOR_SERVICE_CONTAINER_ID.replace('##orderId##', orderId));
            if (serviceContainer.length == 0) {
                return;
            }
            var serviceRequestButton = serviceContainer.find(DeliveryExperience.SELECTOR_SERVICE_BUTTON);
            if (serviceRequestButton.length) {
                if (serviceRequestButton.hasClass('disabled') && self.isServiceRequestAvailable(serviceContainer)) {
                    serviceRequestButton.removeClass('disabled');
                }
                return;
            }

            self.renderRequestButton(serviceContainer);
        });

        return this;
    };

    DeliveryExperience.prototype.isServiceRequestAvailable = function(serviceContainer)
    {
        var available = true;
        var orderId = this.getOrderIdFromServiceContainer(serviceContainer);
        var inputDataSelector = '#datatable td input[name^="orderData['+orderId+']"], ';
        inputDataSelector +=    '#datatable td input[name^="parcelData['+orderId+']"], ';
        inputDataSelector +=    '#datatable td input[name^="itemData['+orderId+']"]';
        $(inputDataSelector).each(function()
        {
            var input = this;
            if (!$(input).hasClass('required')) {
                return true; // continue
            }
            // Obviously service is allowed to be blank at this stage
            if ($(input).attr('name') && $(input).attr('name').match(/orderData\[.+?\]\[service\]/)) {
                return true; // continue
            }
            if ($(input).val() == '') {
                available = false;
                return false; // break
            }
        });
        return available;
    };

    DeliveryExperience.prototype.fetchAndRenderServices = function(serviceContainer)
    {
        var self = this;
        var orderId = this.getOrderIdFromServiceContainer(serviceContainer);
        var servicesPromise = this.fetchShippingServices(serviceContainer);
        var templatePromise = this.fetchSelectTemplate();
        if (!servicesPromise || !templatePromise) {
            return;
        }
        serviceContainer.empty().html(DeliveryExperience.LOADER);
        Promise.all([servicesPromise, templatePromise]).then(function(results)
        {
            var shippingServices = results[0].serviceOptions;
            var template = results[1].template;
            var cgMustache = results[1].cgMustache;

            self.renderServiceSelect(template, orderId, shippingServices, cgMustache, serviceContainer);
        }, function()
        {
            n.error('There was a problem fetching the shipping services');
            self.renderRequestButton(serviceContainer);
        });
    };

    DeliveryExperience.prototype.fetchShippingServices = function(serviceContainer)
    {
        var orderId = this.getOrderIdFromServiceContainer(serviceContainer);
        var orderData = this.getInputDataForOrder(orderId);
        if (!orderData) {
            return;
        }

        return $.ajax({
            "url": "/orders/courier/services",
            "method": "POST",
            "data": {"order": orderId, "account": this.getCourierAccountId(), "orderData": orderData}
        });
    };

    DeliveryExperience.prototype.renderServiceSelect = function(template, orderId, shippingServices, cgMustache, serviceContainer)
    {
        if (!shippingServices || shippingServices.length == 0) {
            var inputHtml = this.getBlankServiceInput(orderId);
            serviceContainer.html(this.getNoServicesMessage() + inputHtml);
            return;
        }
        var selectHtml = cgMustache.renderTemplate(template, {
            "id": "courier-service-options-select-" + orderId,
            "name": "orderData[" + orderId + "][service]",
            "class": "required courier-service-select courier-service-custom-select",
            "options": shippingServices
        });
        if (shippingServices.length == 1) {
            // Keep the input, copy it to the new element
            var input = $('input[type=hidden]', selectHtml);
            input.val(shippingServices[0]['value']);
            selectHtml = $('<div><span>'+shippingServices[0]['title']+'</span></div>')
                .append(input)
                .html()
        }
        serviceContainer.html(selectHtml);
    };

    DeliveryExperience.prototype.getOrderIdFromServiceContainer = function(serviceContainer)
    {
        var deliveryExperienceInput = serviceContainer.closest('tr').find(DeliveryExperience.SELECTOR_DEL_EXP_SELECT + ' input');
        var nameParts = $(deliveryExperienceInput).attr('name').match(/orderData\[(.+?)\]/);
        return nameParts[1];
    };

    DeliveryExperience.prototype.getInputDataForOrder = function(orderId)
    {
        var inputData = this.getInputDataService().getInputDataForOrder(orderId, false);
        if (!inputData) {
            return false;
        }
        var data = this.getInputDataService().convertInputDataToAjaxData(inputData);
        var formattedData = {};
        for (var name in data) {
            var nameParts = name.match(/Data\[.+?\](\[.?\])?\[(.+?)\]/);
            formattedData[nameParts[2]] = data[name];
        }
        return formattedData;
    };

    DeliveryExperience.prototype.addRequestAllServicesButton = function()
    {
        var self = this;
        this.fetchButtonTemplate().then(function(result)
        {
            var buttonHtml = result.cgMustache.renderTemplate(result.template, {
                "buttons": [{
                    "title": "Request all services",
                    "type": "button",
                    "id": "request-all-services-button",
                    "class": "courier-request-all-services-button"
                }]
            });
            $(DeliveryExperience.SELECTOR_BULK_ACTIONS).append(buttonHtml);
            self.listenForRequestAllButtonClick();
        });
    };

    DeliveryExperience.prototype.listenForRequestAllButtonClick = function()
    {
        var self = this;
        $(DeliveryExperience.SELECTOR_ALL_SERVICES_BUTTON).click(function()
        {
            self.fetchAndRenderServicesForAll($(this).siblings('div.button'));
        });
    };

    DeliveryExperience.prototype.fetchAndRenderServicesForAll = function(button)
    {
        if ($(button).hasClass('disabled')) {
            return;
        }
        var self = this;
        var labelStatuses = ['', 'cancelled'];
        // We want to validate the form but we have to temporarily mark services as not required to get past that
        var count = this.toggleServiceButtonsRequired(false, labelStatuses);
        if (count == 0) {
            return;
        }
        var data = this.getInputDataService().getInputDataForOrdersOfLabelStatuses(labelStatuses);
        if (!data) {
            this.toggleServiceButtonsRequired(true, labelStatuses);
            return;
        }
        $(button).addClass('disabled');

        // Replace the buttons with spinners
        for (var key in data.order) {
            var orderId = data.order[key];
            var serviceContainer = $(DeliveryExperience.SELECTOR_SERVICE_CONTAINER_ID.replace('##orderId##', orderId));
            if (serviceContainer.find(DeliveryExperience.SELECTOR_SERVICE_BUTTON).length == 0) {
                continue;
            }
            this.toggleServiceLoading(true, orderId);
        }

        var servicesPromise = this.fetchShippingServicesForOrders(data);
        var templatePromise = this.fetchSelectTemplate();
        Promise.all([servicesPromise, templatePromise]).then(function(results) {
            var shippingServicesPerOrder = results[0].serviceOptions;
            var template = results[1].template;
            var cgMustache = results[1].cgMustache;

            self.processShippingServicesResponse(shippingServicesPerOrder, template, cgMustache);
        }, function()
        {
            n.error('There was a problem fetching the shipping services');
            $(button).removeClass('disabled');
            for (var key in data.order) {
                var orderId = data.order[key];
                this.toggleServiceLoading(false, orderId);
            }
        });
    };

    DeliveryExperience.prototype.toggleServiceButtonsRequired = function(toggle, labelStatuses)
    {
        var self = this;
        var count = 0;
        $(DeliveryExperience.SELECTOR_SERVICE_CONTAINER).each(function()
        {
            var serviceContainer = $(this);
            var orderId = self.getOrderIdFromServiceContainer(serviceContainer);
            var labelStatus = self.getInputDataService().getOrderLabelStatus(orderId);
            if (!labelStatuses[labelStatus] && labelStatuses.indexOf(labelStatus) == -1) {
                return true; // continue
            }
            if ($(serviceContainer).find(DeliveryExperience.SELECTOR_SERVICE_BUTTON).length == 0) {
                return true; // continue
            }
            count++;
            var input = serviceContainer.find(DeliveryExperience.SELECTOR_SERVICE_INPUT);
            if (toggle) {
                input.addClass('required');
            } else {
                input.removeClass('required');
            }
        });
        return count;
    };

    DeliveryExperience.prototype.toggleServiceLoading = function(toggle, orderId)
    {
        var serviceContainer = $(DeliveryExperience.SELECTOR_SERVICE_CONTAINER_ID.replace('##orderId##', orderId));
        if (serviceContainer.find('.custom-select').length > 0) {
            return;
        }
        if (toggle) {
            serviceContainer.empty().html(DeliveryExperience.LOADER);
        } else {
            this.renderRequestButton(serviceContainer);
        }
    };

    DeliveryExperience.prototype.fetchShippingServicesForOrders = function(data)
    {
        // Merge parcel data into order data
        var mergedData = {};
        for (var name in data) {
            var matches = name.match(/^parcelData\[(.+?)\]\[.+?\]\[(.+?)\]/);
            if (!matches) {
                mergedData[name] = data[name];
                continue;
            }
            mergedData['orderData['+matches[1]+']['+matches[2]+']'] = data[name];
        }

        mergedData.account = this.getCourierAccountId();
        return $.ajax({
            "url": "/orders/courier/servicesForOrders",
            "method": "POST",
            "data": mergedData
        });
    };

    DeliveryExperience.prototype.processShippingServicesResponse = function(shippingServicesPerOrder, template, cgMustache)
    {
        var pollOrders = [];
        var errorOrders = [];
        for (var orderId in shippingServicesPerOrder) {
            var shippingServices = shippingServicesPerOrder[orderId];
            if (shippingServices === true) {
                pollOrders.push(orderId);
                continue;
            } else if (shippingServices === false) {
                errorOrders.push(orderId);
                continue;
            }
            var serviceContainer = $(DeliveryExperience.SELECTOR_SERVICE_CONTAINER_ID.replace(/##orderId##/, orderId));
            this.renderServiceSelect(template, orderId, shippingServices, cgMustache, serviceContainer);
        }
        if (errorOrders.length > 0) {
            n.error('There was a problem retrieving services for one or more orders');
            for (var key in errorOrders) {
                var orderId = errorOrders[key];
                this.toggleServiceLoading(false, orderId);
            }
        }
        if (pollOrders.length > 0) {
            return this.pollForOrderServices(pollOrders, template, cgMustache);
        }
        $(DeliveryExperience.SELECTOR_ALL_SERVICES_BUTTON).siblings('div.button').removeClass('disabled');
    };

    DeliveryExperience.prototype.pollForOrderServices = function(orderIds, template, cgMustache)
    {
        var self = this;
        var button = $(DeliveryExperience.SELECTOR_ALL_SERVICES_BUTTON).siblings('div.button');
        setTimeout(function()
        {
            $.ajax({
                "url": "/orders/courier/checkServicesForOrders",
                "method": "POST",
                "data": {"order": orderIds, "account": self.getCourierAccountId()}
            }).then(function (response)
            {
                self.processShippingServicesResponse(response.serviceOptions, template, cgMustache);
            }, function () {
                n.error('There was a problem fetching the remaining shipping services');
                $(button).removeClass('disabled');
                for (var key in orderIds) {
                    var orderId = orderIds[key];
                    self.toggleServiceLoading(false, orderId);
                }
            });
        }, DeliveryExperience.POLL_TIMEOUT_MS);
    };

    return DeliveryExperience;
});
