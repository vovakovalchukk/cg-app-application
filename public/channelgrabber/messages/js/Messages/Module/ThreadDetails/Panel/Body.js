define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Body/EventHandler'
], function(
    PanelAbstract,
    EventHandler
) {
    var Body = function(thread)
    {
        PanelAbstract.call(this, thread);

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Body.prototype = Object.create(PanelAbstract.prototype);

    Body.prototype.render = function(thread)
    {
        // TODO: CGIV-5839
    };

    return Body;
});