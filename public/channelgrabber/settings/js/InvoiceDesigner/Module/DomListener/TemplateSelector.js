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

    TemplateSelector.DUPLICATE_TEMPLATE_SELECTOR = '#duplicate-template';
    TemplateSelector.NEW_TEMPLATE_SELECTOR = '#new-template';

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

    TemplateSelector.prototype.getDuplicateTemplateSelector = function()
    {
        return TemplateSelector.DUPLICATE_TEMPLATE_SELECTOR;
    };

    return new TemplateSelector();
});