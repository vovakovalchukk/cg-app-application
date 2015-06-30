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
                .listenForAssigneeChange();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_TAKE = '#control-take';
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

    return EventHandler;
});