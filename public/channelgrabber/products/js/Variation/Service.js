define([
    'cg-mustache',
    'DomManipulator',
    'Variation/DomListener',
    'Product/Filter/Mapper',
    'Product/Service'
], function (
    CGMustache,
    domManipulator,
    domListener,
    productFilterMapper,
    productService
) {
    var Service = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.getDomListener = function()
        {
            return domListener;
        };

        this.getProductFilterMapper = function()
        {
            return productFilterMapper;
        };

        this.getProductService = function()
        {
            return productService;
        };
    };

    Service.SELECTOR_LOADING_MESSAGE = '#products-loading-message';
    Service.SELECTOR_CONTENT_CONTAINER = '.product-content-container';
    Service.SELECTOR_EXPAND_BUTTON = '.product-variation-expand-button';
    Service.SELECTOR_VARIATION_TABLE = '.variation-table';
    Service.SELECTOR_STOCK_TABLE = '.stock-table';
    Service.SELECTOR_ID = ':input[name=id]';
    Service.CLASS_AJAX = 'expand-button-ajax';
    Service.CLASS_EXPANDED = 'expanded';
    Service.DEFAULT_DISPLAY_VARIATIONS = 2;

    Service.prototype.init = function()
    {
        this.getDomListener().init(this);
    };

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
        this.getDomManipulator().setCssValue(Service.SELECTOR_LOADING_MESSAGE, 'display', 'block');

        var self = this;
        var productId = this.getDomManipulator().getValue(containerSelector + ' ' + Service.SELECTOR_ID);
        var productFilter = this.getProductFilterMapper().fromParentProductId(productId);
        this.getProductService().fetchProducts(
            productFilter,
            function(variations) {
                self.getProductService().fetchProductTemplates(function(templates)
                {
                    var variationTableBodySelector = self.getSelectorForProductContainer(productContainer, Service.SELECTOR_VARIATION_TABLE + ' tbody');
                    var stockTableBodySelector = self.getSelectorForProductContainer(productContainer, Service.SELECTOR_STOCK_TABLE + ' tbody');
                    var attributeNamesSelector = self.getSelectorForProductContainer(productContainer, Service.SELECTOR_VARIATION_TABLE + ' thead th:nth-child(n+3)');
                    var attributeNames = self.getAttributeNamesFromDom(attributeNamesSelector);
                    var variationRows = '';
                    var stockRows = '';

                    for (var index in variations) {
                        var variation = variations[index];
                        variationRows += self.getProductService().getVariationLineView(
                            templates, variation, attributeNames
                        );
                        stockRows += self.getProductService().getStockTableLineView(
                            variation['stock']['locations'][0], templates
                        );
                    }

                    self.getDomManipulator().setHtml(variationTableBodySelector, variationRows);
                    self.getDomManipulator().setHtml(stockTableBodySelector, stockRows);
                });

                self.getDomManipulator().setCssValue(Service.SELECTOR_LOADING_MESSAGE, 'display', 'none');
                self.expandVariations(productContainer);
            }
        );
    };

    Service.prototype.getAttributeNamesFromDom = function(attributeNamesSelector)
    {
        var attributeNames = [];
        this.getDomManipulator().each(attributeNamesSelector, function() {
            attributeNames.push($.trim($(this).text()));
        });
        return attributeNames;
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