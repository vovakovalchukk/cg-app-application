define([
    '../ModuleAbstract',
    '../InspectorManager',
    './DomListener/InspectorManager'
], function(
    ModuleAbstract,
    inspectorManagerListener,
    templateInspectorManager
) {
    var InspectorManager = function()
    {
        ModuleAbstract.call(this);
        var inspectorManager = templateInspectorManager;
        this.setDomListener(inspectorManagerListener);

        this.getInspectorManager = function()
        {
            return inspectorManager;
        };
    };

    InspectorManager.prototype = Object.create(ModuleAbstract.prototype);

    InspectorManager.prototype.init = function(template)
    {
        ModuleAbstract.prototype.init.call(this, template);
        this.getInspectorManager().init();
    };

    InspectorManager.prototype.elementSelected = function(element)
    {
        this.getInspectorManager().showForElement(element);
    };

    return new InspectorManager();
});