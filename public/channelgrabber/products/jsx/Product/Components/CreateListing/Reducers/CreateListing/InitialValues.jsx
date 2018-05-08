define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
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
            if (data.accountsData[accountId].channel === 'ebay' && !defaults.ebay) {
                account = data.accountDefaultSettings[accountId];
                defaults.ebay = {
                    dispatchTimeMax: account.listingDispatchTime
                };
            }
        }
        return defaults;
    };

    return reducerCreator(initialState, {
        "LOAD_INITIAL_VALUES": function(state, action) {
            var product = action.payload.product,
                variationData = action.payload.variationData,
                selectedAccounts = action.payload.selectedAccounts;

            var dimensions = {};
            variationData.map(function(variation) {
                dimensions[variation.sku] = {
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
                prices[variation.sku] = pricesForVariation;
            });

            var identifiers = {};
            variationData.map(function(variation) {
                identifiers[variation.sku] = {
                    ean: variation.details.ean,
                    upc: variation.details.upc,
                    isbn: variation.details.isbn,
                    mpn: variation.details.mpn
                };
            });

            var productDetails = product.detail ? product.details : {};

            return {
                title: product.name,
                description: getDetailForProduct('description', productDetails, variationData),
                condition: getDetailForProduct('condition', productDetails, variationData),
                brand: getDetailForProduct('brand', productDetails, variationData),
                identifiers: identifiers,
                dimensions: dimensions,
                prices: prices,
                channel: formatChannelDefaultValues(action.payload)
            };
        }
    });
});
