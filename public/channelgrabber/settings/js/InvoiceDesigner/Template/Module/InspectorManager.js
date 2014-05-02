define([
    '../ModuleAbstract',
    '../Inspector/Service',
    './DomListener/InspectorManager'
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

        this.getService = function()
        {
            return service;
        };
    };

    InspectorManager.prototype = Object.create(ModuleAbstract.prototype);

    InspectorManager.prototype.init = function(template)
    {
        ModuleAbstract.prototype.init.call(this, template);
        this.getService().init();
    };

    InspectorManager.prototype.elementSelected = function(element)
    {
        this.getService().showForElement(element);
    };

    return new InspectorManager();
});