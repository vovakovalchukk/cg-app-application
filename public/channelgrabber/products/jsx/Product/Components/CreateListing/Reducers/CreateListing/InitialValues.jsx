define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "LOAD_INITIAL_VALUES": function(state, action) {
            var product = action.payload.product,
                variationData = action.payload.variationData;

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
                prices[variation.sku] = {
                    [variation.accountId]: variation.details.price,
                };
            });

            return {
                title: product.name,
                dimensions: dimensions,
                prices: prices
            };
        }
    });
});
