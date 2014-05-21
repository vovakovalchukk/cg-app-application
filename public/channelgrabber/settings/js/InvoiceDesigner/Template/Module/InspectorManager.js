define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Inspector/Service',
    'InvoiceDesigner/Template/Module/DomListener/InspectorManager'
], function(
    ModuleAbstract,
    inspectorService,
    inspectorManagerListener
) {
    var InspectorManager = function()
    {
        ModuleAbstract.call(this);
        var service = inspectorService;
        this.setDomListener(inspectorManagerListener);

        this.getInspectorService = function()
        {
            return service;
        };
    };

    InspectorManager.prototype = Object.create(ModuleAbstract.prototype);

    InspectorManager.prototype.init = function(template, templateService)
    {
        ModuleAbstract.prototype.init.call(this, template, templateService);
        this.getInspectorService().init(template);
    };

    InspectorManager.prototype.elementSelected = function(element)
    {
        this.getInspectorService().showForElement(element);
    };

    return new InspectorManager();
});