define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/Renderer'
], function(
    ModuleAbstract,
    rendererListener
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
        this.getTemplateService().render(template);
    };

    return new Renderer();
});