define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ToPdf',
    'InvoiceDesigner/Template/DomManipulator',
], function(
    ModuleAbstract,
    toPdfListener,
    domManipulator
) {
    var ToPdf = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(toPdfListener);

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    ToPdf.MODULE_SELECTOR = '#template-topdf-container';

    ToPdf.prototype = Object.create(ModuleAbstract.prototype);

    ToPdf.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
        this.getDomManipulator().show(ToPdf.MODULE_SELECTOR);
    };

    ToPdf.prototype.toPdf = function()
    {
        var templateJson = this.getTemplateService().getMapper().toJson(this.getTemplate());
        this.getTemplateService().showAsPdf(templateJson);
    };

    return new ToPdf();
});