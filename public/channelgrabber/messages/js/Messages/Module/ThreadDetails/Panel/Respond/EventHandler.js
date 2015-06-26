define([
    'jquery',
    'Messages/Module/ThreadDetails/Panel/EventHandlerAbstract'
], function(
    $,
    EventHandlerAbstract
) {
    var EventHandler = function(panel)
    {
        EventHandlerAbstract.call(this, panel);

        var init = function()
        {
            // TODO: CGIV-5839, listen to DOM events for this panel
        };
        init.call(this);
    };

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    return EventHandler;
});