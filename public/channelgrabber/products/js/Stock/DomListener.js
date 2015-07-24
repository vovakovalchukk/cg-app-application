define([
    'Stock/Service',
    'element/DomListener/InlineText',
    'DomManipulator'
], function(
    service,
    inlineTextListener,
    domManipulator
) {
    var DomListener = function()
    {
    };

    DomListener.EVENT_INLINE_TEXT_SAVE = inlineTextListener.EVENT_INLINE_TEXT_SAVE;

    DomListener.prototype.init = function(elementSelector, stockLocationId, eTagSelector, availableSelector, allocatedSelector)
    {
        $(elementSelector).off('save').on('save', function(event, value) {
            domManipulator.setHtml(availableSelector, value - $(allocatedSelector).html());
            service.save(stockLocationId, value, $(eTagSelector).val(), function(eTag){
                $(eTagSelector).val(eTag);
            });
        });
    };

    return DomListener;
});
