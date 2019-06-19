define([
    'jquery',
    'ShippingAlias/DomManipulator',
    'AjaxRequester'
],
function(
    $,
    domManipulator,
    ajaxRequester
) {
    function ServiceChange()
    {
        var templatePath;

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.setTemplatePath = function(newTemplatePath)
        {
            templatePath = newTemplatePath;
            return this;
        };

        this.getTemplatePath = function()
        {
            return templatePath;
        };
    }

    ServiceChange.SELECTOR_SERVICE = '.shipping-services .custom-select[id^=shipping-service-custom-select-]';
    ServiceChange.SELECTOR_ALIAS_PREFIX = '#shipping-alias-';
    ServiceChange.SELECTOR_OPTIONS = '.shipping-service-options';
    ServiceChange.URI = '/settings/shipping/serviceOptions/';
    ServiceChange.TEMPLATE = '/settings/shipping/serviceOptions/';

    ServiceChange.prototype.serviceOptionsTypeMap = {
        "select": "custom-select",
        "multiselect": "custom-select-group"
    };

    ServiceChange.prototype.init = function(templatePath)
    {
        this.setTemplatePath(templatePath);
        var self = this;
        $(document).on('change', ServiceChange.SELECTOR_SERVICE, function(event, element, value)
        {

            debugger;
            var accountId = $(element).closest('.shipping-alias').find('.shipping-account input').val();

//            let accountId = event.target.querySelector('input.shipping-account-select').value;


            var aliasId = $(element).attr('id').split('-').pop();
            self.fetchServiceOptions(accountId, value)
                .then(function(response)
                {
                    self.renderServiceOptions(aliasId, response.shippingServiceOptions);
                });
        });
    };

    ServiceChange.prototype.fetchServiceOptions = function(accountId, service)
    {
        var self = this;
        return new Promise(function(resolve, reject)
        {
            self.getAjaxRequester().sendRequest(ServiceChange.URI + accountId, {service: service}, function(response)
            {
                resolve(response);
            });
        });
    };

    ServiceChange.prototype.renderServiceOptions = function(aliasId, options)
    {
        var template = null;
        if (options && typeof options == 'object') {
            template = this.getTemplateFromOptions(options);
            this.addDefaultDataToOptions(aliasId, options);
        }
        this.getDomManipulator().updateServicesOptions(aliasId, options, template);
    };

    ServiceChange.prototype.getTemplateFromOptions = function(options)
    {
        var inputType = options.inputType;
        if (this.serviceOptionsTypeMap.hasOwnProperty(inputType)) {
            inputType = this.serviceOptionsTypeMap[inputType];
        }
        return this.getTemplatePath() + 'elements/' + inputType + '.mustache';
    };

    ServiceChange.prototype.addDefaultDataToOptions = function(aliasId, options)
    {
        options.name = 'shipping-service-options-' + aliasId;
        options.id = 'shipping-service-options-' + aliasId;
        options.class = 'shipping-service-options-input';
    };

    return new ServiceChange();
});