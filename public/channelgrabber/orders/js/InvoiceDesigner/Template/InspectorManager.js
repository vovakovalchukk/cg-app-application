define([
    'require',
    './Inspector/Collection',
    // Inspector requires here
    './Inspector/TextArea'
], function(
    require,
    inspectorCollection,
    // Inspector variables here
    textArea
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

    InspectorManager.REQUIRED_INSPECTOR_METHODS = [
        'init', 'getSupportedTypes', 'getId', 'clear', 'showForElement'
    ];

    InspectorManager.prototype.init = function(template)
    {
        this.setTemplate(template);
        
        var inspectorsToAdd = [
            // Inspector variables here
            textArea
        ];

        for (var key in inspectorsToAdd) {
            var inspector = inspectorsToAdd[key];
            this.initInspector(template, inspector);
        }
    };

    InspectorManager.prototype.initInspector = function(template, inspector)
    {
        if (!inspector.hasMethods(InspectorManager.REQUIRED_INSPECTOR_METHODS)) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\InspectorManager::init() encountered an invalid inspector';
        }
        inspector.init(template);

        var inspectors = this.getInspectors();
        var supportedTypes = inspector.getSupportedTypes();
        for (var key in supportedTypes) {
            var type = supportedTypes[key];
            if (!inspectors[type]) {
                inspectors[type] = require('./Inspector/Collection');
            }
            inspectors[type].attach(inspector);
        }
    };

    InspectorManager.prototype.showForElement = function(element)
    {
        this.clear();

        var inspectors = this.getForType(element.getType());
        inspectors.each(function(inspector)
        {
            inspector.showForElement(element);
        });
    };

    InspectorManager.prototype.clear = function()
    {
        var inspectors = this.getInspectors();
        for (var type in inspectors) {
            inspectors[type].each(function(inspector)
            {
                inspector.clear();
            });
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