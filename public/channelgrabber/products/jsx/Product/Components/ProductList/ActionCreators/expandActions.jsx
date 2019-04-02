import productActions from "Product/Components/ProductList/ActionCreators/productActions"
import stateUtility from "Product/Components/ProductList/stateUtility";

let expandActions = (function() {
    return {
        changeStatusExpandAll: (desiredStatus) => {
            return {
                type: "EXPAND_ALL_STATUS_CHANGE",
                payload: {
                    desiredStatus
                }
            }
        },
        toggleExpandAll: () => {
            return async function(dispatch, getState) {
                let expand = getState().expand;

                let expandHandler = {
                    'loading': () => {},
                    'collapsed': async () => {
                        let state = getState();
                        let allParentIds = stateUtility.getAllParentProductIds(state.products);
                        let variationsByParent = state.products.variationsByParent;

                        let haveFetchedAlready = checkIfAllVariationsHaveBeenFetchedAlready(variationsByParent, allParentIds);

                        if (!haveFetchedAlready) {
                            dispatch(expandActions.changeStatusExpandAll('loading'));
                        }
                        await dispatch(productActions.expandAllProducts(haveFetchedAlready));
                        dispatch(expandActions.changeStatusExpandAll('expanded'));
                    },
                    'expanded': () => {
                        dispatch(productActions.collapseAllProducts());
                        dispatch(expandActions.changeStatusExpandAll('collapsed'));
                    }
                };

                expandHandler[expand.expandAllStatus]();
            }
        }
    };
}());

export default expandActions;

function checkIfAllVariationsHaveBeenFetchedAlready(variationsByParent, allParentIds) {
    if (!Object.keys(variationsByParent).length) {
        return false;
    }
    for (let parentProductId of allParentIds) {
        let parentHasHadVariationRetrievedAlready = false;
        for (let parentId of Object.keys(variationsByParent)) {
            if (parentId == parentProductId) {
                parentHasHadVariationRetrievedAlready = true;
                break;
            }
        }
        if (!parentHasHadVariationRetrievedAlready) {
            return false;
        }
    }
    return true;
}