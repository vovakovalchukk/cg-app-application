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



    return new AddDiscardBar();
});