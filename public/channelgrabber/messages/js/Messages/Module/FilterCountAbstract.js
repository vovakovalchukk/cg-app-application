define([
    'Messages/Module/FilterAbstract',
    'cg-mustache',
    'DomManipulator'
], function(
    FilterAbstract,
    CGMustache,
    domManipulator
) {
    var FilterCountAbstract = function(filterModule)
    {
        FilterAbstract.call(this, filterModule);

        this.setCount = function(count)
        {
            domManipulator.setHtml(this.getFilterSelector()+' '+FilterCountAbstract.SELECTOR_COUNT, count);
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    FilterCountAbstract.SELECTOR_COUNT = '.number-loz';

    FilterCountAbstract.prototype = Object.create(FilterAbstract.prototype);

    return FilterCountAbstract;
});
