define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Application/Events',
    'Messages/Module/Filter/Events',
    'Messages/Module/ThreadDetails/Panel/Controls/Events',
], function(
    $,
    EventHandlerAbstract,
    AppEvents,
    FilterEvents,
    ControlEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForApplicationInitialised()
                .listenForAssigneeChanged();
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
        return this;
    };

    EventHandler.prototype.listenForAssigneeChanged = function()
    {
        var self = this;
        $(document).on(ControlEvents.ASSIGNEE_CHANGED, function(event, thread)
        {
            self.getModule().applyActiveFilters(thread);
        });
        return this;
    };

    EventHandler.prototype.triggerApplyRequested = function(filter, selectedThread)
    {
        $(document).trigger(FilterEvents.APPLY_REQUESTED, [filter, selectedThread]);
    };

    return EventHandler;
});