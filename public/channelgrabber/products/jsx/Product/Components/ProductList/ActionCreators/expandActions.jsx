import productActions from "Product/Components/ProductList/ActionCreators/productActions"

let expandActions = (function() {
    const changeStatusExpandAll = (desiredStatus) => {
        return {
            type: "EXPAND_ALL_STATUS_CHANGE",
            payload: {
                desiredStatus
            }
        }
    };

    return {
        toggleExpandAll: () => {
            return async function(dispatch, getState) {
                let expand = getState().expand;

                let expandHandler = {
                    'loading': () => {},
                    'collapsed': async () => {
                        dispatch(changeStatusExpandAll('loading'));
                        await dispatch(productActions.expandAllProducts());
                        dispatch(changeStatusExpandAll('expanded'));
                    },
                    'expanded': () => {
                        dispatch(productActions.collapseAllProducts());
                        dispatch(changeStatusExpandAll('collapsed'));
                    }
                };

                expandHandler[expand.expandAllStatus]();
            }
        }
    };
}());

export default expandActions;