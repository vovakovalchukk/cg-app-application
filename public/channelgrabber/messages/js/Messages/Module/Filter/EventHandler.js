define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Application/Events',
    'Messages/Module/Filter/Events',
    'Messages/Module/ThreadDetails/Panel/Controls/Events'
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
                .listenForAssigneeOrStatusChanged();
        };
        init.call(this);
    };

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForApplicationInitialised = function()
    {
        var self = this;
        $(document).on(AppEvents.INITIALISED, function(event, selectedThreadId)
        {
            self.getModule().initialise(selectedThreadId);
        });
        return this;
    };

    EventHandler.prototype.listenForAssigneeOrStatusChanged = function()
    {
        var self = this;
        $(document).on(ControlEvents.ASSIGNEE_CHANGED + ' ' + ControlEvents.STATUS_CHANGED, function(event, thread)
        {
            self.getModule().applyActiveFilters(thread.getId())
                .updateFilterCounts();
        });
        return this;
    };

    EventHandler.prototype.triggerApplyRequested = function(filter, selectedThreadId)
    {
        $(document).trigger(FilterEvents.APPLY_REQUESTED, [filter, selectedThreadId]);
    };

    return EventHandler;
});