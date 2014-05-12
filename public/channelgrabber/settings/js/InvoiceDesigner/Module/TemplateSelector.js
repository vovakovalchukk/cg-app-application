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
        var template;
        this.setDomListener(templateSelectorListener);

        this.getService = function()
        {
            return service;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };

        this.getTemplate = function()
        {
            return template;
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
        this.setTemplate(this.getService().fetch(id));
        this.getService().loadModules(this.getTemplate());
        this.getDomListener().enableDuplicate();
    };

    TemplateSelector.prototype.duplicate()
    {
        this.getService().duplicate(this.getTemplate());
    }

    TemplateSelector.prototype.create()
    {
        this.getService().create();
    }

    return new TemplateSelector();
});