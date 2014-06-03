define(['InvoiceDesigner/ModuleAbstract'], function(AppModuleAbstract)
{
    var ModuleAbstract = function()
    {
        AppModuleAbstract.call(this);

        var template;
        var templateService;

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };

        this.getTemplateService = function()
        {
            return templateService;
        };

        this.setTemplateService = function(newService)
        {
            templateService = newService;
            return this;
        };
    };

    ModuleAbstract.TEMPLATE_PATH = '/channelgrabber/settings/template/InvoiceDesigner/Template/';

    ModuleAbstract.prototype = Object.create(AppModuleAbstract.prototype);

    ModuleAbstract.prototype.init = function(template, templateService)
    {
        this.setTemplate(template);
        this.setTemplateService(templateService);
        this.getDomListener().init(this);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    return ModuleAbstract;
});