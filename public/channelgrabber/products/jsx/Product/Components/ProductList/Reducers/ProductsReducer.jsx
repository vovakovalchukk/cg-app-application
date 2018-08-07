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
        }
    });
    
    return ProductsReducer;
});