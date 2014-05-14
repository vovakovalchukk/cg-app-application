define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Template/AddDiscardBar/Service',
    'InvoiceDesigner/Template/Module/DomListener/AddDiscardBar'
], function(
    ModuleAbstract,
    addDiscardBarService,
    addDiscardBarListener
    ) {
    var AddDiscardBar = function()
    {
        ModuleAbstract.call(this);
        var service = addDiscardBarService;
        this.setDomListener(addDiscardBarListener);

        this.getService = function()
        {
            return service;
        };
    };

    AddDiscardBar.prototype = Object.create(ModuleAbstract.prototype);

    AddDiscardBar.prototype.init = function(template)
    {
        ModuleAbstract.prototype.init.call(this, template);
        this.getService().init(template);
        this.getDomListener().init(this);
    };

    AddDiscardBar.prototype.discard = function()
    {
        this.getService().discard();
    };

    AddDiscardBar.prototype.save = function()
    {
        this.getService().save();
    };

    return new AddDiscardBar();
});