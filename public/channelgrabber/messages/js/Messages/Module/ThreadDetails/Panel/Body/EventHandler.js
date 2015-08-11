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
            this.listenForPrintClick();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_PRINT = '.message-print';

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForPrintClick = function()
    {
        var panel = this.getPanel();
        $(document).off('click', EventHandler.SELECTOR_PRINT).on('click', EventHandler.SELECTOR_PRINT, function()
        {
            panel.print($(this).closest("li"));
        });
        return this;
    };

    return EventHandler;
});
