define([
    'jquery'
], function(
    $
) {
    var EventHandler = function(filter)
    {
        this.getFilter = function()
        {
            return filter;
        };

        var init = function()
        {
            this.listenForClick();
        };
        init.call(this);
    };

    EventHandler.prototype.listenForClick = function()
    {
        var filter = this.getFilter();
        $(filter.getFilterSelector()).click(function()
        {
            filter.activate();
        });
    };

    return EventHandler;
});