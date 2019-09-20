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
        this.getInspectorService().hideAll();
    };

    InspectorManager.prototype.elementSelected = function(element, event)
    {
        this.getInspectorService().showForElement(element, event);
    };

    InspectorManager.prototype.elementDeselected = function(element)
    {
        this.getInspectorService().hideAll();
    };

    InspectorManager.prototype.tableCellClick = function(event) {
        this.getInspectorService().hideAllButTableCellInspector(event);
    };

    return new InspectorManager();
});