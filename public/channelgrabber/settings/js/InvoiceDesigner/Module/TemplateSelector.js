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
        var template;
        this.setDomListener(templateSelectorListener);

        this.getService = function()
        {
            return service;
        };

        this.getDomManipulator = function()
        {
            return manipulator;
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
    };

    TemplateSelector.prototype = Object.create(ModuleAbstract.prototype);

    TemplateSelector.prototype.init = function(application, templateId)
    {
        ModuleAbstract.prototype.init.call(this, application);

        if (templateId !== '') {
            this.setTemplate(this.getService().fetchAndLoadModules(templateId));
        } else {
            this.getService().createForOu(this.getApplication().getOrganisationUnitId());
        }

        if (window.location.href.indexOf("duplicate") > -1) {
            this.getService().duplicate(this.getTemplate());
        }
    };

    TemplateSelector.prototype.selectionMade = function(id)
    {
        this.setTemplate(this.getService().fetchAndLoadModules(id));
        this.getDomManipulator().enable(this.getDomListener().getDuplicateTemplateSelector());
    };

    TemplateSelector.prototype.duplicate = function()
    {
        this.getService().duplicate(this.getTemplate());
    };

    TemplateSelector.prototype.create = function()
    {
        this.getService().createForOu(this.getApplication().getOrganisationUnitId());
    };

    return new TemplateSelector();
});