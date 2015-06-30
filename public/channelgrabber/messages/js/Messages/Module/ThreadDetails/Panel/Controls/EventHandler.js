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

    return EventHandler;
});