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
            this.listenForStockTotalSave()
                .listenForStockLevelSave()
                .listenForStockModeChange();
        };
        init.call(this);
    };

    DomListener.EVENT_INLINE_TEXT_SAVE = inlineTextListener.EVENT_INLINE_TEXT_SAVE;
    DomListener.EVENT_STOCK_MODE_UPDATED = 'product-stock-mode-change';
    DomListener.SELECTOR_STOCK_TOTAL = '.product-stock-total';
    DomListener.SELECTOR_STOCK_AVAILABLE = '.product-stock-available';
    DomListener.SELECTOR_STOCK_ALLOCATED = '.product-stock-allocated';
    DomListener.SELECTOR_STOCK_LOC_ETAG = '.product-stock-location-etag';
    DomListener.SELECTOR_STOCK_LEVEL = '.product-stock-level';
    DomListener.SELECTOR_STOCK_LEVEL_HOLDER = '.stock-level-holder';
    DomListener.SELECTOR_STOCK_PROD_ETAG = '.product-stock-product-etag';
    DomListener.SELECTOR_STOCK_MODE = '.stock-mode-holder';

    DomListener.prototype.listenForStockTotalSave = function()
    {
        var service = this.getService();
        $(document).on('save', DomListener.SELECTOR_STOCK_TOTAL, function(event, value) {
            var element = this;
            var idParts = $(element).attr('id').split('_');
            var stockLocationId = idParts.pop();
            var row = $(element).closest('tr');
            var availableElement = row.find(DomListener.SELECTOR_STOCK_AVAILABLE);
            var allocatedElement = row.find(DomListener.SELECTOR_STOCK_ALLOCATED);
            var etagElement = row.find(DomListener.SELECTOR_STOCK_LOC_ETAG);
            domManipulator.setHtml(availableElement.get(0), value - allocatedElement.html());
            service.save(stockLocationId, value, etagElement.val(), function(eTag){
                etagElement.val(eTag);
            });
        });
        return this;
    };

    DomListener.prototype.listenForStockLevelSave = function()
    {
        var service = this.getService();
        $(document).on('save', DomListener.SELECTOR_STOCK_LEVEL, function(event, value) {
            var element = this;
            var productId = $(element).closest(DomListener.SELECTOR_STOCK_LEVEL_HOLDER).data('productId');
            service.saveStockLevel(productId, value);
        });
        return this;
    };

    DomListener.prototype.listenForStockModeChange = function()
    {
        var service = this.getService();;
        $(document).on('change', DomListener.SELECTOR_STOCK_MODE, function(event, element, value) {
            var productId = $(element).attr('id').split('-').pop();
            var eTagElement = $('input[name="product[' + productId + '][eTag]"]');
            service.saveStockModeForProduct(productId, value, eTagElement);
        });
        return this;
    };

    DomListener.prototype.triggerStockModeUpdatedEvent = function(productId, stockMode, stockModeDesc, stockLevel)
    {
        // Expected to be picked up by Product/DomListener/Product
        $(document).trigger(DomListener.EVENT_STOCK_MODE_UPDATED, [productId, stockMode, stockModeDesc, stockLevel]);
        return this;
    };

    return DomListener;
});
