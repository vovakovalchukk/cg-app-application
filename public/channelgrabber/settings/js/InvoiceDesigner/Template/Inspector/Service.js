define([
    'require',
    'InvoiceDesigner/Template/Inspector/Collection',
    // Inspector requires here
    'InvoiceDesigner/Template/Inspector/TextArea',
    'InvoiceDesigner/Template/Inspector/Heading'
], function(
    require,
    inspectorCollection,
    // Inspector variables here
    textArea,
    heading
) {
    var Service = function()
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

    Service.REQUIRED_INSPECTOR_METHODS = [
        'init', 'getInspectedAttributes', 'getId', 'hide', 'showForElement'
    ];

    Service.prototype.init = function(template)
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

    Service.prototype.initInspector = function(template, inspector)
    {
        if (!inspector.hasMethods(Service.REQUIRED_INSPECTOR_METHODS)) {
            throw 'InvalidArgumentException: InvoiceDesigner\\Template\\Inspector\\Service::init() encountered an invalid inspector';
        }
        inspector.init(template);

        var inspectors = this.getInspectors();
        var inspectedAttributes = inspector.getInspectedAttributes();
        for (var key in inspectedAttributes) {
            var attribute = inspectedAttributes[key];
            if (!inspectors[attribute]) {
                inspectors[attribute] = require('InvoiceDesigner/Template/Inspector/Collection');
            }
            inspectors[attribute].attach(inspector);
        }
    };

    Service.prototype.showForElement = function(element)
    {
        this.hideAll();
        var inspectors = this.getForElement(element);
        heading.showForElement(element, this.getTemplate(), this);
        inspectors.each(function(inspector)
        {
            inspector.showForElement(element);
        });
    };

    Service.prototype.hideAll = function()
    {
        var inspectors = this.getInspectors();
        for (var type in inspectors) {
            inspectors[type].each(function(inspector)
            {
                inspector.hide();
            });
        }
        heading.hide();
    };

    Service.prototype.getForElement = function(element)
    {
        var inspectorsForElement = require('InvoiceDesigner/Template/Inspector/Collection');
        var inspectors = this.getInspectors();
        var elementAttributes = element.getInspectableAttributes();
        for (var key in elementAttributes) {
            var attribute = elementAttributes[key];
            if (inspectors[attribute]) {
                inspectorsForElement.merge(inspectors[attribute]);
            }
        }
        return inspectorsForElement;
    };

    return new Service();
});