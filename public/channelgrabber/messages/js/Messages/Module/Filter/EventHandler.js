define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Application/Events',
    'Messages/Module/Filter/Events'
], function(
    $,
    EventHandlerAbstract,
    AppEvents,
    FilterEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForApplicationInitialised();
        };
        init.call(this);
    };

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForApplicationInitialised = function()
    {
        var self = this;
        $(document).on(AppEvents.INITIALISED, function()
        {
            self.getModule().applyActiveFilters();
        });
    };

    EventHandler.prototype.triggerApplyRequested = function(filter)
    {
        $(document).trigger(FilterEvents.APPLY_REQUESTED, [filter]);
    };

    return EventHandler;
});