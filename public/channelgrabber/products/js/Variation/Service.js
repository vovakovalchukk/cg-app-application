define([
    'DomManipulator'
], function (
    domManipulator
) {
    var Service = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Service.SELECTOR_CONTENT_CONTAINER = '.product-content-container';
    Service.SELECTOR_EXPAND_BUTTON = '.product-variation-expand-button';
    Service.SELECTOR_VARIATION_TABLE = '.variation-table';
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
                this.getDomManipulator().removeClass(containerSelector + ' ' + Service.SELECTOR_EXPAND_BUTTON, Service.CLASS_AJAX);
            }
            this.expandVariations(productContainer);
        }
    };

    Service.prototype.loadAdditionalVariations = function(productContainer)
    {

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