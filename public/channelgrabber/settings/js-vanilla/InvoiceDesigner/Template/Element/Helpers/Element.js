define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
], function(
    ElementMapperAbstract
) {
    const ElementHelper = function() {
        return this;
    };

    ElementHelper.prototype.getElementDomId = function(element) {
        const domIdPrefix = ElementMapperAbstract.getDomIdPrefix();
        const elementId = element.getId();
        return `${domIdPrefix}${elementId}`
    };

    return new ElementHelper;
});