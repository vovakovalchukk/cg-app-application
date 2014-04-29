define(['../ModuleAbstract'], function(AppModuleAbstract)
{
    var ModuleAbstract = function()
    {
        AppModuleAbstract.call(this);

        var template;

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };
    };

    ModuleAbstract.prototype = Object.create(AppModuleAbstract.prototype);

    ModuleAbstract.prototype.init = function(template)
    {
        this.setTemplate(template);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    return ModuleAbstract;
});