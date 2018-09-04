define([
    'Product/Components/ProductList/Config/constants',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/ProductList/ActionCreators/productActions'
], function(
    constants,
    stateUtility,
    productActions
) {
    "use strict";
    
    var searchActions = (function() {
        const setProductSearchTerm = (searchTerm) => {
            return {
                type:"PRODUCTS_SEARCH_TERM_SET",
                payload:{
                    searchTerm
                }
            };
        };
        return {
            searchProducts : ( searchTerm ) => {
                return async function(dispatch, getState) {
                    const state =  getState();
                    dispatch(setProductSearchTerm(searchTerm));
                    let currentPageNumber = stateUtility.getCurrentPageNumber(state);
                    try{
                        await dispatch(productActions.getProducts(currentPageNumber));
                    }catch(err){
                        console.error(err);
                    }
                }
            },
        };
    })();
    
    return searchActions;
});