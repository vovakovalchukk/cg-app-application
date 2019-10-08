define([
    'InvoiceDesigner/Template/Inspector/Collection',
    // Inspector requires here
    'InvoiceDesigner/Template/Inspector/Text',
    'InvoiceDesigner/Template/Inspector/Heading',
    'InvoiceDesigner/Template/Inspector/Positioning',
    'InvoiceDesigner/Template/Inspector/Font',
    'InvoiceDesigner/Template/Inspector/Border',
    'InvoiceDesigner/Template/Inspector/OrderTableOptions',
    'InvoiceDesigner/Template/Inspector/Barcode',
    'InvoiceDesigner/Template/Inspector/LinkedProducts',
    'InvoiceDesigner/Template/Inspector/TableColumns',
    'InvoiceDesigner/Template/Inspector/AllPagesDisplay',
    'InvoiceDesigner/Template/Inspector/TableSortBy',
    'InvoiceDesigner/Template/Inspector/TableCells'
], function(
    Collection,
    // Inspector variables here
    text,
    heading,
    positioning,
    font,
    border,
    orderTableOptions,
    barcode,
    linkedProducts,
    tableColumns,
    allPagesDisplay,
    tableSortBy,
    tableCells
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
            text,
            positioning,
            font,
            border,
            orderTableOptions,
            barcode,
            linkedProducts,
            tableColumns,
            tableSortBy,
            allPagesDisplay,
            tableCells
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
                inspectors[attribute] = new Collection();
            }
            inspectors[attribute].attach(inspector);
        }
    };

    Service.prototype.showForElement = function(element, event)
    {
        this.hideAll();
        const inspectors = this.getForElement(element);

        if (isTableCellClick(event, element)) {
            inspectors.getItems().tableCells.showForElement(element, event);
            return;
        }

        heading.showForElement(element, this.getTemplate(), this);
        
        if (element.getType() !== 'OrderTable') {
            allPagesDisplay.showForElement(element, this.getTemplate(), this);
        }

        inspectors.each(function(inspector)
        {
            inspector.showForElement(element, event);
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
        var inspectorsForElement = new Collection();
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

    Service.prototype.removeCellSelections = function() {
        const allElements = this.getTemplate().getElements().getItems();

        for (let id in allElements) {
            let element = allElements[id];
            let type = element.getType();

            if (type !== 'OrderTable') {
                continue;
            }

            element.setActiveCellNodeId('');
            return;
        }
    };

    return new Service();

    function isTableCellClick(event, element) {
        if (!event) {
            return;
        }
        const tag = event.target.tagName.toLowerCase();
        const isCellTag =  tag === 'th' || tag === 'td';
        return event.target.id.includes(element.getId()) && isCellTag;
    }
});