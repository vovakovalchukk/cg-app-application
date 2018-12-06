import productActions from 'Product/Components/ProductList/ActionCreators/productActions';

"use strict";

let paginationActions = (function() {
    let changePaginationLimitOnState = (desiredLimit) => {
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
                dispatch(productActions.moveVerticalScrollbarToTop());
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

export default paginationActions;