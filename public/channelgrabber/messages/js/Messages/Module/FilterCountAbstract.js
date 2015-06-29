define([
    'Messages/Module/FilterAbstract',
    'Messages/Module/Filter/Common/EventHandler',
    'cg-mustache',
    'DomManipulator'
], function(
    FilterAbstract,
    EventHandler,
    CGMustache,
    domManipulator
) {
    var FilterCountAbstract = function(filterModule)
    {
        FilterAbstract.call(this, filterModule);

        var eventHandler;

        this.setCount = function(count)
        {
            domManipulator.setHtml(this.getFilterSelector()+' '+FilterCountAbstract.SELECTOR_COUNT, count);
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);
        };
        init.call(this);
    };

    FilterCountAbstract.SELECTOR_COUNT = '.number-loz';

    FilterCountAbstract.prototype = Object.create(FilterAbstract.prototype);

    return FilterCountAbstract;
});
