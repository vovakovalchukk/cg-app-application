define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        pagination:{},
        productSearchActive: false,
        fetchingUpdatedStockLevelsForSkus:{}
    };
    
    var listReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            console.log('in listReducer -R PRODUCTS_GET_REQUEST_SUCCESS action: '  , action);
            let {pagination,productSearchActive} = action.payload;
            let newState = Object.assign({}, state, {
                pagination,
                productSearchActive
            });
            return newState;
        },
        "FETCHING_STOCK_LEVELS_FOR_SKUS_UPDATE": function(state,action){
            console.log('in FETCHING_STOCK_LEVELS_FOR_SKUS_UPDATE -R with action: ' , action, 'state: ' , state);
            let {fetchingStockLevelsForSkus} = action.payload;
            let newState = Object.assign({}, state, {
                fetchingStockLevelsForSkus
            });
            return newState;
        }
    });
    
    return listReducer
});