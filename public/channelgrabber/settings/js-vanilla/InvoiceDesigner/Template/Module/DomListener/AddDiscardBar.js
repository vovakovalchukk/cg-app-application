define([
    'InvoiceDesigner/Module/DomListenerAbstract',
    'jquery'
], function(
    DomListenerAbstract,
    $
) {

    var AddDiscardBar = function()
    {
        DomListenerAbstract.call(this);
    };

    AddDiscardBar.SAVE_TEMPLATE_SELECTOR = '#save-template-button';
    AddDiscardBar.DISCARD_TEMPLATE_SELECTOR = '#discard-template-button';

    AddDiscardBar.prototype = Object.create(DomListenerAbstract.prototype);

    AddDiscardBar.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        $(AddDiscardBar.SAVE_TEMPLATE_SELECTOR).off('click').click(function() {
            self.getModule().save();
        });
        $(AddDiscardBar.DISCARD_TEMPLATE_SELECTOR).off('click').click(function() {
            self.getModule().discard();
        });
    };

    return new AddDiscardBar();
});