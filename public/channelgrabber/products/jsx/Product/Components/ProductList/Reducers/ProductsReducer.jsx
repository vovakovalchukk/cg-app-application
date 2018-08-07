define([
    'Common/Reducers/creator',
], function(
    reducerCreator,
) {
    "use strict";
    var initialState = {
        completeInitialLoads:{
            simpleAndParentProducts:false
        },
        simpleAndParentProducts:[]
    };
    
    var ProductsReducer = reducerCreator(initialState, {
        "INITIAL_SIMPLE_AND_PARENT_PRODUCTS_LOAD": function(state, action) {
            console.log('r-in initial products load with action.payload.products: ' , action.payload.products);
            let newState = Object.assign({}, state, {
                completeInitialLoads:{
                    simpleAndParentProducts:true
                },
                simpleAndParentProducts:action.payload.products,
                visibleRows: action.payload.products
            });
            return newState;
        },
        "PRODUCT_EXPAND":function(state,action){
            console.log('in product expand with action: ' , action, ' state: ' , state);
            let currentVisibleProducts = state.visibleRows.slice();
            
            let parentProduct = currentVisibleProducts.find(function(product){
                return product.id === action.payload.productRowIdToExpand
            });
    
            // // todo - loop through variation Ids and retrieve all of the relevant variations
            // parentProduct.variationIds.forEach(variationId=>{
            //
            // });
    
            console.log('parentProduct: ' , parentProduct);
            
            return state;
            
        }
    });
    
    return ProductsReducer;
});