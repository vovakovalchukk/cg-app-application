define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Module/DomListener/AddDiscardBar',
    'InvoiceDesigner/Template/Service'
], function(
    ModuleAbstract,
    AddDiscardBarListener,
    templateService
    ) {
    var AddDiscardBar = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        this.setDomListener(AddDiscardBarListener);

        this.getService = function()
        {
            return service;
        };
    };

    AddDiscardBar.prototype = Object.create(ModuleAbstract.prototype);

    AddDiscardBar.prototype.init = function(application)
    {
        ModuleAbstract.prototype.init.call(this, application);
        this.getDomListener().init(this);
    };

    AddDiscardBar.prototype.discard = function()
    {
        var state = this.getApplication().getTemplate().getState();
        this.getService()[state](this.getApplication().getTemplate().getStateId());
    };

    AddDiscardBar.prototype.save = function()
    {
        this.getService().save(this.getApplication().getTemplate());
    };

    return new AddDiscardBar();
});