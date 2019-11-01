define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/AddDiscardBar',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    addDiscardBarListener,
    domManipulator
    ) {
    var AddDiscardBar = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(addDiscardBarListener);

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    AddDiscardBar.prototype = Object.create(ModuleAbstract.prototype);

    AddDiscardBar.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
    };

    AddDiscardBar.prototype.discard = function()
    {
        const template = this.getTemplate();
        const state = template.getState();
        const stateId = template.getStateId();
        const domManipulator = this.getDomManipulator();

        this.getTemplateService()[state](stateId);
        domManipulator.hideSaveDiscardBar();
    };

    AddDiscardBar.prototype.save = function()
    {
        var success = this.getTemplateService().save(this.getTemplate());
        if(! success) {
            return;
        }
        this.getDomManipulator().hideSaveDiscardBar();
    };

    return new AddDiscardBar();
});