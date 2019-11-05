define([
    'InvoiceDesigner/Module/DomListenerAbstract',
    'InvoiceDesigner/Template/DomManipulator',
    'element/ElementCollection'
    
], function(
    DomListenerAbstract,
    domManipulator,
    ElementCollection
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
        var element = ElementCollection.get('template');
        if (element) {
            element.enable();
        }

        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        $(templateSelectorId).on('change', function (event, selectBox, id) {
            self.getModule().paperTypeSelectionMade(id);
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
        $(document).on(domManipulator.getTemplateSelectedEvent(), function (event, template) {
            self.getModule().setTemplate(template);
        });
    };

    TemplateSelector.prototype.getDuplicateTemplateSelector = function()
    {
        return TemplateSelector.DUPLICATE_TEMPLATE_SELECTOR;
    };

    return new TemplateSelector();
});