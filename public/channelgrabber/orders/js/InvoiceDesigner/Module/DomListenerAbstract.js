define(function()
{
    var DomListenerAbstract = function()
    {
        var module;
        var domListener;

        this.getModule = function()
        {
            return module;
        };

        this.setModule = function(newModule)
        {
            module = newModule;
            return this;
        };

        this.getDomListener = function()
        {
            return domListener;
        };
    };

    DomListenerAbstract.prototype.init = function(module)
    {
        this.setModule(module);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    return DomListenerAbstract;
});