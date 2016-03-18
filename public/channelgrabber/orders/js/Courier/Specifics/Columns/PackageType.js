define(['AjaxRequester', 'cg-mustache'], function(ajaxRequester, CGMustache)
{
    function PackageType(templateMap)
    {
        var template;

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getTemplateMap = function()
        {
            return templateMap;
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

        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    PackageType.SELECTOR_PACKAGE_TYPE_PREFIX = '#courier-package-type_';
    PackageType.SELECTOR_PACKAGE_TYPE_CONTAINER = '.courier-package-type-options';
    PackageType.SELECTOR_SERVICE_SELECT = '.courier-service-custom-select';
    PackageType.SELECTOR_ACCOUNT_INPUT = '#courier-specifics-label-form input';
    PackageType.URI = '/orders/courier/specifics/{accountId}/optionData';
    PackageType.LOADER = '<img src="/cg-built/zf2-v4-ui/img/loading-transparent-21x21.gif">';

    PackageType.prototype.listenForServiceChanges = function()
    {
        var self = this;
        $(document).on('change', PackageType.SELECTOR_SERVICE_SELECT, function(event, element, value)
        {
            var orderId = $(element).data('elementName').match(/^orderData\[(.+?)\]/)[1];
            self.updateOptionsForOrder(orderId, value);
        });
    };

    PackageType.prototype.updateOptionsForOrder = function(orderId, service)
    {
        var self = this;
        var selected = $(PackageType.SELECTOR_PACKAGE_TYPE_PREFIX + orderId + ' input').val();
        var container = $(PackageType.SELECTOR_PACKAGE_TYPE_PREFIX + orderId)
            .closest(PackageType.SELECTOR_PACKAGE_TYPE_CONTAINER);
        container.empty().html(PackageType.LOADER);

        this.fetchTemplate()
            .then(function(template)
            {
                var data = {
                    order: orderId,
                    option: "packageTypes",
                    service: service
                };
                var accountId = $(PackageType.SELECTOR_ACCOUNT_INPUT).val();
                var uri = PackageType.URI.replace('{accountId}', accountId);
                self.getAjaxRequester().sendRequest(uri, data, function(response)
                {
                    self.renderNewOptions(template, orderId, response.packageTypes, selected, container);
                });
            });
    };

    PackageType.prototype.fetchTemplate = function()
    {
        var self = this;
        return new Promise(function(resolve, reject)
        {
            var template = self.getTemplate();
            if (template) {
                resolve(template);
                return;
            }
            CGMustache.get().fetchTemplate(self.getTemplateMap()['select'], function(template)
            {
                resolve(template);
            });
        });
    };

    PackageType.prototype.renderNewOptions = function(template, orderId, options, selected, container)
    {
        var data = {
            id: PackageType.SELECTOR_PACKAGE_TYPE_PREFIX.replace('#', '') + orderId,
            name: 'orderData[' + orderId + '][packageType]',
            class: 'required',
            options: []
        };
        for (var index in options) {
            data.options.push({
                title: options[index],
                selected: (options[index] == selected)
            });
        }
        var html = CGMustache.get().renderTemplate(template, data);
        container.empty().append(html);
        return this;
    };

    return PackageType;
});