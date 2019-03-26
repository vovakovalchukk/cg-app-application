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
    ShippingServices.LOADER = `<div class="indicator-sizer -default u-margin-center">
                                ${loadingIndicator.getIndicator()}
                           </div>`;
    ShippingServices.URI_SERVICES_FOR_ORDER = '/orders/courier/services';
    ShippingServices.SELECT_ELEMENTS = ".courier-service-custom-select";

    ShippingServices.prototype.loadServicesSelectForOrder = function(orderId, accountId, name)
    {
        var self = this;
        var container = $('#' + ShippingServices.SELECT_ID_PREFIX + orderId);
        container.empty().append(ShippingServices.LOADER);

        var templatePromise = self.fetchTemplate();
        var dataPromise = self.fetchServicesForOrder(orderId, accountId);
        Promise.all([templatePromise, dataPromise]).then(function(responses)
        {
            var templateResponse = responses[0];
            var dataResponse = responses[1];

            var html = self.renderServicesSelect(
                orderId, dataResponse.serviceOptions, templateResponse.template, templateResponse.cgMustache, name
            );

            container.empty().append(html);
        });
    };

    ShippingServices.prototype.loadServicesSelectForOrderAndServices = function(orderId, serviceOptions, name)
    {
        var self = this;
        var container = $('#' + ShippingServices.SELECT_ID_PREFIX + orderId);
        container.empty().append(ShippingServices.LOADER);

        self.fetchTemplate().then(function(templateResponse)
        {
            var html = self.renderServicesSelect(
                orderId, serviceOptions, templateResponse.template, templateResponse.cgMustache, name
            );

            container.empty().append(html);
            var select = container.find(ShippingServices.SELECT_ELEMENTS);
            $(select).trigger("change", [$(select), $(select).find("> input:hidden").val()]);
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
        var selected;
        for (key in serviceOptions) {
            if (serviceOptions[key].selected) {
                selected = serviceOptions[key].value;
                break;
            }
        }

        var data = {
            id: ShippingServices.SELECT_ID_PREFIX + 'select-' + orderId,
            name: name || 'service_' + orderId,
            class: 'courier-service-select required',
            searchField: true,
            options: serviceOptions,
            initialValue: selected
        };

        var html = cgMustache.renderTemplate(template, data);

        var $html = $(html);
        $html.find('.custom-select').addClass('courier-service-custom-select');
        // html() calls innerHtml which drops the outer-most element so wrap it in a throw-away first
        html = $html.wrap('<div></div>').html();

        // If there's only one option don't bother with the select, just show it
        if (serviceOptions.length == 1) {
            html = this.renderSingleService(html, serviceOptions[0], orderId);
        }

        return html;
    };

    ShippingServices.prototype.renderSingleService = function(selectHtml, service, orderId)
    {
        // Keep the input, copy it to the new element
        var input = $('input[type=hidden]', selectHtml);
        input.val(service.value);

        var html = $('<div><span>'+service.title+'</span></div>')
            .append(input)
            .html();
        return '<div id="' + ShippingServices.SELECT_ID_PREFIX + orderId + '">' + html + '</div>';
    };

    return new ShippingServices();
});