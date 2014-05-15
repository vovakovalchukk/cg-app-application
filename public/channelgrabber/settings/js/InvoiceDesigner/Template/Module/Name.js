define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/Name'
], function(
    ModuleAbstract,
    nameListener
    ) {
    var Name = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(nameListener);
    };

    Name.prototype = Object.create(ModuleAbstract.prototype);

    Name.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
    };

    return new Name();
});