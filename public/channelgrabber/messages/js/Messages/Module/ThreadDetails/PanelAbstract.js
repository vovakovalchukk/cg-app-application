define([

], function(

) {
    var PanelAbstract = function(thread)
    {
        var eventHandler;

        this.getThread = function()
        {
            return thread;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.setEventHandler = function(newEventHandler)
        {
            eventHandler = newEventHandler;
            return this;
        };
    };

    return PanelAbstract;
});