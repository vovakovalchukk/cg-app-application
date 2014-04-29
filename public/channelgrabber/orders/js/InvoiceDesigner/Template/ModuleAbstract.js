define(function()
{
    var ModuleAbstract = function()
    {
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

    ModuleAbstract.prototype.init = function(template)
    {
        this.setTemplate(template);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    return ModuleAbstract;
});