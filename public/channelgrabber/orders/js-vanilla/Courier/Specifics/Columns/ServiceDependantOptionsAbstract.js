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
    function ServiceDependantOptionsAbstract(templatePath)
    {
        var template;

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getTemplatePath = function()
        {
            return templatePath;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
            return this;
        };
    }

    ServiceDependantOptionsAbstract.SELECTOR_SERVICE_SELECT = '.courier-service-custom-select';
    ServiceDependantOptionsAbstract.SELECTOR_ACCOUNT_INPUT = '#courier-specifics-label-form input';
    ServiceDependantOptionsAbstract.URI = '/orders/courier/specifics/{accountId}/optionData';
    ServiceDependantOptionsAbstract.LOADER = `<div class="indicator-wrapper -default u-margin-center">
                                                ${loadingIndicator.getIndicator()}
                                           </div>`;

    ServiceDependantOptionsAbstract.prototype.listenForServiceChanges = function()
    {
        var self = this;
        $(document).on('change', ServiceDependantOptionsAbstract.SELECTOR_SERVICE_SELECT, function(event, element, value)
        {
            var orderId = $(element).data('elementName').match(/^orderData\[(.+?)\]/)[1];
            self.updateOptionsForOrder(orderId, value);
        });
        return this;
    };

    ServiceDependantOptionsAbstract.prototype.updateOptionsForOrder = function(orderId, service)
    {
        if (this.preventUpdateOptions(orderId)) {
            return;
        }
        var self = this;
        var selected = this.getSelectedValue(orderId);
        var container = this.getContainer(orderId);
        container.empty().html(ServiceDependantOptionsAbstract.LOADER);

        this.fetchTemplate()
            .then(function(result)
            {
                var data = {
                    order: orderId,
                    option: self.getOptionName(),
                    service: service
                };
                var accountId = $(ServiceDependantOptionsAbstract.SELECTOR_ACCOUNT_INPUT).val();
                var uri = ServiceDependantOptionsAbstract.URI.replace('{accountId}', accountId);
                self.getAjaxRequester().sendRequest(uri, data, function(response)
                {
                    self.renderNewOptions(
                        result.cgMustache, result.template, orderId, response[self.getOptionName()], selected, container
                    );
                });
            });
        return this;
    };

    ServiceDependantOptionsAbstract.prototype.getSelectedValue = function(orderId)
    {
        throw 'getSelectedValue must be overridden';
    };

    ServiceDependantOptionsAbstract.prototype.getContainer = function(orderId)
    {
        throw 'getContainer must be overridden';
    };

    ServiceDependantOptionsAbstract.prototype.getOptionName = function()
    {
        throw 'getOptionName must be overridden';
    };

    ServiceDependantOptionsAbstract.prototype.fetchTemplate = function()
    {
        var self = this;
        return new Promise(function(resolve, reject)
        {
            var template = self.getTemplate();
            if (template) {
                resolve({template: template, cgMustache: CGMustache.get()});
                return;
            }
            CGMustache.get().fetchTemplate(self.getTemplatePath(), function(template, cgMustache)
            {
                resolve({template: template, cgMustache: cgMustache});
            });
        });
    };

    ServiceDependantOptionsAbstract.prototype.renderNewOptions = function(
        cgMustache,
        template,
        orderId,
        options,
        selected,
        container
    ) {
        throw 'renderNewOptions must be overridden';
    };

    ServiceDependantOptionsAbstract.prototype.preventUpdateOptions = function(orderId)
    {
        return false;
    };

    return ServiceDependantOptionsAbstract;
});