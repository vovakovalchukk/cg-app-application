define([
    'jquery',
    'Messages/Module/ThreadDetails/Panel/EventHandlerAbstract',
    'Messages/Module/ThreadDetails/Panel/Respond/Events',
], function(
    $,
    EventHandlerAbstract,
    RespondEvents
) {
    var EventHandler = function(panel)
    {
        EventHandlerAbstract.call(this, panel);

        var init = function()
        {
            this.listenForSendClick()
                .listenForSendAndResolveClick();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_SEND = '#respond-send-shadow';
    EventHandler.SELECTOR_SEND_RESOLVE = '#respond-send-resolve-shadow';

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForSendClick = function()
    {
        var panel = this.getPanel();
        $(document).off('click', EventHandler.SELECTOR_SEND).on('click', EventHandler.SELECTOR_SEND, function()
        {
            panel.send();
        });
        return this;
    };

    EventHandler.prototype.listenForSendAndResolveClick = function()
    {
        var panel = this.getPanel();
        $(document).off('click', EventHandler.SELECTOR_SEND_RESOLVE).on('click', EventHandler.SELECTOR_SEND_RESOLVE, function()
        {
            panel.sendAndResolve();
        });
        return this;
    };

    EventHandler.prototype.triggerMessageAdded = function(message, resolve)
    {
        $(document).trigger(RespondEvents.MESSAGE_ADDED, [message, resolve]);
    };

    return EventHandler;
});