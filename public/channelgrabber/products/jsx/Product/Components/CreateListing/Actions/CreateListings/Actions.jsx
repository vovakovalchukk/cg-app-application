define([
], function(
) {
    "use strict";

    return {
        loadInitialValues: function(product) {
            return {
                type: "LOAD_INITIAL_VALUES",
                payload: {
                    product: product
                }
            };
        }
    };
});
