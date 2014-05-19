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
        var d = {
            type: elementName,
            borderWidth: 1,
            borderColour: 'black'
        };

        //var element = this.getTemplateService().getMapper().createNewElement(elementName);
        var element = this.getTemplateService().getMapper().elementFromJson(d, true);
        this.getTemplate().addElement(element);
        console.log(this.getTemplate().getElements().getItems());
    }

    return new ElementManager();
});