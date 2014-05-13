define(['jquery'], function($)
{
    var DomListenerAbstract = function()
    {
        var module;

        this.getModule = function()
        {
            return module;
        };

        this.setModule = function(newModule)
        {
            module = newModule;
            return this;
        };
    };

    DomListenerAbstract.prototype.init = function(module)
    {
        this.setModule(module);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    return DomListenerAbstract;
});