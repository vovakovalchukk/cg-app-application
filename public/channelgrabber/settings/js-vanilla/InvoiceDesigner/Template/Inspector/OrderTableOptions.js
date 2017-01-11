define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/OrderTableOptions',
    'cg-mustache'
], function(
    InspectorAbstract,
    OrderTableOptionsDomListener,
    CGMustache
) {
    var OrderTableOptions = function()
    {
        InspectorAbstract.call(this);

        this.setId('orderTableOptions');
        // This Inspector used to handle a 'Show VAT?' option but that is now controlled by the OU's vatRegistered flag
        //this.setInspectedAttributes(['showVat']);
    };

    OrderTableOptions.ORDER_TABLE_OPTIONS_INSPECTOR_SELECTOR = '#orderTableOptions-inspector';
    OrderTableOptions.ORDER_TABLE_SHOW_VAT_ID = 'show-vat-checkbox';

    OrderTableOptions.prototype = Object.create(InspectorAbstract.prototype);

    OrderTableOptions.prototype.hide = function()
    {
        this.getDomManipulator().render(OrderTableOptions.ORDER_TABLE_OPTIONS_INSPECTOR_SELECTOR, "");
    };

    OrderTableOptions.prototype.showForElement = function(element)
    {
        var self = this;
        var templateUrlMap = {
            checkbox: '/channelgrabber/zf2-v4-ui/templates/elements/checkbox.mustache',
            orderTableOptions: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/orderTableOptions.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var showVat = cgmustache.renderTemplate(templates, self.getShowVatData(element), 'checkbox');

            var orderTableOptions = cgmustache.renderTemplate(templates, {}, 'orderTableOptions', {
                showVat: showVat
            });

            var collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Options',
                'id': 'ordertable-collapsible'
            }, 'collapsible', {'content': orderTableOptions});

            self.getDomManipulator().render(OrderTableOptions.ORDER_TABLE_OPTIONS_INSPECTOR_SELECTOR, collapsible);
            OrderTableOptionsDomListener.init(self, element);
        });
    };

    OrderTableOptions.prototype.getShowVatData = function(element)
    {
        return {
            class: OrderTableOptions.ORDER_TABLE_SHOW_VAT_ID,
            selected: element.getShowVat(),
            id: OrderTableOptions.ORDER_TABLE_SHOW_VAT_ID,
            name: 'show-vat',
            label: 'Show VAT'
        };
    };

    OrderTableOptions.prototype.getShowVatId = function()
    {
        return OrderTableOptions.ORDER_TABLE_SHOW_VAT_ID;
    };

    return new OrderTableOptions();
});

