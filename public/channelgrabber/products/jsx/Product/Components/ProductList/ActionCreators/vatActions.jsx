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
            updateVat: (rowId, countryCode, desiredVal) => {
                return function(dispatch){
                    console.log('in updateVat AQ');
                    dispatch({
                        type: "VAT_UPDATE",
                        payload:{
                            rowId,
                            countryCode,
                            desiredVal
                        }
                    });
                }
            }
        };
    })();
    
    return vatActions;
});