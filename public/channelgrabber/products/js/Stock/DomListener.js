define([
    'jquery',
    'Stock/Service',
    'element/DomListener/InlineText'
], function(
    $,
    service,
    inlineTextListener
) {
    var DomListener = function()
    {
    };

    DomListener.EVENT_INLINE_TEXT_SAVE = inlineTextListener.EVENT_INLINE_TEXT_SAVE;

    DomListener.prototype.init = function(elementSelector, stockLocationId, eTagSelector)
    {
        $(elementSelector).off('save').on('save', function(event, value) {
            service.save(stockLocationId, value, eTagSelector);
        });
    };

    return DomListener;
});