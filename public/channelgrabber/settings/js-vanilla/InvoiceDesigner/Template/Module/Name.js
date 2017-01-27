define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/Name',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    nameListener,
    domManipulator
    ) {
    var Name = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(nameListener);

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Name.prototype = Object.create(ModuleAbstract.prototype);

    Name.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
        this.getDomManipulator().show(this.getDomListener().getTemplateNameContainerSelector());
        this.getDomManipulator().reloadName(this.getDomListener().getTemplateNameSelector(), template);
    };

    Name.prototype.updateName = function(name)
    {
        this.getTemplate().setName(name);
    };

    return new Name();
});