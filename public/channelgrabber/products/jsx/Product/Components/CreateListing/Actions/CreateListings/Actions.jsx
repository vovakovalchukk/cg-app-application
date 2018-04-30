define([
], function(
) {
    "use strict";

    return {
        loadInitialValues: function(product, variationData, accounts) {
            return {
                type: "LOAD_INITIAL_VALUES",
                payload: {
                    product: product,
                    variationData: variationData,
                    selectedAccounts: accounts
                }
            };
        }
    };
});
