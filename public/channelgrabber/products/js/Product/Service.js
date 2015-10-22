define([
    'cg-mustache',
    'Product/DomListener/Search',
    'Product/DomListener/Pagination',
    'Product/DomListener/StockMode',
    'Product/DomListener/TaxRate',
    'Product/Filter/Mapper',
    'Product/Storage/Ajax',
    'DomManipulator',
    'Variation/DomListener',
    'BulkActionAbstract',
    'DeferredQueue'
], function (
    CGMustache,
    SearchDomListener,
    PaginationDomListener,
    StockModeDomListener,
    TaxRateDomListener,
    productFilterMapper,
    productStorage,
    domManipulator,
    VariationDomListener,
    BulkActionAbstract,
    DeferredQueue
) {
    var Service = function (baseImgUrl)
    {
        var baseImgUrl;
        var deferredQueue;
        var searchDomListener;
        var paginationDomListener;
        var stockModeDomListener;
        var taxRateDomListener;

        this.getBaseImgUrl = function()
        {
            return baseImgUrl;
        };

        this.setBaseImgUrl = function(newBaseImgUrl)
        {
            baseImgUrl = newBaseImgUrl;
            return this;
        };

        this.getDeferredQueue = function()
        {
            return deferredQueue;
        };

        this.getSearchDomListener = function()
        {
            return searchDomListener;
        };

        var init = function()
        {
            deferredQueue = new DeferredQueue();
            searchDomListener = new SearchDomListener(this);
            paginationDomListener = new PaginationDomListener(this);
            stockModeDomListener = new StockModeDomListener(this);
            taxRateDomListener = new TaxRateDomListener(this);

            this.setBaseImgUrl(baseImgUrl)
                .refresh();
        };
        init.call(this);
    };

    Service.DOM_SELECTOR_PRODUCT_CONTAINER = '#products-list';
    Service.DOM_SELECTOR_LOADING_MESSAGE = '#products-loading-message';
    Service.DEFAULT_IMAGE_URL = '/noproductsimage.png';
    Service.DOM_SELECTOR_TAX_RATE = 'product-tax-rate-custom-select';
    Service.DOM_SELECTOR_PAGINATION = '#product-pagination';
    Service.STOCK_MODE_ID_PREFIX = 'product-stock-mode';

    Service.prototype.refresh = function(page)
    {
        var self = this;
        var filter = productFilterMapper.fromDom();
        if (page) {
            filter.setPage(page);
        }
        domManipulator.setCssValue(Service.DOM_SELECTOR_LOADING_MESSAGE, 'display','block');
        this.fetchProducts(filter, function (data) {
            var products = data.products;
            domManipulator.setCssValue(Service.DOM_SELECTOR_LOADING_MESSAGE, 'display','none');
            if (!products.length) {
                self.renderNoProduct();
                return;
            }
            self.getSearchDomListener().triggerProductsFetchedEvent(products);
            self.renderProducts(products, data.pagination);
        });
    };

    Service.prototype.fetchProducts = function(filter, callable)
    {
        return productStorage.fetchByFilter(filter, callable);
    };

    Service.prototype.renderProducts = function(products, pagination)
    {
        var self = this;
        this.fetchProductTemplates(function(templates)
        {
            var html = "";
            for (var index in products) {
                html += self.renderProduct(products[index], templates);
            }
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCT_CONTAINER, html);
            self.updatePagination(pagination);
            self.getSearchDomListener().triggerProductsRenderedEvent(products);
        });
    };

    Service.prototype.fetchProductTemplates = function(callable)
    {
        var productUrlMap = {
            checkbox: '/channelgrabber/zf2-v4-ui/templates/elements/checkbox.mustache',
            buttons: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            customSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
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
            callable(templates);
        });
    };

    Service.prototype.renderProduct = function(product, templates)
    {
        var checkbox = this.getCheckboxView(product, templates);
        var expandButton = '';
        var hasVariations = false;
        var taxRateCustomSelect = null;

        if (product['variationCount'] != undefined && product['variationCount']) {
            var productContent = this.getVariationView(product, templates);
            if (product['variationCount'] > 2) {
              expandButton = this.getExpandButtonView(product, templates);
            }
            hasVariations = true;
        } else {
            var productContent = this.getStockTableView(product, templates);
        }

        var statusLozenge = this.getStatusView(product, templates);
        var stockModesCustomSelect = this.getStockModesCustomSelectView(product, templates);
        if (product['taxRates']) {
            taxRateCustomSelect = this.getTaxRateCustomSelectView(product, templates);
        }
        var productView = CGMustache.get().renderTemplate(templates, {
            'title': product['name'],
            'sku': product['sku'],
            'id': product['id'],
            'eTag': product['eTag'],
            'image': this.getPrimaryImage(product['images']),
            'hasVariations': hasVariations
        }, 'product', {
            'productContent': productContent,
            'statusLozenge': statusLozenge,
            'expandButton': expandButton,
            'stockModeOptions': stockModesCustomSelect,
            'taxRateCustomSelect': taxRateCustomSelect,
            'checkbox': checkbox
        });
        return productView;
    };

    Service.prototype.getStockTableView = function(product, templates)
    {
        var stockLocations = "";
        if (typeof(product['stock']) != 'undefined' && typeof(product['stock']['locations']) != 'undefined') {
            for (var index in product['stock']['locations']) {
                stockLocations += this.getStockTableLineView(product, product['stock']['locations'][index], templates);
            }
        }
        var html = CGMustache.get().renderTemplate(templates, {'showStockLevel': (product.stockLevel !== null), 'stockModeDesc': product.stockModeDesc}, 'stockTable', {'stockLocations': stockLocations});
        return html;
    };

    Service.prototype.getStockTableLineView = function(product, location, templates)
    {
        var name = 'total-stock_' + product.id + '_' + location['id'];
        var quantityInlineText = this.getStockTotalView(name, location, templates);
        var available = location['onHand'] - location['allocated'];
        var showStockLevel = (product.stockLevel !== null);
        var stockLevel = null;
        if (showStockLevel) {
            stockLevel = this.getStockLevelView(product, templates);
        }
        return CGMustache.get().renderTemplate(templates, {
            'productId': product.id,
            'eTag': product.eTag,
            'available': available,
            'allocated': location['allocated'],
            'totalName': name,
            'showStockLevel': showStockLevel,
            'stockLocationId': location['id'],
            'stockLocETag': location['eTag']
        }, 'stockRow', {
            'total': quantityInlineText,
            'stockLevel': stockLevel
        });
    };

    Service.prototype.getStockTotalView = function(name, location, templates)
    {
        return CGMustache.get().renderTemplate(templates, {
            'value': location['onHand'],
            'id': name,
            'name': name,
            'class': 'product-stock-total',
            'type': 'number'
        }, 'inlineText', {});
    };

    Service.prototype.getStockLevelView = function(product, templates)
    {
        return CGMustache.get().renderTemplate(templates, {
            'id': 'product-stock-level-' + product.id,
            'value': product.stockLevel,
            'name': 'product[' + product.id + '][stockLevel]',
            'class': 'product-stock-level',
            'type': 'number'
        }, 'inlineText', {});
    };

    Service.prototype.getVariationView = function(product, templates)
    {
        var variations = "";
        var stockLocations = "";
        for (var index in product['variations']) {
            var variation = product['variations'][index];
            variations += this.getVariationLineView(templates, variation, product['attributeNames']);
            stockLocations += this.getStockTableLineView(variation, variation['stock']['locations'][0], templates);
        }
        var variationTable = CGMustache.get().renderTemplate(templates, {
            'attributes': product['attributeNames']
        }, 'variationTable', {'variations': variations});
        var stockTable = CGMustache.get().renderTemplate(templates, {'showStockLevel': (product.stockLevel !== null), 'stockModeDesc': product.stockModeDesc}, 'stockTable', {'stockLocations': stockLocations});
        var html = CGMustache.get().renderTemplate(templates, {}, 'variationStock', {
            'variationTable': variationTable,
            'stockTable': stockTable
        });
        return html;
    };

    Service.prototype.getVariationLineView = function(templates, variation, attributeNames)
    {
        var attributeValues = [];
        for (var attributeNameIndex in attributeNames) {
            if(!($).isEmptyObject(variation['attributeValues'][attributeNames[attributeNameIndex]])) {
                attributeValues.push(variation['attributeValues'][attributeNames[attributeNameIndex]]);
            } else {
                attributeValues.push('');
            }
        }

        return CGMustache.get().renderTemplate(templates, {
            'image': this.getPrimaryImage(variation['images']),
            'sku': variation['sku'],
            'attributes': attributeValues
        }, 'variationRow');
    };

    Service.prototype.getPrimaryImage = function(images)
    {
        return images.length > 0 ? images[0]['url'] : this.getBaseImgUrl() + Service.DEFAULT_IMAGE_URL;
    };

    Service.prototype.getExpandButtonView = function(product, templates)
    {
        return CGMustache.get().renderTemplate(templates, {
            'buttons': true,
            'id': 'product-variation-expand-button-' + product['id'],
            'class': VariationDomListener.CLASS_EXPAND_BUTTON + " " + VariationDomListener.CLASS_EXPAND_AJAX,
            'value': 'Expand Variations',
            'action': 'Contract Variations'
        }, 'buttons');
    };

    Service.prototype.getTaxRateCustomSelectView = function(product, templates)
    {
        var options = [];
        for(var taxRateId in product['taxRates']) {
            if(!product['taxRates'].hasOwnProperty(taxRateId)) {
                continue;
            }
            options.push({
                'title': product['taxRates'][taxRateId]['rate'] + '% (' + product['taxRates'][taxRateId]['name'] + ')',
                'value': taxRateId,
                'selected': product['taxRates'][taxRateId]['selected']
            });
        }

        return CGMustache.get().renderTemplate(templates, {
            'id': Service.DOM_SELECTOR_TAX_RATE + '-' + product['id'],
            'name': Service.DOM_SELECTOR_TAX_RATE + '-' + product['id'],
            'class': Service.DOM_SELECTOR_TAX_RATE,
            'title': 'VAT',
            'options': options
        }, 'customSelect');
    };

    Service.prototype.getStockModesCustomSelectView = function(product, templates)
    {
        return CGMustache.get().renderTemplate(templates, {
            'id': Service.STOCK_MODE_ID_PREFIX + '-' + product['id'],
            'name': 'product[' + product['id'] + '][stockMode]',
            'class': Service.STOCK_MODE_ID_PREFIX,
            'title': 'Stock',
            'options': product.stockModeOptions
        }, 'customSelect');
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
        var mustacheFormattedData = { 'listings' : [] };
        var accountId;
        for (var listing in product['listings']) {
            if (product['listings'].hasOwnProperty(listing)) {
                accountId = product['listings'][listing]['accountId'];
                mustacheFormattedData['listings'].push({
                    'status' : product['listings'][listing]['status'],
                    'channel' : product['accounts'][accountId]['displayName'],
                    'url' : product['listings'][listing]['url']
                });
            }
        };
        return mustacheFormattedData;
    };

    Service.prototype.getStatusView = function(product, templates)
    {
        var hasMultipleListings = false;
        if (product['listings'].length) {
            hasMultipleListings = true;
        }
        return CGMustache.get().renderTemplate(templates, {
            'listings': this.getStatusListingData(product)['listings'],
            'hasMultipleListings': hasMultipleListings,
            'decidedStatus': this.productStatusDecider(product)
        }, 'statusLozenge');
    };

    Service.prototype.productStatusDecider = function(product)
    {
        var statusPrecedence = {
            'inactive': 1,
            'active': 2,
            'pending': 3,
            'paused': 4,
            'error': 5
        };

        var status = 'inactive';
        for (var listing in product['listings']) {
            var listingStatus = product['listings'][listing]['status'];
            if(statusPrecedence[listingStatus] > statusPrecedence[status]) {
                status = listingStatus;
            }
        }
        return status;
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
        $(Service.DOM_SELECTOR_PAGINATION).parent().hide();
    };

    Service.prototype.updatePagination = function(pagination)
    {
        $(Service.DOM_SELECTOR_PAGINATION).trigger('update', [pagination]);
        $(Service.DOM_SELECTOR_PAGINATION).parent().show();
    };

    Service.prototype.saveTaxRate = function(sourceCustomSelect)
    {
        var productId = $(sourceCustomSelect).closest(".product-container").find("input[type=hidden][name='id']").val();
        var value = $(sourceCustomSelect).find("input[type=hidden][class='" + Service.DOM_SELECTOR_TAX_RATE + "']").val();

        if(productId === undefined || productId === '' || value === undefined || value === '') {
            return;
        }

        this.getDeferredQueue().queue(function() {
            return productStorage.saveTaxRate(productId, value, function(error, textStatus, errorThrown) {
                if(error === null) {
                    n.success('Product tax rate updated successfully');
                } else {
                    n.ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    Service.prototype.saveStockModeForProduct = function(productId, value)
    {
        var eTag = $('input[name="product[' + productId + '][eTag]"]').val();
        this.getDeferredQueue().queue(function() {
            return productStorage.saveStockMode(productId, value, eTag, function(response) {
                $('input[name="product[' + productId + '][eTag]"]').val(response.eTag);
                n.success('Product stock mode updated successfully');
            });
        });
    };

    Service.prototype.pageSelected = function(page)
    {
        this.refresh(page);
    };

    return Service;
});
