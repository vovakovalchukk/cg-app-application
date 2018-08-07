define([
], function(
) {
    "use strict";
    
    return {
        initialSimpleAndParentProductsLoad: (products) => {
            return {
                type: "INITIAL_SIMPLE_AND_PARENT_PRODUCTS_LOAD",
                payload: {
                    products
                }
            };
        },
    };
});
