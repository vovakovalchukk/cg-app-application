define([
    'InvoiceDesigner/Template/Service'
], function(
    service
    ) {
    var Service = function()
    {
        var template;

        this.getService = function()
        {
            return service;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };
    };

    Service.prototype.init = function(template)
    {
        this.setTemplate(template);
    };

    Service.prototype.discard = function()
    {
        var state = this.getTemplate().getState();
        this.getService()[state](this.getTemplate().getStateId());
    };

    Service.prototype.save = function()
    {
        this.getService().save(this.getTemplate());
    };

    return new Service();
});