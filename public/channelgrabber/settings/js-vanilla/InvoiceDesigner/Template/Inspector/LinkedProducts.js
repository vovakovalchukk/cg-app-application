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

    LinkedProducts.prototype.getLinkedProductsDisplaySettingSelect = function(){
        return LinkedProducts.LINKED_PRODUCTS_DISPLAY_SETTING_SELECT_ID;
    };

    LinkedProducts.prototype.showForElement = async function(element, template, service) {
        let self = this;
        const templateUrlMap = {
            linkedProducts: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/linkedProducts.mustache',
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };

        await CGMustache.get().fetchTemplates(templateUrlMap, (templates, cgmustache) => {
            const displaySelect = cgmustache.renderTemplate(templates,
                self.getSelectFields(),
                "select"
            );
            const linkedProducts = cgmustache.renderTemplate(templates,
                {'type': element.getType()},
                "linkedProducts", { 'displaySelect': displaySelect}
            );
            const collapsibleInspector = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Linked Products',
                'id': 'linked-products-collapsible'
            }, "collapsible", {'content': linkedProducts});

            self.getDomManipulator().render(LinkedProducts.LINKED_PRODUCTS_INSPECTOR_SELECTOR, collapsibleInspector);
        });

        linkedProductsDomListener.init(self, template, element, service);
    };

    LinkedProducts.prototype.getSelectFields = function() {
        const options = [
            {title: 'dummy', value: 'dummy'},
            {title: 'dummy2', value: 'dummy2'}
        ];

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

    return new LinkedProducts();
});