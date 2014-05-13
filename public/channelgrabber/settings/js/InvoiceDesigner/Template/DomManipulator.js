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

    DomManipulator.prototype.triggerTemplateChanged = function ()
    {
        $(DomManipulator.EVENT_TEMPLATE_CHANGED).trigger();
    };

    DomManipulator.prototype.getTemplateChangedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_CHANGED;
    };

    return new DomManipulator();
});