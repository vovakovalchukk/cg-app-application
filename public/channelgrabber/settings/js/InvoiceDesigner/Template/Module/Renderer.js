define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/Renderer',
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    ModuleAbstract,
    rendererListener,
    ElementMapperAbstract
) {
    var Renderer = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(rendererListener);
    };

    Renderer.prototype = Object.create(ModuleAbstract.prototype);

    Renderer.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
        this.templateChanged(template);
    };

    Renderer.prototype.templateChanged = function(template)
    {
        var self = this;
        this.getTemplateService().render(template);
        template.getElements().each(function(element)
        {
            if (element.getType() === 'page') {
                return true;
            }
            var domId = ElementMapperAbstract.getDomId(element);
            self.getDomListener().listenForElementSelect(domId, element);
        });
    };

    return new Renderer();
});