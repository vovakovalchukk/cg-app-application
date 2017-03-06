define(['cg-mustache'], function(CGMustache)
{
    function DeliveryExperience(buttonTemplatePath, disabledMessage)
    {
        var buttonTemplate;

        this.getButtonTemplatePath = function()
        {
            return buttonTemplatePath;
        };

        this.getDisabledMessage = function()
        {
            return disabledMessage;
        };

        this.getButtonTemplate = function()
        {
            return buttonTemplate;
        };

        this.setButtonTemplate = function(newButtonTemplate)
        {
            buttonTemplate = newButtonTemplate;
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
    DeliveryExperience.BLANK_SERVICE = '-';

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
        var deliveryExperienceInput = serviceContainer.closest('tr').find(DeliveryExperience.SELECTOR_DEL_EXP_SELECT + ' input');
        var nameParts = $(deliveryExperienceInput).attr('name').match(/orderData\[(.+?)\]/);
        var orderId = nameParts[1];
        var className = DeliveryExperience.SELECTOR_SERVICE_BUTTON.replace(/^\./, '');
        this.fetchButtonTemplate().then(function(result)
        {
            var buttonHtml = result.cgMustache.renderTemplate(result.template, {
                "buttons": [{
                    "title": "Request services",
                    "type": "button",
                    "id": className + '_' + orderId,
                    "class": className,
                    "disabled": deliveryExperienceInput.val() == ''
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
            var template = self.getButtonTemplate();
            if (template) {
                resolve({template: template, cgMustache: CGMustache.get()});
                return;
            }
            CGMustache.get().fetchTemplate(self.getButtonTemplatePath(), function(template, cgMustache)
            {
                resolve({template: template, cgMustache: cgMustache});
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
            }
console.log('clickety');
// TODO
        });

        return this;
    };

    DeliveryExperience.prototype.listenForOptionChanges = function()
    {
        $(document).on(
            'change',
            DeliveryExperience.SELECTOR_DEL_EXP_SELECT+', '+DeliveryExperience.SELECTOR_COURIER_PICKUP_INPUT+','+DeliveryExperience.SELECTOR_INSURANCE_INPUT,
            function()
        {
            var serviceContainer = $(this).closest('tr').find(DeliveryExperience.SELECTOR_SERVICE_CONTAINER);
            var serviceRequestButton = serviceContainer.find(DeliveryExperience.SELECTOR_SERVICE_BUTTON);
            var className = DeliveryExperience.SELECTOR_DEL_EXP_SELECT.replace(/^\./, '');
            if ($(this).hasClass(className) && serviceRequestButton.length && serviceRequestButton.hasClass('disabled')) {
                serviceRequestButton.removeClass('disabled');
                return;
            }
            if (serviceRequestButton.length) {
                return;
            }

            self.replaceServicesWithRequestButton(serviceContainer);
        });

        return this;
    };

    return DeliveryExperience;
});