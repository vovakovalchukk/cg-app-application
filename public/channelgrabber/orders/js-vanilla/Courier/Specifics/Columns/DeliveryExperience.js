define(['cg-mustache'], function(CGMustache)
{
    function DeliveryExperience(templatePaths, disabledMessage)
    {
        var templates;

        this.getTemplatePaths = function()
        {
            return templatePaths;
        };

        this.getDisabledMessage = function()
        {
            return disabledMessage;
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

        var init = function()
        {
            var self = this;
            $(DeliveryExperience.SELECTOR_TABLE).on('fnDrawCallback', function()
            {
                self.replaceBlankServicesWithRequestButtons()
                    .listenForRequestButtonClicks()
                    .listenForOptionChanges();
            });
        };
        init.call(this);
    }

    DeliveryExperience.SELECTOR_TABLE = '#datatable';
    DeliveryExperience.SELECTOR_SERVICE_CONTAINER = '.courier-service-options';
    DeliveryExperience.SELECTOR_SERVICE_INPUT = 'input.courier-service-select';
    DeliveryExperience.SELECTOR_SERVICE_BUTTON = '.courier-service-options-request-button';
    DeliveryExperience.SELECTOR_DEL_EXP_SELECT = '.courier-delivery-experience-select';
    DeliveryExperience.SELECTOR_COURIER_PICKUP_INPUT = '.courier-pickup input[type=checkbox]';
    DeliveryExperience.SELECTOR_INSURANCE_INPUT = '.courier-insurance input[type=checkbox]';
    DeliveryExperience.SELECTOR_ORDER_INPUT = 'input[name^="orderData[##orderId##]"]';
    DeliveryExperience.SELECTOR_PARCEL_INPUT = 'input[name^="parcelData[##orderId##]"]';
    DeliveryExperience.BLANK_SERVICE = '-';
    DeliveryExperience.LOADER = '<img src="/cg-built/zf2-v4-ui/img/loading-transparent-21x21.gif">';

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
            self.replaceServicesWithRequestButton(serviceContainer);
        });

        return this;
    };

    DeliveryExperience.prototype.replaceServicesWithRequestButton = function(serviceContainer)
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
            var inputHtml = '<input type="hidden" name="orderData['+orderId+'][service]" class="required" value="" />';
            serviceContainer.html(buttonHtml + inputHtml);
        });
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
            self.replaceRequestButtonWithServices(serviceContainer);
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
            var serviceContainer = $(this).closest('tr').find(DeliveryExperience.SELECTOR_SERVICE_CONTAINER);
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

            self.replaceServicesWithRequestButton(serviceContainer);
        });

        return this;
    };

    DeliveryExperience.prototype.isServiceRequestAvailable = function(serviceContainer)
    {
        var available = true;
        serviceContainer.closest('tr').find('input.required').each(function()
        {
            var input = this;
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

    DeliveryExperience.prototype.replaceRequestButtonWithServices = function(serviceContainer)
    {
        serviceContainer.empty().html(DeliveryExperience.LOADER);
        var self = this;
        var orderId = this.getOrderIdFromServiceContainer(serviceContainer);
        var servicesPromise = this.fetchShippingServices(serviceContainer);
        var templatePromise = this.fetchSelectTemplate();
        Promise.all([servicesPromise, templatePromise]).then(function(results)
        {
            var shippingServices = results[0].serviceOptions;
            var template = results[1].template;
            var cgMustache = results[1].cgMustache;

            var selectHtml = cgMustache.renderTemplate(template, {
                "id": "courier-service-options-select-" + orderId,
                "name": "orderData[" + orderId + "][service]",
                "class": "required courier-service-select courier-service-custom-select",
                "options": shippingServices
            });
            serviceContainer.html(selectHtml);
        }, function()
        {
            n.error('There was a problem fetching the shipping services');
            self.replaceServicesWithRequestButton(serviceContainer);
        });
    };

    DeliveryExperience.prototype.fetchShippingServices = function(serviceContainer)
    {
        var orderId = this.getOrderIdFromServiceContainer(serviceContainer);
        var orderData = this.getOrderInputData(orderId);

        return $.ajax({
            "url": "/orders/courier/services",
            "method": "POST",
            // courierAccountId from global scope
            "data": {"order": orderId, "account": courierAccountId, "orderData": orderData}
        });
    };

    DeliveryExperience.prototype.getOrderIdFromServiceContainer = function(serviceContainer)
    {
        var deliveryExperienceInput = serviceContainer.closest('tr').find(DeliveryExperience.SELECTOR_DEL_EXP_SELECT + ' input');
        var nameParts = $(deliveryExperienceInput).attr('name').match(/orderData\[(.+?)\]/);
        return nameParts[1];
    };

    DeliveryExperience.prototype.getOrderInputData = function(orderId)
    {
        var inputData = {};
        var orderDataSelector = DeliveryExperience.SELECTOR_ORDER_INPUT.replace('##orderId##', orderId);
        var parcelDataSelector = DeliveryExperience.SELECTOR_PARCEL_INPUT.replace('##orderId##', orderId);
        $(DeliveryExperience.SELECTOR_TABLE + ' td ' + orderDataSelector + ', ' +
          DeliveryExperience.SELECTOR_TABLE + ' td ' + parcelDataSelector).each(function()
        {
            var input = this;
            var name = $(input).attr('name');
            var nameParts = name.match(/Data\[.+?\](\[.?\])?\[(.+?)\]/);
            var value = $(input).val();
            if ($(input).attr('type') == 'checkbox') {
                value = ($(input).is(':checked') ? 1 : 0);
            }

            inputData[nameParts[2]] = value;
        });
        return inputData;
    };

    return DeliveryExperience;
});