define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ElementManager'
], function(
    ModuleAbstract,
    ElementManagerListener
) {
    var ElementManager = function ()
    {
        ModuleAbstract.call(this);
        this.setDomListener(ElementManagerListener);
    };

    ElementManager.prototype = Object.create(ModuleAbstract.prototype);

    ElementManager.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
    };

    ElementManager.prototype.addElementToCurrentTemplate = function(elementName)
    {
        var element = this.getTemplateService().getMapper().createNewElement(elementName);
        this.getTemplate().addElement(element, true);
        console.log(this.getTemplate().getElements().getItems());
    }

    return new ElementManager();
});