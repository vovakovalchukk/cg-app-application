define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Controls/EventHandler'
], function(
    PanelAbstract,
    EventHandler
) {
    var Controls = function(thread)
    {
        PanelAbstract.call(this, thread);

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Controls.prototype = Object.create(PanelAbstract.prototype);

    Controls.prototype.render = function(thread)
    {
        // TODO: CGIV-5839
    };

    return Controls;
});