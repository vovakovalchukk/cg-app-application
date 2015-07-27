define([], function()
{
    var EventHandlerAbstract = function(panel)
    {
        this.getPanel = function()
        {
            return panel;
        };
    };

    return EventHandlerAbstract;
});