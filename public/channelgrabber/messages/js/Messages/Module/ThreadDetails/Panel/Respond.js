define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Respond/EventHandler'
], function(
    PanelAbstract,
    EventHandler
) {
    var Respond = function(thread)
    {
        PanelAbstract.call(this, thread);

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Respond.prototype = Object.create(PanelAbstract.prototype);

    Respond.prototype.render = function(thread)
    {
        // TODO: CGIV-5839
    };

    return Respond;
});