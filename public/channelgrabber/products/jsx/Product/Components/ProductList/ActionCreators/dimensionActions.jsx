"use strict";

let dimensionActions = (function() {
    return{
        changeDimensionValue: (productId, detail, newValue) => {
            return async function(dispatch, getState) {
                let currentDetails = getState.customGetters.getDetailsFromProductState(productId);
                console.log('in changeDimensionValue with rowData: ' , {productId, detail, newValue, currentValue: currentDetails});

                dispatch({
                    type: "DIMENSION_VALUE_CHANGE",
                    payload: {
                        productId,
                        detail,
                        newValue,
                        currentDetails
                    }
                });
            }
        }
    }
})();

export default dimensionActions;


