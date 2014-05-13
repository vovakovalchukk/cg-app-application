define([
    'InvoiceDesigner/Module/DomListenerAbstract',
    'element/customSelect',
    'jquery'
], function(
    DomListenerAbstract,
    CustomSelect,
    $
) {

    var TemplateSelector = function()
    {
        DomListenerAbstract.call(this);
    };

    TemplateSelector.prototype.DUPLICATE_TEMPLATE_SELECTOR = '#duplicate-template';
    TemplateSelector.prototype.NEW_TEMPLATE_SELECTOR = '#new-template';

    TemplateSelector.prototype = Object.create(DomListenerAbstract.prototype);

    TemplateSelector.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        $(document).on(CustomSelect.EVENT_SELECT_CHANGED, function (event, selectBox, id) {
            self.getModule().selectionMade(id);
        });
        $(TemplateSelector.DUPLICATE_TEMPLATE_SELECTOR).click(function () {
            if ($(this).hasClass('disabled'))  {
                return;
            }

            self.getModule().duplicate();
        });
        $(TemplateSelector.NEW_TEMPLATE_SELECTOR).click(function () {
            self.getModule().create();
        });
    };

    TemplateSelector.prototype.enableDuplicate = function()
    {
        $(TemplateSelector.DUPLICATE_TEMPLATE_SELECTOR).removeClass('disabled');
    };

    return new TemplateSelector();
});