define(['jquery'], function($)
{
    var DomManipulator = function()
    {

    };

    DomManipulator.EVENT_TEMPLATE_CHANGED = 'invoice-template-changed';
    DomManipulator.DOM_SELECTOR_TEMPLATE_CONTAINER = '#invoice-template';

    DomManipulator.prototype.insertTemplateHtml = function(html)
    {
        $(DomManipulator.DOM_SELECTOR_TEMPLATE_CONTAINER).empty().append(html);
    };

    DomManipulator.prototype.triggerTemplateChangeEvent = function(template)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_CHANGED, [template]);
        return this;
    };

    return new DomManipulator();
});