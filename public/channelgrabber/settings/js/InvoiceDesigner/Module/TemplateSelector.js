define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Module/DomListener/TemplateSelector',
    'InvoiceDesigner/Template/Service'
], function(
    ModuleAbstract,
    templateSelectorListener,
    templateService
) {
    var TemplateSelector = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        this.setDomListener(templateSelectorListener);

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

    TemplateSelector.prototype.selectionMade = function(id)
    {
        var template = this.getService().fetch(id);
        this.getService().loadModules(template);
    };

    return new TemplateSelector();
});