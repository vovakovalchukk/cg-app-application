define([
    '../ModuleAbstract',
    '../InspectorManager'
], function(
    ModuleAbstract,
    // TODO: DomListener
    templateInspectorManager
) {
    var InspectorManager = function()
    {
        ModuleAbstract.call(this);
        var inspectorManager = templateInspectorManager;

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

    return new InspectorManager();
});