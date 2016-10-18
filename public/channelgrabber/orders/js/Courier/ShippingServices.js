define(['AjaxRequester', 'cg-mustache'], function(ajaxRequester, CGMustache)
{
    function ShippingServices()
    {
        var template;

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
            return this;
        };

        this.getTemplate = function()
        {
            return template;
        };
    }

    ShippingServices.TEMPLATE_PATH = '/cg-built/zf2-v4-ui/templates/elements/custom-select.mustache';
    ShippingServices.SELECT_ID_PREFIX = 'courier-service-options-';
    ShippingServices.LOADER = '<img src="/cg-built/zf2-v4-ui/img/loading-transparent-21x21.gif">';
    ShippingServices.URI_SERVICES_FOR_ORDER = '/orders/courier/services';

    ShippingServices.prototype.loadServicesSelectForOrder = function(orderId, accountId, name)
    {
        var self = this;
        var td = $('#' + ShippingServices.SELECT_ID_PREFIX + orderId).closest('td');
        td.empty().append(ShippingServices.LOADER);

        var templatePromise = self.fetchTemplate();
        var dataPromise = self.fetchServicesForOrder(orderId, accountId);
        Promise.all([templatePromise, dataPromise]).then(function(responses)
        {
            var templateResponse = responses[0];
            var dataResponse = responses[1];

            var html = self.renderServicesSelect(
                orderId, dataResponse.serviceOptions, templateResponse.template, templateResponse.cgMustache, name
            );

            td.empty().append(html);
        });
    };

    ShippingServices.prototype.fetchTemplate = function()
    {
        var self = this;
        return new Promise(function(resolve, reject)
        {
            var template = self.getTemplate();
            if (template) {
                resolve({template: template, cgMustache: CGMustache.get()});
                return;
            }
            CGMustache.get().fetchTemplate(ShippingServices.TEMPLATE_PATH, function(template, cgMustache)
            {
                resolve({template: template, cgMustache: cgMustache});
            });
        });
    };

    ShippingServices.prototype.fetchServicesForOrder = function(orderId, accountId)
    {
        var ajaxRequester = this.getAjaxRequester();
        return new Promise(function(resolve, reject)
        {
            var uri = ShippingServices.URI_SERVICES_FOR_ORDER;
            var data = {"order": orderId, "account": accountId};
            ajaxRequester.sendRequest(uri, data, function(response)
            {
                resolve(response);
            }, function()
            {
                reject();
            });
        });
    };

    ShippingServices.prototype.renderServicesSelect = function(orderId, serviceOptions, template, cgMustache, name)
    {
        name = name || 'service_' + orderId;
        var data = {
            id: ShippingServices.SELECT_ID_PREFIX + orderId,
            //name: 'orderData[' + orderId + '][service]',
            name: name,
            class: 'courier-service-custom-select',
            searchField: false,
            options: serviceOptions
        };
        var html = cgMustache.renderTemplate(template, data);

        // If there's only one option don't bother with the select, just show it
        if (serviceOptions.length == 1) {
            $(html).removeAttr('class').html(function() {
                var input = $('input[type=hidden]', this);
                var selected = $('.custom-select-item.active', this);
                return $('<div></div>').text(selected.text()).append(input.val(selected.attr('data-value'))).html();
            });
        }

        return html;
    };

    return new ShippingServices();
});