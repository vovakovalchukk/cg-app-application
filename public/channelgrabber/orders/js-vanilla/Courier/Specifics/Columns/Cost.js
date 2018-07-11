define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function Cost(templatePath)
    {
        console.log(templatePath);
        ServiceDependantOptionsAbstract.call(this, templatePath);

        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    Cost.prototype = Object.create(ServiceDependantOptionsAbstract.prototype);

    Cost.prototype.listenForServiceChanges = function()
    {
        var self = this;
        $(document).on('change', ServiceDependantOptionsAbstract.SELECTOR_SERVICE_SELECT, function(event, element, value)
        {
            var orderId = $(element).data('elementName').match(/^orderData\[(.+?)\]/)[1];
            self.updateShippingLabelCost(orderId, value);
        });
        return this;
    };

    Cost.prototype.updateShippingLabelCost = function(orderId, service)
    {
        var self = this;

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
                    console.log(response);
                });
            });
        return this;
    };

    Cost.prototype.getSelectedValue = function(orderId)
    {
        return true;
    };

    Cost.prototype.getContainer = function(orderId)
    {
        return true;
    };

    Cost.prototype.getOptionName = function()
    {
        return 'cost';
    };

    Cost.prototype.renderNewOptions = function(
        cgMustache,
        template,
        orderId,
        options,
        selected,
        container
    ) {
        return true;
    };

    return Cost;
});
