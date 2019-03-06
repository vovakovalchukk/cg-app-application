import productActions from "Product/Components/ProductList/ActionCreators/productActions"
import stateUtility from "Product/Components/ProductList/stateUtility";

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
                        let state = getState();
                        let allParentIds = stateUtility.getAllParentProductIds(state.products);
                        let variationsByParent = state.products.variationsByParent;

                        let haveFetchedAlready = checkIfAllVariationsHaveBeenFetchedAlready(variationsByParent, allParentIds);

                        if(!haveFetchedAlready){
                            dispatch(changeStatusExpandAll('loading'));
                        }
                        await dispatch(productActions.expandAllProducts(haveFetchedAlready));
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

function checkIfAllVariationsHaveBeenFetchedAlready(variationsByParent, allParentIds) {
    if(!Object.keys(variationsByParent).length){
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
            expandAllHasOccurred = false;
            return false;
        }
    }
    return true;
}