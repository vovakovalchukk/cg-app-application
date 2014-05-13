define([
    'InvoiceDesigner/Module/DomListenerAbstract',
    'InvoiceDesigner/Template/DomManipulator',
    'jquery'
], function(
    DomListenerAbstract,
    domManipulator,
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
        $(document).on(domManipulator.getTemplateChangedEvent(), function (event) {
            self.getModule().notifyOfChange();
        });
    };

    return new TemplateSelector();
});