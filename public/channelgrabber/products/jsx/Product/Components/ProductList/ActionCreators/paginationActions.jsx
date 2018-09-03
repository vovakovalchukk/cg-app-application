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
        
        let changePaginationLimitOnState = (desiredLimit)=>{
            return {
                type: 'LIMIT_CHANGE',
                payload: {
                    desiredLimit
                }
            }
        };
        
        return {
            changePage: desiredPageNumber => {
                return async (dispatch, getState) => {
                    const state = getState();
                    let {searchTerm} = state.search;
                    await dispatch(productActions.getProducts(desiredPageNumber, searchTerm));
                }
            },
            changeLimit: desiredLimit => {
                return async (dispatch) => {
                    dispatch(changePaginationLimitOnState(desiredLimit));
                    await dispatch(productActions.getProducts());
                }
            }
        }
    })();
    
    return paginationActions;
});