define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ElementManager',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    ElementManagerListener,
    domManipulator
) {
    var ElementManager = function ()
    {
        ModuleAbstract.call(this);
        this.setDomListener(ElementManagerListener);

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    ElementManager.prototype = Object.create(ModuleAbstract.prototype);

    ElementManager.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
        this.getDomManipulator().show(this.getDomListener().getContainerSelector());
    };

    ElementManager.prototype.addElementToCurrentTemplate = function(elementName)
    {
        var element = this.getTemplateService().getMapper().createNewElement(elementName);
        this.getTemplate().addElement(element);
    }

    return new ElementManager();
});