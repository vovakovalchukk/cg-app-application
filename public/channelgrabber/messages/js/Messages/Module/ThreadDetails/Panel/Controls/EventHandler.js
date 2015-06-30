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
            this.listenForTakeClick();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_TAKE = '#control-take';

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForTakeClick = function()
    {
        var panel = this.getPanel();
        $(document).on('click', EventHandler.SELECTOR_TAKE, function()
        {
            panel.take();
        });
    };

    return EventHandler;
});