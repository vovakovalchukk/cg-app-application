define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Module/DomListener/TemplateSelector',
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    templateSelectorListener,
    templateService,
    domManipulator
) {
    var TemplateSelector = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        var manipulator = domManipulator;
        this.setDomListener(templateSelectorListener);

        this.getService = function()
        {
            return service;
        };

        this.getDomManipulator = function()
        {
            return manipulator;
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
        this.getDomManipulator().enable(this.getDomListener().getDuplicateTemplateSelector());
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