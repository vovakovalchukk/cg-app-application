define(['module'], function(requireModule)
{
    var TemplateSelector = function()
    {
        var module;
        var events = requireModule.config().events;

        this.getEvents = function()
        {
            return events;
        };

        this.getModule = function(module)
        {
            return module;
        };

        this.setModule = function(newModule)
        {
            module = newModule;
            return this;
        };
    };

    TemplateSelector.protoype.init = function(module)
    {
        this.setModule(module);
        /*
         * TODO (CGIV-2009): foreach event add a listener that calls back to module
         */
    };

    return new TemplateSelector();
});