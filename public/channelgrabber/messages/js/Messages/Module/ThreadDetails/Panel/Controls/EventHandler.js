define([
    'jquery',
    'Messages/Module/ThreadDetails/Panel/EventHandlerAbstract',
    'Messages/Module/ThreadDetails/Panel/Controls/Events'
], function(
    $,
    EventHandlerAbstract,
    ControlEvents
) {
    var EventHandler = function(panel)
    {
        EventHandlerAbstract.call(this, panel);

        var init = function()
        {
            this.listenForTakeClick()
                .listenForReleaseClick()
                .listenForResolveClick()
                .listenForAssigneeChange();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_TAKE = '#control-take';
    EventHandler.SELECTOR_RELEASE = '#control-release';
    EventHandler.SELECTOR_RESOLVE = '#control-resolve';
    EventHandler.SELECTOR_ASSIGNEE = '#control-assignee';

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForTakeClick = function()
    {
        var panel = this.getPanel();
        $(document).off('click', EventHandler.SELECTOR_TAKE).on('click', EventHandler.SELECTOR_TAKE, function()
        {
            panel.take();
        });
        return this;
    };

    EventHandler.prototype.listenForReleaseClick = function()
    {
        var panel = this.getPanel();
        $(document).off('click', EventHandler.SELECTOR_RELEASE).on('click', EventHandler.SELECTOR_RELEASE, function()
        {
            panel.release();
        });
        return this;
    };

    EventHandler.prototype.listenForResolveClick = function()
    {
        var panel = this.getPanel();
        $(document).off('click', EventHandler.SELECTOR_RESOLVE).on('click', EventHandler.SELECTOR_RESOLVE, function()
        {
            panel.resolve();
        });
        return this;
    };

    EventHandler.prototype.listenForAssigneeChange = function()
    {
        var panel = this.getPanel();
        $(document).off('change', EventHandler.SELECTOR_ASSIGNEE).on('change', EventHandler.SELECTOR_ASSIGNEE, function(event, select, value)
        {
            panel.assign(value);
        });
        return this;
    };

    EventHandler.prototype.triggerAssigneeChanged = function(thread)
    {
        $(document).trigger(ControlEvents.ASSIGNEE_CHANGED, [thread]);
    };

    EventHandler.prototype.triggerStatusChanged = function(thread)
    {
        $(document).trigger(ControlEvents.STATUS_CHANGED, [thread]);
    };

    return EventHandler;
});