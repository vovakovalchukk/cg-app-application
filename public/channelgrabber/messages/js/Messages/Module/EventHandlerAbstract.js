define([], function()
{
    var EventHandlerAbstract = function(module)
    {
        this.getModule = function()
        {
            return module;
        };
    };

    return EventHandlerAbstract;
});