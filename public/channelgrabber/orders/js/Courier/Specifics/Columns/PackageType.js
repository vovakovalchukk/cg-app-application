define(['AjaxRequester'], function(ajaxRequester)
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

        this.setTemplate = function()
        {

        }

        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    PackageType.SELECTOR_SERVICE_SELECT = '.courier-service-custom-select';
    PackageType.URI = '/orders/courier/specifics/packageTypes';

    PackageType.prototype.listenForServiceChanges = function()
    {
        var self = this;
        $(document).on('change', PackageType.SELECTOR_SERVICE_SELECT, function(event, element, value)
        {
            var orderId = element.dataset.elementName.match(/^orderData\[(.+?)\]/)[1];
            self.updateOptionsForOrder(orderId);
        });
    };

    PackageType.prototype.updateOptionsForOrder = function(orderId)
    {
        // TODO: disable the current packageType dropdown / show its loading somehow
        this.getAjaxRequester().sendRequest(PackageType.URI, data, function(response)
        {
            // TODO: get the mustache template (pre-load it?)
            // remove the old dropdown and render the new one over the top
        });
    };

    return PackageType;
});