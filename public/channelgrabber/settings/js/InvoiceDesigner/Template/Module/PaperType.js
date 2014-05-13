define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PaperType',
    'InvoiceDesigner/Template/Service'
], function(
    ModuleAbstract,
    paperTypeListener,
    templateService
    ) {
    var PaperType = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        var template;
        this.setDomListener(paperTypeListener);

        this.getService = function()
        {
            return service;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };

        this.getTemplate = function()
        {
            return template;
        };
    };

    PaperType.prototype = Object.create(ModuleAbstract.prototype);

    PaperType.prototype.init = function(application)
    {
        ModuleAbstract.prototype.init.call(this, application);
        this.getDomListener().init(this);
        // TODO Load paper type options from storage
        // TODO show ui. Currently shown by default until CGIV-2002
    };

    PaperType.prototype.selectionMade = function(id)
    {
        // TODO Look up paper type by id
        // TODO template.getPage().setBackgroundImage(paperTypeById.getBackgroundImage())
    };

    return new PaperType();
});