define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    let initialState = {
        selectedProducts : []
    };
    
    let bulkSelectReducer = reducerCreator(initialState, {
        "BULK_SELECT_PRODUCT_STATUS_CHANGE": function(state, action) {
            console.log('in bulk select product status change -R');
            let {productId,checked} = action.payload;
            let selectedProducts = state.selectedProducts.slice();
            
            if(checked){
                if(state.selectedProducts.indexOf(productId) > -1){
                    console.log('breaking out');
                    console.log('new selectedProducts: ', selectedProducts);
                    
                }
                selectedProducts.push(productId);
            }else{
                if(state.selectedProducts.indexOf(productId) > -1){
                    selectedProducts.splice(
                        selectedProducts.indexOf(productId),
                        1
                    );
                }
            }
            console.log('new selectedProducts: ', selectedProducts);
            
            let newState = Object.assign({}, state, {
                selectedProducts
            });
            return newState;
        }
    });
    
    return bulkSelectReducer;
});