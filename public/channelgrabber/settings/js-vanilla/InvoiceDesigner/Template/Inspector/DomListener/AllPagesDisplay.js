define([
    'jquery'
], function(
    $
) {
    const AllPagesDisplay = function() {
    };

    AllPagesDisplay.prototype.init = function(inspector, element) {
        this.initDisplayOnAllPagesListener(inspector, element);
    };

    AllPagesDisplay.prototype.initDisplayOnAllPagesListener = function(inspector, element) {
        const selector = '#' + inspector.getAllPagesDisplayCheckboxId();
        const checkboxNode = $(selector);
        checkboxNode.off('change').on('change', () => {
            inspector.setDisplayOnAllPages(element, checkboxNode.is(":checked"));
        });
        return this;
    };

    return new AllPagesDisplay();
});