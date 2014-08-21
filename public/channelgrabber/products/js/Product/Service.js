define([
    'Product/Filter/Mapper',
    'Product/Storage/Ajax',
    'DomManipulator',
    'Variation/DomListener'
], function (
    productFilterMapper,
    productStorage,
    domManipulator,
    variationDomListener
) {
    var Service = function ()
    {
        var baseUrl;
        this.getBaseUrl = function()
        {
            return baseUrl;
        };

        this.setBaseUrl = function(newBaseUrl)
        {
            baseUrl = newBaseUrl;
        };
    };

    Service.DOM_SELECTOR_PRODUCT_CONTAINER = '#products-list';
    Service.DEFAULT_IMAGE_URL = '/noproductsimage.png';

    Service.prototype.init = function(baseUrl)
    {
        this.setBaseUrl(baseUrl);
        this.refresh();
    };

    Service.prototype.refresh = function()
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
            buttons: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            inlineText: '/channelgrabber/zf2-v4-ui/templates/elements/inline-text.mustache',
            variationTable: '/channelgrabber/products/template/product/variationTable.mustache',
            variationRow: '/channelgrabber/products/template/product/variationRow.mustache',
            variationStock: '/channelgrabber/products/template/product/variationStock.mustache',
            stockTable: '/channelgrabber/products/template/product/stockTable.mustache',
            stockRow: '/channelgrabber/products/template/product/stockRow.mustache',
            product: '/channelgrabber/products/template/elements/product.mustache'
        };
        CGMustache.get().fetchTemplates(productUrlMap, function(templates, cgmustache)
        {
            var html = "";
            for (var index in products) {
                html += self.renderProduct(products[index], templates, cgmustache);
            }
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCT_CONTAINER, html);
        });
    };

    Service.prototype.renderProduct = function(product, templates, cgmustache)
    {
        var expandButton = '';
        if (product['variations'] != undefined && product['variations'].length) {
            var productContent = this.getVariationView(product, templates, cgmustache);
            expandButton = this.getExpandButtonView(product, templates, cgmustache);
        } else {
            var productContent = this.getStockTableView(product, templates, cgmustache);
        }
        var productView = cgmustache.renderTemplate(templates, {
            'title': product['name'],
            'sku': product['sku'],
            'status': 'active',
            'id': product['id'],
            'image': this.getPrimaryImage(product['images'])
        }, 'product', {'productContent': productContent, 'expandButton': expandButton});
        return productView;
    };

    Service.prototype.getStockTableView = function(product, templates, cgmustache)
    {
        var stockLocations = "";
        for (var index in product['stock']['locations']) {
            stockLocations += this.getStockTableLineView(product['stock']['locations'][index], templates, cgmustache);
        }
        var html = cgmustache.renderTemplate(templates, {}, 'stockTable', {'stockLocations': stockLocations});
        return html;
    };
    
    Service.prototype.getStockTableLineView = function(location, templates, cgmustache)
    {
        var name = 'total-stock-' + location['id'];
        var quantityInlineText = cgmustache.renderTemplate(templates, {
            'value': location['onHand'],
            'name': name,
            'type': 'number'
        }, 'inlineText', {});
        var available = location['onHand'] - location['allocated'];
        return cgmustache.renderTemplate(templates, {
            'available': available,
            'allocated': location['allocated'],
            'totalName': name,
            'stockLocationId': location['id'],
            'eTag': location['eTag']
        }, 'stockRow', {'total': quantityInlineText});
    };

    Service.prototype.getVariationView = function(product, templates, cgmustache)
    {
        var variations = "";
        var stockLocations = "";
        for (var index in product['variations']) {
            var variation = product['variations'][index];
            var attributeValues = [];
            for (var attributeNameIndex in product['attributeNames']) {
                attributeValues.push(variation['attributeValues'][product['attributeNames'][attributeNameIndex]]);
            }
            variations += cgmustache.renderTemplate(templates, {
                'image': this.getPrimaryImage(variation['images']),
                'sku': variation['sku'],
                'attributes': attributeValues
            }, 'variationRow');
            stockLocations += this.getStockTableLineView(variation['stock']['locations'][0], templates, cgmustache);
        }
        var variationTable = cgmustache.renderTemplate(templates, {
            'attributes': product['attributeNames']
        }, 'variationTable', {'variations': variations});
        var stockTable = cgmustache.renderTemplate(templates, {}, 'stockTable', {'stockLocations': stockLocations});
        var html = cgmustache.renderTemplate(templates, {}, 'variationStock', {
            'variationTable': variationTable,
            'stockTable': stockTable
        });
        return html;
    };

    Service.prototype.getPrimaryImage = function(images)
    {
        return images.length > 0 ? images[0]['url'] : this.getBaseUrl() + Service.DEFAULT_IMAGE_URL;
    };

    Service.prototype.getExpandButtonView = function(product, templates, cgmustache)
    {
        return cgmustache.renderTemplate(templates, {
            'buttons': true,
            'id': 'product-variation-expand-button-' + product['id'],
            'class': variationDomListener.getClassExpandButton(),
            'value': 'Expand Variations',
            'action': 'Contract Variations'
        }, 'buttons');
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