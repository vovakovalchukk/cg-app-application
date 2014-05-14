define(['jquery'], function($)
{
    var DomManipulator = function()
    {

    };

    DomManipulator.SAVE_DISCARD_BAR_SELECTOR = '#save-template';
    DomManipulator.EVENT_TEMPLATE_CHANGED = 'event-template-changed';

    DomManipulator.prototype.insertTemplateHtml = function(html)
    {
        /*
         * TODO (CGIV-2026)
         * Use jQuery to insert the HTML in the right place
         */
    };

    DomManipulator.prototype.showSaveDiscardBar = function(template)
    {
        $(DomManipulator.SAVE_DISCARD_BAR_SELECTOR).show();
        return this;
    };

    DomManipulator.prototype.hideSaveDiscardBar = function(template)
    {
        $(DomManipulator.SAVE_DISCARD_BAR_SELECTOR).hide();
        return this;
    };

    DomManipulator.prototype.triggerTemplateChangeEvent = function (template)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_CHANGED, [template]);
        return this;
    };

    DomManipulator.prototype.getTemplateChangedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_CHANGED;
    };

    DomManipulator.prototype.enable = function(selector)
    {
        $(selector).removeClass('disabled');
    };

    return new DomManipulator();
});