define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Module/DomListener/TemplateChange',
    'InvoiceDesigner/Template/Service'
], function(
    ModuleAbstract,
    templateChangeListener,
    templateService
    ) {
    var TemplateSelector = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        this.setDomListener(templateChangeListener);

        this.getService = function()
        {
            return service;
        };
    };

    TemplateSelector.prototype = Object.create(ModuleAbstract.prototype);

    TemplateSelector.prototype.init = function(application)
    {
        ModuleAbstract.prototype.init.call(this, application);
        this.getDomListener().init(this);
    };

    TemplateSelector.prototype.notifyOfChange = function ()
    {
        this.getService().notifyOfChange();
    };

    return new TemplateSelector();
});