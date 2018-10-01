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
            let {productId,checked} = action.payload;
    
            console.log('in bulk select product status change -R',{productId,checked});
    
            let selectedProducts = state.selectedProducts.slice();
            
            if(checked){
                if(state.selectedProducts.indexOf(productId) > -1){
                    console.log('breaking out');
                    console.log('new selectedProducts: ', selectedProducts);
                    return state
                }
                selectedProducts.push(productId);
            }else{
                if(state.selectedProducts.indexOf(productId) > -1){
                    console.log('about to splice');
                    
                    
                    selectedProducts.splice(
                        selectedProducts.indexOf(productId),
                        1
                    );
                    console.log('wont splice');
                    
                    
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