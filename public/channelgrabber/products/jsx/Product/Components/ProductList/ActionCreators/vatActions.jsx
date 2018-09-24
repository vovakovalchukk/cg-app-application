define([], function() {
    "use strict";
    
    let vatActions = (function() {
        return {
            extractVatFromProducts: (products) => {
                return function(dispatch) {
                    dispatch({
                        type: "VAT_FROM_PRODUCTS_EXTRACT",
                        payload: {
                            products
                        }
                    });
                }
            },
        };
    })();
    
    return vatActions;
});