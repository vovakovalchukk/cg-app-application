import productActions from 'Product/Components/ProductList/ActionCreators/productActions';

"use strict";

let searchActions = (function() {
    const setProductSearchTerm = (searchTerm) => {
        return {
            type: "PRODUCTS_SEARCH_TERM_SET",
            payload: {
                searchTerm
            }
        };
    };
    return {
        searchProducts: (searchTerm) => {
            return async function(dispatch, getState) {
                const state = getState();
                dispatch(setProductSearchTerm(searchTerm));
                try {
                    await dispatch(productActions.getProducts());
                } catch (err) {
                    console.error(err);
                }
            }
        },
    };
})();

export default searchActions;