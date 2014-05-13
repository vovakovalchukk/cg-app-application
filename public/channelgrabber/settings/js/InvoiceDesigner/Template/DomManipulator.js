define(['jquery'], function($)
{
    var DomManipulator = function()
    {

    };

    DomManipulator.SAVE_TEMPLATE_SELECTOR = '#save-template';
    DomManipulator.EVENT_TEMPLED_CHANGED = 'event-entity-changed';

    DomManipulator.prototype.insertTemplateHtml = function(html)
    {
        /*
         * TODO (CGIV-2026)
         * Use jquery to insert the HTML in the right place
         */
    };

    DomManipulator.prototype.showSaveDiscardBar = function(template)
    {
        $(DomManipulator.SAVE_TEMPLATE_SELECTOR).show();
        return this;
    };

    DomManipulator.prototype.hideSaveDiscardBar = function(template)
    {
        $(DomManipulator.SAVE_TEMPLATE_SELECTOR).hide();
        return this;
    };

    DomManipulator.prototype.triggerTemplateChanged = function ()
    {
        $(DomManipulator.EVENT_TEMPLED_CHANGED).trigger();
    };

    return new DomManipulator();
});