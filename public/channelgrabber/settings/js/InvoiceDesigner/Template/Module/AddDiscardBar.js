define([
    'InvoiceDesigner/Template/ModuleAbstract',
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

    AddDiscardBar.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
    };

    AddDiscardBar.prototype.discard = function()
    {
        var state = this.getTemplate().getState();
        this.getTemplateService()[state](this.getTemplate().getStateId());
    };

    AddDiscardBar.prototype.save = function()
    {
        this.getTemplateService().save(this.getTemplate());
    };

    return new AddDiscardBar();
});