define([
], function(
) {
    "use strict";

    return {
        loadInitialValues: function(product, variationData) {
            return {
                type: "LOAD_INITIAL_VALUES",
                payload: {
                    product: product,
                    variationData: variationData
                }
            };
        }
    };
});
