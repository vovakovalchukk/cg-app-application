define([
    'element/DomListener/InlineText',
    'DomManipulator'
], function(
    inlineTextListener,
    domManipulator
) {
    var DomListener = function(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.listenForStockTotalSave();
        };
        init.call(this);
    };

    DomListener.EVENT_INLINE_TEXT_SAVE = inlineTextListener.EVENT_INLINE_TEXT_SAVE;
    DomListener.SELECTOR_STOCK_TOTAL = '.product-stock-total';
    DomListener.SELECTOR_STOCK_AVAILABLE = '.product-stock-available';
    DomListener.SELECTOR_STOCK_ALLOCATED = '.product-stock-allocated';
    DomListener.SELECTOR_STOCK_LOC_ETAG = '.product-stock-location-etag';

    DomListener.prototype.listenForStockTotalSave = function()
    {
        var service = this.getService();
        $(document).on('save', DomListener.SELECTOR_STOCK_TOTAL, function(event, value) {
            var element = this;
            var idParts = $(element).attr('id').split('_');
            var stockLocationId = idParts.pop();
            var availableElement = $(element).closest('tr').find(DomListener.SELECTOR_STOCK_AVAILABLE);
            var allocatedElement = $(element).closest('tr').find(DomListener.SELECTOR_STOCK_ALLOCATED);
            var etagElement = $(element).closest('tr').find(DomListener.SELECTOR_STOCK_LOC_ETAG);
            domManipulator.setHtml(availableElement.get(0), value - allocatedElement.html());
            service.save(stockLocationId, value, etagElement.val(), function(eTag){
                etagElement.val(eTag);
            });
        });
        return this;
    };

    return DomListener;
});
