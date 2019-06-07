define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/LinkedProducts',
    'cg-mustache'
], function(
    InspectorAbstract,
    linkedProductsDomListener,
    CGMustache
) {
    var LinkedProducts = function() {
        InspectorAbstract.call(this);

        this.setId('linkedProducts');
        this.setInspectedAttributes(['linkedProductsDisplay']);
    };

    LinkedProducts.LINKED_PRODUCTS_INSPECTOR_SELECTOR = '#linkedProducts-inspector';
    LinkedProducts.LINKED_PRODUCTS_INSPECTOR_DELETE_ID = 'linked-products-delete-button';
    LinkedProducts.LINKED_PRODUCTS_DISPLAY_SETTING_SELECT_ID = 'linked-products-display-setting-select';

    LinkedProducts.prototype = Object.create(InspectorAbstract.prototype);

    LinkedProducts.prototype.hide = function() {
        this.getDomManipulator().render(LinkedProducts.LINKED_PRODUCTS_INSPECTOR_SELECTOR, "");
    };

    LinkedProducts.prototype.getLinkedProductsDisplaySettingSelect = function() {
        return LinkedProducts.LINKED_PRODUCTS_DISPLAY_SETTING_SELECT_ID;
    };

    LinkedProducts.prototype.showForElement = function(element, template, service) {
        let self = this;
        const templateUrlMap = {
            linkedProducts: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/linkedProducts.mustache',
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, (templates, cgmustache) => {
            const displaySelect = cgmustache.renderTemplate(templates,
                self.getSelectFields(element),
                "select"
            );
            const linkedProducts = cgmustache.renderTemplate(templates,
                {'type': element.getType()},
                "linkedProducts", {'displaySelect': displaySelect}
            );
            const collapsibleInspector = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Linked Products',
                'id': 'linked-products-collapsible'
            }, "collapsible", {'content': linkedProducts});

            self.getDomManipulator().render(LinkedProducts.LINKED_PRODUCTS_INSPECTOR_SELECTOR, collapsibleInspector);
            linkedProductsDomListener.init(self, template, element, service);
        });
    };

    LinkedProducts.prototype.getSelectFields = function(element) {
        let options = [
            {title: 'Purchased Sku', value: 'Purchased Sku', id: 'Purchased Sku'},
            {title: 'Components Only', value: 'Components Only', id: 'Components Only'},
            {title: 'Purchased SKU + Components', value: 'Purchased SKU + Components', id: 'Purchased SKU + Components'}
        ];
        const savedOptionIndex = options.findIndex(option => option.value === element.getLinkedProductsDisplay());
        options[savedOptionIndex].selected = true;
        return {
            initialTitle: 'Display Setting',
            id: LinkedProducts.LINKED_PRODUCTS_DISPLAY_SETTING_SELECT_ID,
            name: LinkedProducts.LINKED_PRODUCTS_DISPLAY_SETTING_SELECT_ID,
            options: options
        };
    };

    LinkedProducts.prototype.removeElement = function(template, element) {
        template.removeElement(element);
    };

    LinkedProducts.prototype.getHeadingInspectorDeleteId = function() {
        return LinkedProducts.LINKED_PRODUCTS_INSPECTOR_DELETE_ID;
    };

    LinkedProducts.prototype.setLinkedProductsDisplay = function(element, optionId) {
        element.setLinkedProductsDisplay(optionId);
    };

    return new LinkedProducts();
});