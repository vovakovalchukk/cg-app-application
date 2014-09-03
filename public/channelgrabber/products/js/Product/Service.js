define([
    'cg-mustache',
    'Product/Filter/Mapper',
    'Product/Storage/Ajax',
    'DomManipulator',
    'Variation/DomListener',
    'BulkActionAbstract'
], function (
    CGMustache,
    productFilterMapper,
    productStorage,
    domManipulator,
    variationDomListener,
    BulkActionAbstract
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
        console.log("Satr2");
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
            checkbox: '/channelgrabber/zf2-v4-ui/templates/elements/checkbox.mustache',
            buttons: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            inlineText: '/channelgrabber/zf2-v4-ui/templates/elements/inline-text.mustache',
            variationTable: '/channelgrabber/products/template/product/variationTable.mustache',
            variationRow: '/channelgrabber/products/template/product/variationRow.mustache',
            variationStock: '/channelgrabber/products/template/product/variationStock.mustache',
            stockTable: '/channelgrabber/products/template/product/stockTable.mustache',
            stockRow: '/channelgrabber/products/template/product/stockRow.mustache',
            product: '/channelgrabber/products/template/elements/product.mustache',
            statusLozenge: '/channelgrabber/products/template/elements/statusLozenge.mustache'
        };
        CGMustache.get().fetchTemplates(productUrlMap, function(templates)
        {
            var html = "";
            console.log("2");
            for (var index in products) {
                html += self.renderProduct(products[index], templates);
            }
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCT_CONTAINER, html);
        });
    };

    Service.prototype.productStatusDecider = function(product)
    {
        var statusPrecedence = {
            'inactive': 1,
            'active': 2,
            'pending': 3,
            'paused': 4,
            'error': 5,
        };
        
        var status = status['inactive'];
        for (var listing in product['listings']) {
            var listingStatus = listing['status'];
            if(statusPrecedence[listingStatus] > statusPrecedence[status])
                status = listingStatus;
        }
        return status;
    }

    Service.prototype.renderProduct = function(product, templates)
    {
        var checkbox = this.getCheckboxView(product, templates);
        var expandButton = '';
        var hasVariations = false;
        var hasMultipleListings = false;
        console.log("pl " + product['listings'].length);
        if (product['variations'] != undefined && product['variations'].length) {
            var productContent = this.getVariationView(product, templates);
//            console.log("its " + statusLozenge);
            expandButton = this.getExpandButtonView(product, templates);
            hasVariations = true;
        } else {
            var productContent = this.getStockTableView(product, templates);
        }
        if (product['listings'].length) {
            hasMultipleListings = true;
            console.log("TREEEEEE"); 
        }
        else {
            console.log("else =-- " + product['listings'].length);
        }
        var statusLozenge = this.getStatusView(product, templates, hasMultipleListings);
        var productView = CGMustache.get().renderTemplate(templates, {
            'title': product['name'],
            'sku': product['sku'],
            'id': product['id'],
            'image': this.getPrimaryImage(product['images']),
            'hasVariations': hasVariations
        }, 'product', {
            'productContent': productContent,
            'statusLozenge': statusLozenge,
            'expandButton': expandButton,
            'checkbox': checkbox
        });
        return productView;
    };

    Service.prototype.getStockTableView = function(product, templates)
    {
        var stockLocations = "";
        for (var index in product['stock']['locations']) {
            stockLocations += this.getStockTableLineView(product['stock']['locations'][index], templates);
        }
        var html = CGMustache.get().renderTemplate(templates, {}, 'stockTable', {'stockLocations': stockLocations});
        return html;
    };
    
    Service.prototype.getStockTableLineView = function(location, templates)
    {
        var name = 'total-stock-' + location['id'];
        var quantityInlineText = CGMustache.get().renderTemplate(templates, {
            'value': location['onHand'],
            'name': name,
            'type': 'number'
        }, 'inlineText', {});
        var available = location['onHand'] - location['allocated'];
        return CGMustache.get().renderTemplate(templates, {
            'available': available,
            'allocated': location['allocated'],
            'totalName': name,
            'stockLocationId': location['id'],
            'eTag': location['eTag']
        }, 'stockRow', {'total': quantityInlineText});
    };

    Service.prototype.getVariationView = function(product, templates)
    {
        var variations = "";
        var stockLocations = "";
        for (var index in product['variations']) {
            var variation = product['variations'][index];
            var attributeValues = [];
            for (var attributeNameIndex in product['attributeNames']) {
                attributeValues.push(variation['attributeValues'][product['attributeNames'][attributeNameIndex]]);
            }
            variations += CGMustache.get().renderTemplate(templates, {
                'image': this.getPrimaryImage(variation['images']),
                'sku': variation['sku'],
                'attributes': attributeValues
            }, 'variationRow');
            stockLocations += this.getStockTableLineView(variation['stock']['locations'][0], templates);
        }
        var variationTable = CGMustache.get().renderTemplate(templates, {
            'attributes': product['attributeNames']
        }, 'variationTable', {'variations': variations});
        var stockTable = CGMustache.get().renderTemplate(templates, {}, 'stockTable', {'stockLocations': stockLocations});
        var html = CGMustache.get().renderTemplate(templates, {}, 'variationStock', {
            'variationTable': variationTable,
            'stockTable': stockTable
        });
        return html;
    };

    Service.prototype.getPrimaryImage = function(images)
    {
        return images.length > 0 ? images[0]['url'] : this.getBaseUrl() + Service.DEFAULT_IMAGE_URL;
    };

    Service.prototype.getExpandButtonView = function(product, templates)
    {
        return CGMustache.get().renderTemplate(templates, {
            'buttons': true,
            'id': 'product-variation-expand-button-' + product['id'],
            'class': variationDomListener.getClassExpandButton(),
            'value': 'Expand Variations',
            'action': 'Contract Variations'
        }, 'buttons');
    };

    Service.prototype.getCheckboxView = function(product, templates)
    {
        return CGMustache.get().renderTemplate(templates, {
            'id': 'product-checkbox-input-' + product['id'],
            'class': BulkActionAbstract.CLASS_CHECKBOX
        }, 'checkbox');
    };

    Service.prototype.getStatusListingData = function(product)
    {
        console.log("gSLD");
        console.log(product);
        var mustacheFormattedData = { 'listings' : [] };
        for (var listing in product['listings']) {
            console.log("listingstat " + listing['status']);
            if (product['listing'].hasOwnProperty(listing)) {

                mustacheFormattedData['listing'].push({
                    'status' : listing['status'],
                    'channel' : listing['channel']
                });
            }
        }
        return mustacheFormattedData;
    };

    Service.prototype.getStatusView = function(product, templates, hasMultipleListings)
    {
        console.log("gSV");
        if(hasMultipleListings) {
            console.log("listings====");
        }
        return CGMustache.get().renderTemplate(templates, {
            'listings': this.getStatusListingData(product),
            'hasMultipleListings': hasMultipleListings,
            'decidedStatus': 'active'
        }, 'statusLozenge');
    };

    Service.prototype.renderNoProduct = function()
    {
        var noProductsUrlMap = {
            noProduct: '/channelgrabber/products/template/elements/noProduct.Mustache'
        };
        CGMustache.get().fetchTemplates(noProductsUrlMap, function(templates)
        {
            var html = CGMustache.get().renderTemplate(templates, {}, 'noProduct');
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCT_CONTAINER, html);
        });
    };

    return new Service();
});