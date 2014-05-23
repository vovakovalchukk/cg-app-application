define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/Renderer',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    rendererListener,
    ElementMapperAbstract,
    domManipulator
) {
    var Renderer = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(rendererListener);

        var selectedElement;
        this.setSelectedElement = function(element)
        {
            selectedElement = element;
            return this;
        };

        this.getSelectedElement = function()
        {
            return selectedElement;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Renderer.prototype = Object.create(ModuleAbstract.prototype);

    Renderer.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
        this.templateChanged(template);
    };

    Renderer.prototype.elementSelected = function(element)
    {
        this.setSelectedElement(element);
    };

    Renderer.prototype.elementDeselected = function()
    {
        this.setSelectedElement(undefined);
    };

    Renderer.prototype.templateChanged = function(template)
    {
        var self = this;
        var selectedElement = this.getSelectedElement();
        this.getTemplateService().render(template);
        template.getElements().each(function(element)
        {
            var domWrapperId = ElementMapperAbstract.getDomWrapperId(element);
            self.getDomListener().listenForElementSelect(domWrapperId, element);

            if (selectedElement && selectedElement.getId() === element.getId()) {
                self.getDomManipulator().triggerElementSelectedEvent(element);
            }
        });
    };

    return new Renderer();
});