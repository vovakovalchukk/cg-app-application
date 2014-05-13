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
        this.getApplication().setTemplate(this.getService().fetch(id));
        this.getService().loadModules(this.getApplication().getTemplate());
        this.getDomListener().enableDuplicate();
    };

    TemplateSelector.prototype.duplicate = function()
    {
        this.getService().duplicate(this.getApplication().getTemplate());
    };

    TemplateSelector.prototype.create = function()
    {
        this.getService().create();
    };

    return new TemplateSelector();
});