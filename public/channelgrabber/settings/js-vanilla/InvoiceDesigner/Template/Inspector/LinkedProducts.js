define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/LinkedProducts',
    'cg-mustache'
], function(
    InspectorAbstract,
    linkedProductsDomListener,
    CGMustache
) {
    var LinkedProducts = function()
    {
        InspectorAbstract.call(this);

        this.setId('linkedProducts');
        this.setInspectedAttributes(['linkedProductsDisplay']);
    };

    LinkedProducts.LINKED_PRODUCTS_INSPECTOR_SELECTOR = '#linkedProducts-inspector';
    LinkedProducts.LINKED_PRODUCTS_INSPECTOR_DELETE_ID = 'linked-products-delete-button';

    LinkedProducts.prototype = Object.create(InspectorAbstract.prototype);

    LinkedProducts.prototype.hide = function()
    {
        this.getDomManipulator().render(LinkedProducts.LINKED_PRODUCTS_INSPECTOR_SELECTOR, "");
    };

    LinkedProducts.prototype.showForElement = function(element, template, service)
    {
        var self = this;
        var templateUrlMap = {
            button: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            heading: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/heading.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var button = cgmustache.renderTemplate(templates, {'buttons' : true, 'value' : 'Delete', 'id' : LinkedProducts.LINKED_PRODUCTS_INSPECTOR_DELETE_ID}, "button");
            var heading = cgmustache.renderTemplate(templates, {'type' : element.getType()}, "heading", {'button': button});
            self.getDomManipulator().render(LinkedProducts.LINKED_PRODUCTS_INSPECTOR_SELECTOR, heading);
            linkedProductsDomListener.init(self, template, element, service);
        });
    };

    LinkedProducts.prototype.removeElement = function(template, element)
    {
        template.removeElement(element);
    };

    LinkedProducts.prototype.getHeadingInspectorDeleteId = function()
    {
        return LinkedProducts.LINKED_PRODUCTS_INSPECTOR_DELETE_ID;
    };

    return new LinkedProducts();
});