define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Module/Filter/Events'
], function(
    $,
    EventHandlerAbstract,
    FilterEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForFilterApplyRequested();
        };
        init.call(this);
    };

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForFilterApplyRequested = function()
    {
        var self = this;
        $(document).on(FilterEvents.APPLY_REQUESTED, function(event, filter)
        {
            self.getModule().loadForFilter(filter);
        });
    };

    return EventHandler;
});