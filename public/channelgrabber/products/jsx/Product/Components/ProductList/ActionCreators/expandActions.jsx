import productActions from "Product/Components/ProductList/ActionCreators/productActions"

// note: individual expand statuses are stored against products through the products reducer

let expandActions = (function() {
    return {
        toggleExpandAll: (dispatch, getState) => {
            return function(dispatch, getState) {
                let expand = getState().expand;
                console.log('in toggleExpand All');

                dispatch(productActions.expandAllProducts);

                // needs to fire an update in product actions
                dispatch({
                    type: "EXPAND_ALL_TOGGLE"
                });
            }
        }
    };
}());

export default expandActions;