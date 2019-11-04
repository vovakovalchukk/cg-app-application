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
        checkboxNode.off('change').on('change', event => {
            const value = event.target.value === 'on';
            inspector.setDisplayOnAllPages(element, value);
        });
        return this;
    };

    return new AllPagesDisplay();
});