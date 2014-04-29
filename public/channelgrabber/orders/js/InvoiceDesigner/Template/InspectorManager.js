define([
    './Inspector/Collection'
    // Inspector requires here
], function(
    inspectorCollection
    // Inspector variables here
) {
    var InspectorManager = function()
    {
        var inspectors = {};
        var template;

        this.getInspectors = function()
        {
            return inspectors;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };
    };

    InspectorManager.prototype.init = function(template)
    {
        this.setTemplate(template);
        
        var inspectorsToAdd = [
            // Inspector variables here
        ];

        for (var key in inspectorsToAdd) {
            var inspector = inspectorsToAdd[key];
            this.initInspector(template, inspector);
        }
    };

    InspectorManager.prototype.initInspector = function(template, inspector)
    {
        if (!inspector.hasMethods(['init', 'getSupportedTypes', 'getId'])) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\InspectorManager::init() encountered an invalid inspector';
        }
        inspector.init(template);

        var inspectors = this.getInspectors();
        var supportedTypes = inspector.getSupportedTypes();
        for (var key2 in supportedTypes) {
            var type = supportedTypes[key2];
            if (!inspectors[type]) {
                inspectors[type] = require('./Inspector/Collection');
            }
            inspectors[type].attach(inspector);
        }
    };

    InspectorManager.prototype.getForType = function(type)
    {
        var inspectors = this.getInspectors();
        if (!inspectors[type]) {
            inspectors[type] = require('./Inspector/Collection');
        }
        return inspectors[type];
    };

    return new InspectorManager();
});