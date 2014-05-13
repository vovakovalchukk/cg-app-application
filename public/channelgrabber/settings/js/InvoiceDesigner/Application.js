define([
    // Application Module requires here
    'InvoiceDesigner/Module/TemplateSelector',
    'InvoiceDesigner/Module/AddDiscardBar'
], function(
    // Application Module variables here
    templateSelector,
    addDiscardBar
) {
    var Application = function()
    {
        var template;
        var modules = [
            // Modules here
            templateSelector,
            addDiscardBar
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

    this.getTemplate = function()
    {
        return template;
    };

    this.setTemplate = function(newTemplate)
    {
        template = newTemplate;
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