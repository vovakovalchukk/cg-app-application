import reducerCreator from 'Common/Reducers/creator';
    var initialState = {};

    var getDetailForProduct = function(detailName, productDetails, variationData) {
        if (productDetails[detailName]) {
            return productDetails[detailName];
        }
        var variation = variationData.find(function(variation) {
           return !!variation.details[detailName];
        });

        return variation ? variation.details[detailName] : '';
    };

    var formatChannelDefaultValues = function(data) {
        var account,
            defaults = {};

        for (var accountId in data.accountDefaultSettings) {
            if (data.accountsData[accountId].channel !== 'ebay') {
                continue;
            }

            if (!defaults.ebay) {
                account = data.accountDefaultSettings[accountId];
                defaults.ebay = {
                    dispatchTimeMax: account.listingDispatchTime,
                    epid: data.selectedProductDetails.epid ? data.selectedProductDetails.epid : null,
                    epidAccountId: data.selectedProductDetails.epidAccountId ? data.selectedProductDetails.epidAccountId : null
                };
            }
        }

        return defaults;
    };

    var formatCategoryDefaultValues = function(data) {
        var defaults = {},
            account = {};

        for (var accountId in data.accountDefaultSettings) {
            if (data.accountsData[accountId].channel !== 'ebay') {
                continue;
            }
            account = data.accountDefaultSettings[accountId];
            break;
        }

        for (var templateId in data.categoryTemplates) {
            var template = data.categoryTemplates[templateId];
            for (var categoryTemplateAccountId in template.accounts) {
                var accountCategory = template.accounts[categoryTemplateAccountId];

                if (accountCategory.channel !== 'ebay') {
                    continue;
                }
                let defaultsForCategory = {};
                if (account.listingDuration) {
                    defaultsForCategory.listingDuration = account.listingDuration;
                }
                defaults[categoryTemplateAccountId] = defaultsForCategory;
            }
        }

        return defaults;
    };

    var getProductIdentifiers = function(variationData, selectedProductDetails) {
        var identifiers = {};
        variationData.forEach(function(variation) {
            identifiers[variation.id] = {
                ean: variation.details.ean ? variation.details.ean : selectedProductDetails.ean,
                upc: variation.details.upc ? variation.details.upc : selectedProductDetails.upc,
                isbn: variation.details.isbn ? variation.details.isbn : selectedProductDetails.isbn,
                mpn: variation.details.mpn ? variation.details.mpn : selectedProductDetails.mpn,
                barcodeNotApplicable: variation.details.barcodeNotApplicable !== null ? variation.details.barcodeNotApplicable : selectedProductDetails.barcodeNotApplicable
            };
        });
        return identifiers;
    };

    export default reducerCreator(initialState, {
        "LOAD_INITIAL_VALUES": function(state, action) {
            var product = action.payload.product,
                variationData = action.payload.variationData,
                selectedAccounts = action.payload.selectedAccounts,
                selectedProductDetails = action.payload.selectedProductDetails;

            var dimensions = {};
            variationData.map(function(variation) {
                dimensions[variation.id] = {
                    length: variation.details.length,
                    width: variation.details.width,
                    height: variation.details.height,
                    weight: variation.details.weight
                };
            });

            var prices = {};
            variationData.map(function(variation) {
                var pricesForVariation = {};
                selectedAccounts.map(function(accountId) {
                    var price = parseFloat(variation.details.price).toFixed(2);
                    pricesForVariation[accountId] = isNaN(price) ? null : price;
                });
                prices[variation.id] = pricesForVariation;
            });

            var productDetails = product.detail ? product.details : {};

            var skus = {};
            variationData.map(function(variation) {
                skus[variation.id] = variation.sku;
            });

            return {
                title: selectedProductDetails.title ? selectedProductDetails.title : product.name,
                description: getDetailForProduct('description', productDetails, variationData),
                condition: getDetailForProduct('condition', productDetails, variationData),
                brand: selectedProductDetails.brand ? selectedProductDetails.brand : getDetailForProduct('brand', productDetails, variationData),
                identifiers: getProductIdentifiers(variationData, selectedProductDetails),
                dimensions: dimensions,
                prices: prices,
                channel: formatChannelDefaultValues(action.payload),
                category: formatCategoryDefaultValues(action.payload),
                skus: skus
            };
        }
    });

