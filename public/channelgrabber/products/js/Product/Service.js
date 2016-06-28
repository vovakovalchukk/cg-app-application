define([
    'cg-mustache',
    'Product/DomListener/Search',
    'Product/DomListener/Pagination',
    'Product/DomListener/Product',
    'Product/Filter/Mapper',
    'Product/Storage/Ajax',
    'DomManipulator',
    'Variation/DomListener',
    'BulkActionAbstract',
    'DeferredQueue',
    'tooltip'
], function (
    CGMustache,
    SearchDomListener,
    PaginationDomListener,
    ProductDomListener,
    productFilterMapper,
    productStorage,
    domManipulator,
    VariationDomListener,
    BulkActionAbstract,
    DeferredQueue,
    Tooltip
) {
    var Service = function (baseImgUrl, isAdmin, searchTerm)
    {
        var baseImgUrl;
        var deferredQueue;
        var searchDomListener;
        var paginationDomListener;
        var productDomListener;
        var templates;
        var tooltip;

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

        this.getTemplates = function()
        {
            return templates;
        };

        this.setTemplates = function(newTemplates)
        {
            templates = newTemplates;
            return this;
        };

        this.isAdmin = function()
        {
            return isAdmin;
        };

        var init = function()
        {
            deferredQueue = new DeferredQueue();
            searchDomListener = new SearchDomListener(this);
            paginationDomListener = new PaginationDomListener(this);
            productDomListener = new ProductDomListener(this);
            tooltip = new Tooltip(
                Service.DOM_SELECTOR_PRODUCTS_CONTAINER,
                Service.DOM_SELECTOR_TOOLTIP_STATUS,
                function() {
                    var status = $(this);
                    tooltip.setClassName(status.text());
                    return status.attr("title");
                }
            );

            this.setBaseImgUrl(baseImgUrl)
                .setSearchTerm(searchTerm)
                .refresh();
        };
        init.call(this);
    };

    Service.DOM_SELECTOR_PRODUCTS_CONTAINER = '#products-list';
    Service.DOM_SELECTOR_PRODUCT_CONTAINER_PREFIX = '#product-container-';
    Service.DOM_SELECTOR_LOADING_MESSAGE = '#products-loading-message';
    Service.DEFAULT_IMAGE_URL = '/noproductsimage.png';
    Service.DOM_SELECTOR_TAX_RATE = 'product-tax-rate-custom-select';
    Service.DOM_SELECTOR_PAGINATION = '#product-pagination';
    Service.STOCK_MODE_ID_PREFIX = 'product-stock-mode';
    Service.DOM_SELECTOR_STOCK_LEVEL_COL = '.product-stock-level-col';
    Service.DOM_SELECTOR_STOCK_TABLE = '.stock-table';
    Service.DOM_SELECTOR_STOCK_LEVEL_INPUT = '.product-stock-level';
    Service.DOM_SELECTOR_TOOLTIP_STATUS = '.product-listing-status-dropdown .status';

    Service.prototype.setSearchTerm = function(searchTerm)
    {
        if (!searchTerm) {
            return this;
        }
        $(SearchDomListener.SELECTOR_INPUT).val(searchTerm);
        return this;
    };

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
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCTS_CONTAINER, html);
            self.updatePagination(pagination);
            self.getSearchDomListener().triggerProductsRenderedEvent(products);
        });
    };

    Service.prototype.fetchProductTemplates = function(callable)
    {
        if (this.getTemplates()) {
            callable(this.getTemplates());
            return;
        }
        var self = this;
        var productUrlMap = {
            checkbox: '/channelgrabber/zf2-v4-ui/templates/elements/checkbox.mustache',
            buttons: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            customSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            inlineText: '/channelgrabber/zf2-v4-ui/templates/elements/inline-text.mustache',
            variationTable: '/channelgrabber/products/template/product/variationTable.mustache',
            variationRow: '/channelgrabber/products/template/product/variationRow.mustache',
            variationContent: '/channelgrabber/products/template/product/variationContent.mustache',
            simpleContent: '/channelgrabber/products/template/product/simpleContent.mustache',
            detailsTable: '/channelgrabber/products/template/product/detailsTable.mustache',
            detailsRow: '/channelgrabber/products/template/product/detailsRow.mustache',
            stockTable: '/channelgrabber/products/template/product/stockTable.mustache',
            stockRow: '/channelgrabber/products/template/product/stockRow.mustache',
            stockLevelHeader: '/channelgrabber/products/template/product/stockLevelHeader.mustache',
            stockLevelCell: '/channelgrabber/products/template/product/stockLevelCell.mustache',
            product: '/channelgrabber/products/template/elements/product.mustache',
            statusLozenge: '/channelgrabber/products/template/elements/statusLozenge.mustache'
        };
        CGMustache.get().fetchTemplates(productUrlMap, function(templates)
        {
            self.setTemplates(templates);
            callable(templates);
        });
    };

    Service.prototype.renderProduct = function(product, templates)
    {
        var checkbox = this.getCheckboxView(product, templates);
        var expandButton = '';
        var hasVariations = false;
        var taxRateCustomSelects = '';

        if (product['variationCount'] != undefined && product['variationCount']) {
            var productContent = this.getVariationView(product, templates);
            if (product['variationCount'] > 2) {
              expandButton = this.getExpandButtonView(product, templates);
            }
            hasVariations = true;
        } else {
            var productContent = this.getSimpleView(product, templates);
        }

        var statusLozenge = this.getStatusView(product, templates);
        var stockModesCustomSelect = this.getStockModesCustomSelectView(product, templates);
        if (product['taxRates']) {
            var showCodeInLabel = (Object.keys(product['taxRates']).length > 1);
            for (var memberState in product['taxRates']) {
                if (product['taxRates'].hasOwnProperty(memberState)) {
                    var taxSelectView = this.getTaxRateCustomSelectView(product, templates, memberState, showCodeInLabel);
                    taxRateCustomSelects += (taxSelectView ? taxSelectView : "");
                }
            }
        }
        var productView = CGMustache.get().renderTemplate(templates, {
            'title': product['name'],
            'sku': product['sku'],
            'id': product['id'],
            'eTag': product['eTag'],
            'image': this.getPrimaryImage(product['images']),
            'hasVariations': hasVariations,
            'taxRateCustomSelects': taxRateCustomSelects,
            'isAdmin': this.isAdmin()
        }, 'product', {
            'productContent': productContent,
            'statusLozenge': statusLozenge,
            'expandButton': expandButton,
            'stockModeOptions': stockModesCustomSelect,
            'checkbox': checkbox
        });
        return productView;
    };

    Service.prototype.getDetailsTableView = function(product, templates)
    {
        var details = this.getDetailsTableLineView(product, templates);
        return this.renderDetailsTableView(product, details, templates);
    };

    Service.prototype.getDetailsTableLineView = function(product, templates)
    {
        return CGMustache.get().renderTemplate(
            templates,
            product['details'],
            'detailsRow',
            {
                weight: this.getDetailView(templates, product['details'], 'weight'),
                height: this.getDetailView(templates, product['details'], 'height'),
                width: this.getDetailView(templates, product['details'], 'width'),
                length: this.getDetailView(templates, product['details'], 'length')
            }
        );
    };

    Service.prototype.getDetailView = function(templates, details, detail, defaultValue)
    {
        defaultValue = defaultValue ? defaultValue : '';
        return CGMustache.get().renderTemplate(
            templates,
            {
                'value': details && details.hasOwnProperty(detail) ? details[detail] : defaultValue,
                'name': detail,
                'class': 'product-detail',
                'type': 'number',
                'step': '0.1'
            },
            'inlineText'
        );
    };

    Service.prototype.renderDetailsTableView = function(product, details, templates)
    {
        return CGMustache.get().renderTemplate(templates, {}, 'detailsTable', {'details': details});
    };

    Service.prototype.getStockTableView = function(product, templates)
    {
        var stockLocations = "";
        if (typeof(product['stock']) != 'undefined' && typeof(product['stock']['locations']) != 'undefined') {
            for (var index in product['stock']['locations']) {
                stockLocations += this.getStockTableLineView(product, product['stock']['locations'][index], templates);
            }
        }
        return this.renderStockTableView(product, stockLocations, templates);
    };

    Service.prototype.renderStockTableView = function(product, stockLocations, templates)
    {
        var stockLevelHeader = null;
        if (product.stockLevel !== null) {
            stockLevelHeader = this.getStockLevelHeader(product.stockModeDesc, templates);
        }
        var html = CGMustache.get().renderTemplate(templates, {}, 'stockTable', {'stockLevelHeader': stockLevelHeader, 'stockLocations': stockLocations});
        return html;
    };

    Service.prototype.getStockTableLineView = function(product, location, templates)
    {
        var name = 'total-stock_' + product.id + '_' + location['id'];
        var quantityInlineText = this.getStockTotalView(name, location, templates);
        var available = location['onHand'] - Math.max(location['allocated'], 0);
        var showStockLevel = (product.stockLevel !== null);
        var stockLevelCell = null;
        if (showStockLevel) {
            stockLevelCell = this.getStockLevelCell(product, templates);
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
            'stockLevelCell': stockLevelCell
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

    Service.prototype.getStockLevelHeader = function(stockModeDesc, templates)
    {
        return CGMustache.get().renderTemplate(templates, {stockModeDesc: stockModeDesc}, 'stockLevelHeader', {});
    };

    Service.prototype.getStockLevelCell = function(product, templates)
    {
        var disabled = this.shouldDisableStockLevel(product.stockMode, product.stockModeDefault);
        var stockLevel = CGMustache.get().renderTemplate(templates, {
            'id': Service.STOCK_LEVEL_INPUT_ID_PREFIX + product.id,
            'value': product.stockLevel,
            'name': 'product[' + product.id + '][stockLevel]',
            'class': 'product-stock-level',
            'disabled': disabled,
            'type': 'number'
        }, 'inlineText', {});
        return CGMustache.get().renderTemplate(templates, {id: product.id}, 'stockLevelCell', {stockLevel: stockLevel});
    };

    Service.prototype.shouldDisableStockLevel = function(stockMode, stockModeDefault)
    {
        return (stockMode == null && stockModeDefault != null && stockModeDefault != 'all');
    };

    Service.prototype.getSimpleView = function(product, templates)
    {
        var detailsTable = this.getDetailsTableView(product, templates);
        var stockTable = this.getStockTableView(product, templates);
        return CGMustache.get().renderTemplate(templates, {}, 'simpleContent', {
            'detailsTable': detailsTable,
            'stockTable': stockTable
        });
    };

    Service.prototype.getVariationView = function(product, templates)
    {
        var variations = "";
        var details = "";
        var stockLocations = "";
        for (var index in product['variations']) {
            var variation = product['variations'][index];
            variations += this.getVariationLineView(templates, variation, product['attributeNames']);
            details += this.getDetailsTableLineView(variation, templates);
            stockLocations += this.getStockTableLineView(variation, variation['stock']['locations'][0], templates);
        }
        var variationTable = CGMustache.get().renderTemplate(templates, {
            'attributes': product['attributeNames']
        }, 'variationTable', {'variations': variations});
        var detailsTable = this.renderDetailsTableView(product, details, templates);
        var stockTable = this.renderStockTableView(product, stockLocations, templates);
        var html = CGMustache.get().renderTemplate(templates, {}, 'variationContent', {
            'variationTable': variationTable,
            'detailsTable': detailsTable,
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

    Service.prototype.getTaxRateCustomSelectView = function(product, templates, memberState, showCodeInLabel)
    {
        var options = [];
        for(var taxRateId in product['taxRates'][memberState]) {
            if(!product['taxRates'][memberState].hasOwnProperty(taxRateId)) {
                continue;
            }
            var formattedRate = parseFloat(product['taxRates'][memberState][taxRateId]['rate']);
            options.push({
                'title': formattedRate + '% (' + product['taxRates'][memberState][taxRateId]['name'] + ')',
                'value': taxRateId,
                'selected': product['taxRates'][memberState][taxRateId]['selected']
            });
        }

        var label = 'VAT';
        if (showCodeInLabel) {
            label = memberState + ' ' + label;
        }
        var html = CGMustache.get().renderTemplate(templates, {
            'id': Service.DOM_SELECTOR_TAX_RATE + '-' + product['id'] + '-' + memberState,
            'name': Service.DOM_SELECTOR_TAX_RATE + '-' + product['id'] + '-' + memberState,
            'class': Service.DOM_SELECTOR_TAX_RATE,
            'title': label,
            'options': options
        }, 'customSelect');
        return '<div class="tax-rate-custom-select-holder">' + html + '</div>';
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
                    'message' : product['listings'][listing]['message'],
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
            'ended': 1,
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
            domManipulator.setHtml(Service.DOM_SELECTOR_PRODUCTS_CONTAINER, html);
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
        var memberState = $(sourceCustomSelect).attr('id').split('-').pop();

        this.getDeferredQueue().queue(function() {
            return productStorage.saveTaxRate(productId, value, memberState, function(error, textStatus, errorThrown) {
                if(error === null) {
                    n.success('Product tax rate updated successfully');
                } else {
                    n.ajaxError(error, textStatus, errorThrown);
                }
            });
        });
    };

    Service.prototype.stockModeUpdated = function(productId, stockMode, stockModeDefault, stockModeDesc, stockLevel)
    {
        var showStockLevel = (stockLevel !== null);
        if (!showStockLevel) {
            $(Service.DOM_SELECTOR_PRODUCT_CONTAINER_PREFIX + productId + ' ' + Service.DOM_SELECTOR_STOCK_LEVEL_COL).remove();
            return;
        }
        var stockLevelHeader = $(Service.DOM_SELECTOR_PRODUCT_CONTAINER_PREFIX + productId + ' th' + Service.DOM_SELECTOR_STOCK_LEVEL_COL);
        if (stockLevelHeader.length > 0) {
            stockLevelHeader.html(stockModeDesc);
            this.updateStockLevelColumnForProduct(productId, stockMode, stockModeDefault, stockLevel);
            return;
        }
        this.addStockLevelColumnToProduct(productId, stockMode, stockModeDefault, stockModeDesc, stockLevel);
    };

    Service.prototype.updateStockLevelColumnForProduct = function(productId, stockMode, stockModeDefault, stockLevel)
    {
        var productContainer = $(Service.DOM_SELECTOR_PRODUCT_CONTAINER_PREFIX + productId);
        productContainer.find(Service.DOM_SELECTOR_STOCK_LEVEL_INPUT).val(stockLevel);
        var disable = this.shouldDisableStockLevel(stockMode, stockModeDefault);
        if (disable) {
            productContainer.find(Service.DOM_SELECTOR_STOCK_LEVEL_INPUT).attr('disabled', 'disabled');
            productContainer.find(Service.DOM_SELECTOR_STOCK_LEVEL_INPUT).closest('.submit-input').addClass('disabled');
        } else {
            productContainer.find(Service.DOM_SELECTOR_STOCK_LEVEL_INPUT).removeAttr('disabled');
            productContainer.find(Service.DOM_SELECTOR_STOCK_LEVEL_INPUT).closest('.submit-input').removeClass('disabled');
        }
        return this;
    };

    Service.prototype.addStockLevelColumnToProduct = function(productId, stockMode, stockModeDefault, stockModeDesc, stockLevel)
    {
        var self = this;
        this.fetchProductTemplates(function(templates)
        {
            var header = self.getStockLevelHeader(stockModeDesc, templates);
            $(Service.DOM_SELECTOR_PRODUCT_CONTAINER_PREFIX + productId + ' ' + Service.DOM_SELECTOR_STOCK_TABLE + ' thead tr').append(header);
            $(Service.DOM_SELECTOR_PRODUCT_CONTAINER_PREFIX + productId + ' ' + Service.DOM_SELECTOR_STOCK_TABLE + ' tbody tr').each(function()
            {
                var row = this;
                var productId = $(row).data('productId');
                var stockLevelCell = self.getStockLevelCell({
                    id: productId, stockLevel: stockLevel, stockMode: stockMode, stockModeDefault: stockModeDefault
                }, templates);
                $(row).append(stockLevelCell);
            });
        });
    };

    Service.prototype.pageSelected = function(page)
    {
        this.refresh(page);
    };

    return Service;
});
