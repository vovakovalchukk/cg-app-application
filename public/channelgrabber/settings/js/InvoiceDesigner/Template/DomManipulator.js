define(['jQuery'], function($)
{
    var DomManipulator = function()
    {

    };

    DomManipulator.EVENT_TEMPLATE_CHANGED = 'invoice-template-changed';

    DomManipulator.prototype.insertTemplateHtml = function(html)
    {
        /*
         * TODO (CGIV-2026)
         * Use jQuery to insert the HTML in the right place
         */
    };

    DomManipulator.prototype.triggerTemplateChangeEvent = function(template)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_CHANGED, [template]);
        return this;
    }

    return new DomManipulator();
});