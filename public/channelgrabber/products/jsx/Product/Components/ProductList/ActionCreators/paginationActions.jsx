define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity',
    'Product/Components/ProductList/ActionCreators/productActions',
    'Product/Components/ProductList/Config/constants'
], function(
    AjaxHandler,
    ProductFilter,
    productActions,
    constants
) {
    "use strict";
    
    // const {PRODUCT_LINKS_URL} = constants;
    
    let paginationActions = (function() {
        return {
            changePage: desiredPageNumber => {
                return async (dispatch, getState) => {
                    const state = getState();
                    let {searchTerm} = state.search;
                    
                    //todo need to pass limit here
                    await dispatch(productActions.getProducts(desiredPageNumber, searchTerm));
                }
            },
            changeLimit: desiredLimit => {
                console.log('in changeLimit AC with desiredLimit: ' , desiredLimit);
                return {
                    type: 'LIMIT_CHANGE',
                    payload: {
                        desiredLimit
                    }
                }
            }
        }
    })();
    
    return paginationActions;
});