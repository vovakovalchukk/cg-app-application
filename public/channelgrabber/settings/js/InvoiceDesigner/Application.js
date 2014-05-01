define([
    // Application Module requires here
    'InvoiceDesigner/Module/TemplateSelector'
], function(
    // Application Module variables here
    templateSelector
) {
    var Application = function()
    {
        var modules = [
            // Modules here
            templateSelector
        ];

        this.getModules = function()
        {
            return modules;
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