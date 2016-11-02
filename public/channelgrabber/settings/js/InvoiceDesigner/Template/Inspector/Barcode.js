define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/Barcode',
    'cg-mustache'
], function(
    InspectorAbstract,
    barcodeDomListener,
    CGMustache
) {
    var Barcode = function()
    {
        var actions = [
            {
                id: 'id',
                text: 'Order ID',
            },{
                id: 'view',
                text: 'Take to order details page',
            }, {
                id: 'dispatch',
                text: 'Dispatch the order'
            }
        ];

        InspectorAbstract.call(this);

        this.setId('barcode');
        this.setInspectedAttributes(['action']);

        this.getActions = function()
        {
            return actions;
        };

        this.setActions = function(newAction)
        {
            actions = newAction;
            return this;
        };
    };

    Barcode.BARCODE_INSPECTOR_SELECTOR = '#barcode-inspector';
    Barcode.BARCODE_INSPECTOR_ACTIONS_ID = 'barcode-inspector-actions';

    Barcode.prototype = Object.create(InspectorAbstract.prototype);

    Barcode.prototype.hide = function()
    {
        this.getDomManipulator().render(Barcode.BARCODE_INSPECTOR_SELECTOR, "");
    };

    Barcode.prototype.showForElement = function(element)
    {
        var self = this;
        var templateUrlMap = {
            actions: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            barcode: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/barcode.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };

        var actions = [];
        self.getActions().forEach(function(action)
        {
            actions.push({value: action.id, title: action.text, selected: (element.getAction() === action.id)});
        });

        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var actionsTemplate = cgmustache.renderTemplate(templates, {
                initialTitle: 'Select Action',
                id: Barcode.BARCODE_INSPECTOR_ACTIONS_ID,
                name: Barcode.BARCODE_INSPECTOR_ACTIONS_ID,
                options: actions
            }, "actions");

            var barcodeTemplate = cgmustache.renderTemplate(templates, {}, "barcode", {'actionSelector': actionsTemplate});

            var collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Barcode Action',
                'id': 'barcode-collapsible'
            }, "collapsible", {'content':barcodeTemplate});

            self.getDomManipulator().render(Barcode.BARCODE_INSPECTOR_SELECTOR, collapsible);
            barcodeDomListener.init(self, element);
        });
    };

    Barcode.prototype.actionSelected = function(selectElement, action)
    {
        console.log('action selected: '+action);
    };

    Barcode.prototype.getBarcodeInspectorActionsId = function()
    {
        return Barcode.BARCODE_INSPECTOR_ACTIONS_ID;
    };

    return new Barcode();
});