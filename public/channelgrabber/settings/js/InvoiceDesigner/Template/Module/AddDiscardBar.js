define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/AddDiscardBar'
], function(
    ModuleAbstract,
    addDiscardBarListener
    ) {
    var AddDiscardBar = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(addDiscardBarListener);
    };

    AddDiscardBar.prototype = Object.create(ModuleAbstract.prototype);

    AddDiscardBar.prototype.discard = function()
    {
        this.getTemplateService().discard();
    };

    AddDiscardBar.prototype.save = function()
    {
        this.getTemplateService().save();
    };

    return new AddDiscardBar();
});