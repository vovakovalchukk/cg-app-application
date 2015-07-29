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
            this.listenForButtonClick()
                .listenForEnterPress();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_BUTTON = '#filter-search-button';
    EventHandler.SELECTOR_INPUT = '#filter-search-field';
    EventHandler.KEY_ENTER = 13;

    EventHandler.prototype.listenForButtonClick = function()
    {
        var filter = this.getFilter();
        $(EventHandler.SELECTOR_BUTTON).click(function()
        {
            filter.activate();
        });
        return this;
    };

    EventHandler.prototype.listenForEnterPress = function()
    {
        var filter = this.getFilter();
        $(EventHandler.SELECTOR_INPUT).keypress(function(event)
        {
            if (event.which != EventHandler.KEY_ENTER) {
                return;
            }
            filter.activate();
        });
        return this;
    };

    return EventHandler;
});