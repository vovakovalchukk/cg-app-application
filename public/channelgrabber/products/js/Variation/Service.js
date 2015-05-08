define([
    'cg-mustache',
    'DomManipulator',
    'Product/Filter/Mapper',
    'Product/Storage/Ajax'
], function (
    CGMustache,
    domManipulator,
    productFilterMapper,
    productStorage
) {
    var Service = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.getProductFilterMapper = function()
        {
            return productFilterMapper;
        };

        this.getProductStorage = function()
        {
            return productStorage;
        };
    };

    Service.SELECTOR_CONTENT_CONTAINER = '.product-content-container';
    Service.SELECTOR_EXPAND_BUTTON = '.product-variation-expand-button';
    Service.SELECTOR_VARIATION_TABLE = '.variation-table';
    Service.SELECTOR_ID = ':input[name=id]';
    Service.CLASS_AJAX = 'expand-button-ajax';
    Service.CLASS_EXPANDED = 'expanded';
    Service.DEFAULT_DISPLAY_VARIATIONS = 2;

    Service.prototype.toggleVariations = function(productContainer)
    {
        var contentSelector = this.getSelectorForProductContainer(productContainer, Service.SELECTOR_CONTENT_CONTAINER);
        var expanded = this.getDomManipulator().hasClass(contentSelector, Service.CLASS_EXPANDED);
        if (expanded) {
            this.collapseVariations(productContainer);
        } else {
            var containerSelector = this.getSelectorForProductContainer(productContainer);
            var ajax = this.getDomManipulator().hasClass(containerSelector + ' ' + Service.SELECTOR_EXPAND_BUTTON, Service.CLASS_AJAX);
            if (ajax) {
                this.loadAdditionalVariations(productContainer);
            } else {
                this.expandVariations(productContainer);
            }
        }
    };

    Service.prototype.loadAdditionalVariations = function(productContainer)
    {
        var containerSelector = this.getSelectorForProductContainer(productContainer);
        this.getDomManipulator().removeClass(containerSelector + ' ' + Service.SELECTOR_EXPAND_BUTTON, Service.CLASS_AJAX);

        var self = this;
        var productId = this.getDomManipulator().getValue(containerSelector + ' ' + Service.SELECTOR_ID);
        var productFilter = this.getProductFilterMapper().fromProductId(productId);
        this.getProductStorage().fetchByFilter(
            productFilter,
            function(variations) {
                var productUrlMap = {
                    checkbox: '/channelgrabber/zf2-v4-ui/templates/elements/checkbox.mustache',
                    buttons: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
                    inlineText: '/channelgrabber/zf2-v4-ui/templates/elements/inline-text.mustache',
                    variationTable: '/channelgrabber/products/template/product/variationTable.mustache',
                    variationRow: '/channelgrabber/products/template/product/variationRow.mustache',
                    variationStock: '/channelgrabber/products/template/product/variationStock.mustache',
                    stockTable: '/channelgrabber/products/template/product/stockTable.mustache',
                    stockRow: '/channelgrabber/products/template/product/stockRow.mustache',
                    product: '/channelgrabber/products/template/elements/product.mustache',
                    statusLozenge: '/channelgrabber/products/template/elements/statusLozenge.mustache'
                };

                var tableBodySelector = self.getSelectorForProductContainer(productContainer, Service.SELECTOR_VARIATION_TABLE + ' tbody');

                CGMustache.get().fetchTemplates(productUrlMap, function(templates) {
                    for (var index in variations) {
                        var variation = variations[index];
                        var attributeValues = [];

                        var variationRow = CGMustache.get().renderTemplate(templates, {
                            'image': self.getPrimaryImage(variation['images']),
                            'sku': variation['sku'],
                            'attributes': attributeValues
                        }, 'variationRow');

                        self.getDomManipulator().append(variationRow, tableBodySelector + ' tr:last');
                    }
                });

                self.expandVariations(productContainer);
            }
        );
    };

    Service.prototype.getPrimaryImage = function(images)
    {
        return images.length > 0 ? images[0]['url'] : this.getBaseUrl() + Service.DEFAULT_IMAGE_URL;
    };

    Service.prototype.expandVariations = function(productContainer)
    {
        var containerSelector = this.getSelectorForProductContainer(productContainer);
        var contentSelector = containerSelector + ' ' + Service.SELECTOR_CONTENT_CONTAINER;
        this.getDomManipulator().addClass(contentSelector, Service.CLASS_EXPANDED);

        this.getDomManipulator().setCssValue(containerSelector, 'height', this.calculateMaxProductContainerHeight(productContainer));

        var buttonSelector = containerSelector + ' ' + Service.SELECTOR_EXPAND_BUTTON;
        this.toggleButton(buttonSelector);
    };

    Service.prototype.collapseVariations = function(productContainer)
    {
        var containerSelector = this.getSelectorForProductContainer(productContainer);
        var contentSelector = containerSelector + ' ' + Service.SELECTOR_CONTENT_CONTAINER;
        this.getDomManipulator().removeClass(contentSelector, Service.CLASS_EXPANDED);

        this.getDomManipulator().setCssValue(containerSelector, 'height', '');

        var buttonSelector = containerSelector + ' ' + Service.SELECTOR_EXPAND_BUTTON;
        this.toggleButton(buttonSelector);
    };

    Service.prototype.getSelectorForProductContainer = function(productContainer, childSelector)
    {
        var containerId = this.getDomManipulator().getAttribute(productContainer, 'id');
        var selector = '#' + containerId;
        if (childSelector) {
            selector += ' '+childSelector;
        }
        return selector;
    };

    Service.prototype.toggleButton = function(buttonSelector)
    {
        var oldValue = this.getDomManipulator().getHtml(buttonSelector + ' .action .title');
        var newValue = this.getDomManipulator().getAttribute(buttonSelector + ' .action', 'data-action');
        this.getDomManipulator().setHtml(buttonSelector + ' .action .title', newValue);
        this.getDomManipulator().setAttribute(buttonSelector + ' .action', 'data-action', oldValue);
    };

    Service.prototype.calculateMaxProductContainerHeight = function(productContainer)
    {
        var rowSelector = this.getSelectorForProductContainer(productContainer, Service.SELECTOR_VARIATION_TABLE + ' tbody tr');
        var rowCount = this.getDomManipulator().getCount(rowSelector);
        var newRowCount = rowCount - Service.DEFAULT_DISPLAY_VARIATIONS;
        var rowHeight = this.getDomManipulator().getSize(rowSelector + ':first').outerHeight;
        var containerSelector = this.getSelectorForProductContainer(productContainer);
        var containerHeight = this.getDomManipulator().getSize(containerSelector).height;

        return containerHeight + (rowHeight * newRowCount);
    };

    return new Service();
});