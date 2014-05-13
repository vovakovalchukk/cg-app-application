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

    AddDiscardBar.SAVE_TEMPLATE_SELECTOR = '#save-template';
    AddDiscardBar.DISCARD_TEMPLATE_SELECTOR = '#discard-template';

    AddDiscardBar.prototype = Object.create(DomListenerAbstract.prototype);

    AddDiscardBar.prototype.init = function(module)
    {
        var self = this;
        $(AddDiscardBar.SAVE_TEMPLATE_SELECTOR).click(function() {
            self.getModule().save();
        });
        $(AddDiscardBar.DISCARD_TEMPLATE_SELECTOR).click(function() {
            self.getModule().discard();
        });
    };

    return new AddDiscardBar();
});