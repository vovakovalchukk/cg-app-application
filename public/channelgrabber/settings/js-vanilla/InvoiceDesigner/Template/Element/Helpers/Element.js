define([], function() {
    const ElementHelper = function() {
        return this;
    };

    ElementHelper.ELEMENT_DOM_CLASS = 'template-element';

    ElementHelper.prototype.getElementDomId = function(element) {
        const domIdPrefix = ElementHelper.ELEMENT_DOM_CLASS;
        const elementId = element.getId();
        return `${domIdPrefix}-${elementId}`
    };

    return new ElementHelper;
});