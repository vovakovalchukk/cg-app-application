define([
    'Product/Filter/Mapper',
    'Product/Storage/Ajax',
    'DomManipulator'
], function (
    productFilterMapper,
    productStorage,
    domManipulator
) {
    var Service = function () {
    };

    Service.PRODUCT_CONTAINER_ID = 'products-list';

    Service.prototype.renderProducts = function()
    {
        var filter = productFilterMapper.fromDom();
        var products = this.fetchProducts(filter);
        if (!products.length) {
            domManipulator.setHtml('#' + Service.PRODUCT_CONTAINER_ID, this.renderNoProduct());
            return;
        }
        var html = "";
        for (var product in products) {
            html += this.renderProduct(product);
        }
        domManipulator.setHtml('#' + Service.PRODUCT_CONTAINER_ID, html);
    };

    Service.prototype.fetchProducts = function(filter)
    {
        return productStorage.fetchFromFilter(filter);
    };

    Service.prototype.renderProduct = function(product)
    {
        var aliasUrlMap = {
            text: '/channelgrabber/zf2-v4-ui/templates/elements/text.mustache',
            deleteButton: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            multiSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select-group.mustache',
            multiSelectExpanded: '/channelgrabber/zf2-v4-ui/templates/elements/multiselectexpanded.mustache',
            alias: '/channelgrabber/settings/template/ShippingAlias/alias.mustache'
        };
        CGMustache.get().fetchTemplates(aliasUrlMap, function(templates, cgmustache)
        {
            var aliasNo = self.getAndIncrementAliasNo();
            var text = cgmustache.renderTemplate(templates, {'name': "alias-name-" + aliasNo}, "text");
            var deleteButton = cgmustache.renderTemplate(templates, {
                'buttons' : true,
                'value' : "Delete",
                'id' : "deleteButton-" + aliasNo
            }, "deleteButton");

            var multiSelect = cgmustache.renderTemplate(templates, {'options': methodCollection.getItems(),
                'name': 'aliasMultiSelect-' + aliasNo}, "multiSelect");
            var multiSelectExpanded = cgmustache.renderTemplate(templates, {}, "multiSelectExpanded", {'multiSelect' : multiSelect});
            var alias = cgmustache.renderTemplate(templates, {'id' : 'shipping-alias-new-' + aliasNo}, "alias", {
                'multiSelectExpanded' : multiSelectExpanded,
                'deleteButton' : deleteButton,
                'text' : text
            });

            if ($(DomManipulator.DOM_SELECTOR_ALIAS_NONE).is(':visible')) {
                $(DomManipulator.DOM_SELECTOR_ALIAS_NONE).hide();
            }
            self.prepend(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER, alias);
            self.updateAllAliasMethodCheckboxes();
        });
    };

    Service.prototype.renderNoProduct = function()
    {

    };

    return new Service();
});