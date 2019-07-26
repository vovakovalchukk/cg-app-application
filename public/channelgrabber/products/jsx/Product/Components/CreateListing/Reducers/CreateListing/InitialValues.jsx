import reducerCreator from 'Common/Reducers/creator';
import fieldService from 'Product/Components/CreateListing/Service/field';

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
                    epidAccountId: data.searchAccountId
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
            for (var accountId in template.accounts) {
                var accountCategory = template.accounts[accountId];

                if (accountCategory.channel !== 'ebay') {
                    continue;
                }
                let defaultsForCategory = {};
                if (account.listingDuration) {
                    defaultsForCategory.listingDuration = account.listingDuration;
                }
                defaults[accountCategory.categoryId] = defaultsForCategory;
            }
        }

        return defaults;
    };

    var getProductIdentifiers = function(variationData) {
        var identifiers = {};
        variationData.forEach(function(variation) {
            identifiers[fieldService.getVariationIdWithPrefix(variation.id)] = {
                ean: variation.details.ean,
                upc: variation.details.upc,
                isbn: variation.details.isbn,
                mpn: variation.details.mpn,
                barcodeNotApplicable: !!(variation.details.barcodeNotApplicable)
            };
        });
        return identifiers;
    };

    export default reducerCreator(initialState, {
        "LOAD_INITIAL_VALUES": function(state, action) {
            var product = action.payload.product,
                variationData = action.payload.variationData,
                selectedAccounts = action.payload.selectedAccounts;

            var dimensions = {};
            variationData.map(function(variation) {
                dimensions[fieldService.getVariationIdWithPrefix(variation.id)] = {
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
                prices[fieldService.getVariationIdWithPrefix(variation.id)] = pricesForVariation;
            });

            var productDetails = product.detail ? product.details : {};

            var skus = {};
            variationData.map(function(variation) {
                skus[fieldService.getVariationIdWithPrefix(variation.id)] = variation.sku;
            });

            return {
                title: product.name,
                description: getDetailForProduct('description', productDetails, variationData),
                condition: getDetailForProduct('condition', productDetails, variationData),
                brand: getDetailForProduct('brand', productDetails, variationData),
                identifiers: getProductIdentifiers(variationData, {}),
                dimensions: dimensions,
                prices: prices,
                channel: formatChannelDefaultValues(action.payload),
                category: formatCategoryDefaultValues(action.payload),
                skus: skus
            };
        },
        "CATEGORY_TEMPLATE_DEPENDANT_FIELD_VALUES_FETCHED": function(state, action) {
            return Object.assign({}, state, {
                category: formatCategoryDefaultValues(action.payload)
            });
        },
        "REVERT_TO_INITIAL_VALUES": function() {
            return {};
        },
        "ASSIGN_SEARCH_PRODUCT_TO_CG_PRODUCT": function(state, action) {
            let searchProduct = action.payload.searchProduct,
                productId = action.payload.cgProduct,
                identifier = state.identifiers[productId];

            return Object.assign({}, state, {
                identifiers: Object.assign({}, state.identifiers, {
                    [productId]: Object.assign({}, state.identifiers[productId], {
                        ean: identifier.ean ? identifier.ean : searchProduct.ean,
                        upc: identifier.upc ? identifier.upc : searchProduct.upc,
                        isbn: identifier.isbn ? identifier.isbn : searchProduct.isbn,
                        mpn: identifier.mpn ? identifier.mpn : searchProduct.mpn,
                        barcodeNotApplicable: identifier.barcodeNotApplicable
                    })
                })
            });
        },
        "CLEAR_SELECTED_PRODUCT": function(state, action) {
            let productId = action.payload.productId,
                variation = action.payload.variationData.find(function(variation) {
                    return variation.id == productId;
                }),
                identifier = variation ? variation.details : {};

            return Object.assign({}, state, {
                identifiers: Object.assign({}, state.identifiers, {
                    [productId]: Object.assign({}, state.identifiers[productId], {
                        ean: identifier.ean,
                        upc: identifier.upc,
                        isbn: identifier.isbn,
                        mpn: identifier.mpn
                    })
                })
            });
        }
    });

