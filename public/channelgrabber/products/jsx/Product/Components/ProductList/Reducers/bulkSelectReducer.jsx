import utility from 'Product/Components/ProductList/utility';
import reducerCreator from 'Common/Reducers/creator';

"use strict";

let initialState = {
    selectedProducts: []
};

let bulkSelectReducer = reducerCreator(initialState, {
    "BULK_SELECT_PRODUCT_STATUS_CHANGE": function(state, action) {
        let {productId, checked} = action.payload;
        let selectedProducts = state.selectedProducts.slice();
        
        if (checked) {
            if (state.selectedProducts.indexOf(productId) > -1) {
                return state
            }
            selectedProducts.push(productId);
        } else {
            if (state.selectedProducts.indexOf(productId) > -1) {
                selectedProducts.splice(
                    selectedProducts.indexOf(productId),
                    1
                );
            }
        }
        let newState = Object.assign({}, state, {
            selectedProducts
        });
        return newState;
    },
    "PRODUCTS_DELETE_SUCCESS": function(state, action) {
        let {deletedProducts} = action.payload;
        n.success('Successfully deleted products.');
        let newSelectedProducts = state.selectedProducts.slice();
        let nonDeletedIds = utility.findDifferenceOfTwoArrays(newSelectedProducts, deletedProducts);
        let newState = Object.assign(
            {},
            state,
            {
                selectedProducts: nonDeletedIds
            }
        );
        return newState;
    }
});

export default bulkSelectReducer;