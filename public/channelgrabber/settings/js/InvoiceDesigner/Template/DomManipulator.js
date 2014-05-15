define(['jquery'], function($)
{
    var DomManipulator = function()
    {

    };

    DomManipulator.EVENT_TEMPLATE_CHANGED = 'invoice-template-changed';
    DomManipulator.EVENT_TEMPLATE_ELEMENT_SELECTED = 'invoice-template-element-selected';
    DomManipulator.DOM_SELECTOR_TEMPLATE_CONTAINER = '#invoice-template-container';

    DomManipulator.prototype.insertTemplateHtml = function(html)
    {
        $(DomManipulator.DOM_SELECTOR_TEMPLATE_CONTAINER).empty().append(html);
    };

    DomManipulator.prototype.triggerTemplateChangeEvent = function(template)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_CHANGED, [template]);
        return this;
    };

    DomManipulator.prototype.triggerElementSelectedEvent = function(element)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_ELEMENT_SELECTED, [element]);
        return this;
    };

    DomManipulator.prototype.getTemplateChangedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_CHANGED;
    };

    DomManipulator.prototype.getElementSelectedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_ELEMENT_SELECTED;
    };

    return new DomManipulator();
});
