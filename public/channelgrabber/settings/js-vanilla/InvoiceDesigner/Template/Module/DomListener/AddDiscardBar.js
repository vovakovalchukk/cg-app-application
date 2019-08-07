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
        DomListenerAbstract.prototype.init.call(this, module);
        $(AddDiscardBar.SAVE_TEMPLATE_SELECTOR).off('click').click(() => {
            const module = this.getModule()
            module.save();
        });
        $(AddDiscardBar.DISCARD_TEMPLATE_SELECTOR).off('click').click(() => {
            const module = this.getModule();
            module.discard();
        });
    };

    return new AddDiscardBar();
});