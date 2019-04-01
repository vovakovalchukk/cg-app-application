"use strict";

let selectActions = (function() {
    return {

        selectActiveToggle : (columnKey, productId) => {
            return {
                type: "SELECT_ACTIVE_TOGGLE",
                payload: {
                    columnKey,
                    productId
                }
            }
        },

        setSelectInactive: () => {

        }


//        searchProducts: (searchTerm) => {
//            return async function(dispatch, getState) {
//                const state = getState();
//                dispatch(setProductSearchTerm(searchTerm));
//                let currentPageNumber = getState.customGetters.getCurrentPageNumber(state);
//                try {
//                    await dispatch(productActions.getProducts(currentPageNumber));
//                } catch (err) {
//                    console.error(err);
//                }
//            }
//        },
    };
})();

export default selectActions;