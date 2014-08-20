define([
    'Product/Filter/Mapper',
    'Product/Storage/Ajax',
    'DomManipulator'
], function (
    productFilterMapper,
    productStorage,
    domManipulator
) {
    var Service = function ()
    {
    };

    Service.DOM_SELECTOR_PRODUCT_CONTAINER = '#products-list';

    Service.prototype.init = function()
    {
        var self = this;
        var filter = productFilterMapper.fromDom();
        this.fetchProducts(filter, function (products) {
            if (!products.length) {
                self.renderNoProduct();
                return;
            }
            self.renderProducts(products);
        });
    };

    Service.prototype.fetchProducts = function(filter, callable)
    {
        return productStorage.fetchByFilter(filter, callable);
    };

    Service.prototype.renderProducts = function(products)
    {
        var self = this;
        var productUrlMap = {
            /*text: '/channelgrabber/zf2-v4-ui/templates/elements/text.mustache',
            deleteButton: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            multiSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select-group.mustache',
            multiSelectExpanded: '/channelgrabber/zf2-v4-ui/templates/elements/multiselectexpanded.mustache', */
            inlineText: '/channelgrabber/zf2-v4-ui/templates/elements/inline-text.mustache',
            variationStock: '/channelgrabber/products/template/product/variationStock.mustache',
            stockTable: '/channelgrabber/products/template/product/stockTable.mustache',
            product: '/channelgrabber/products/template/elements/product.mustache'
        };
        CGMustache.get().fetchTemplates(productUrlMap, function(templates, cgmustache)
        {
            console.log(products);
            var html = "";
            for (var index in products) {
                html += self.renderProduct(products[index], templates, cgmustache);
            }
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCT_CONTAINER, html);
        });
    };

    Service.prototype.renderProduct = function(product, templates, cgmustache)
    {
        var productContent = product['variations'].length ? this.getVariationView(product, templates, cgmustache) : this.getStockTableView(product, templates, cgmustache);
        var productView = cgmustache.renderTemplate(templates, {
            'title': product['name'],
            'sku': product['sku'],
            'status': 'active'
        }, 'product', {'productContent': productContent});

        return productView;
    };

    Service.prototype.getStockTableView = function(product, templates, cgmustache)
    {
        var html;
        for (var index in product['stock']['locations']) {
            var name = 'total-stock-' + product['stock']['locations'][index]['id'];
            var quantityInlineText = cgmustache.renderTemplate(templates, {
                'value': product['stock']['locations'][index]['onHand'],
                'name': name,
                'type': 'number'
            });
            html += cgmustache.renderTemplate(templates, {
                'available': product['stock']['locations'][index]['available'],
                'allocated': product['stock']['locations'][index]['allocated'],
                'totalName': name,
                'stockLocationId': product['stock']['locations'][index]['id'],
                'eTag': product['stock']['locations'][index]['eTag'],
            }, 'product', {'total': quantityInlineText});
        }
        console.log(html);
        return html;
    };

    Service.prototype.getVariationView = function(product, templates, cgmustache)
    {
        return "";
    };

    Service.prototype.renderNoProduct = function()
    {
        var noProductsUrlMap = {
            noProduct: '/channelgrabber/products/template/elements/noProduct.mustache'
        };
        CGMustache.get().fetchTemplates(noProductsUrlMap, function(templates, cgmustache)
        {
            var html = cgmustache.renderTemplate(templates, {}, 'noProduct');
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCT_CONTAINER, html);
        });
    };

    return new Service();
});