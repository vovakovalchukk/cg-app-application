define([
    'DomManipulator'
], function(
    domManipulator
) {
    var PanelAbstract = function(module, thread)
    {
        var eventHandler;

        this.getModule = function()
        {
            return module;
        };

        this.setModule = function(newModule)
        {
            module = newModule;
            return this;
        };

        this.getThread = function()
        {
            return thread;
        };

        this.setThread = function(newThread)
        {
            thread = newThread;
            return this;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
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

    PanelAbstract.SELECTOR_CONTAINER = '.message-preview';

    return PanelAbstract;
});