define([
    // Application Module requires here
    'InvoiceDesigner/Module/TemplateSelector',
    'InvoiceDesigner/Module/TemplateChange'
], function(
    // Application Module variables here
    templateSelector,
    templateChange
) {
    var Application = function()
    {
        var modules = [
            // Modules here
            templateSelector,
            templateChange
        ];

        this.getModules = function()
        {
            return modules;
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

    Application.prototype.init = function()
    {
        var modules = this.getModules();
        for (var key in modules) {
            modules[key].init(this);
        }
    };

    return new Application();
});