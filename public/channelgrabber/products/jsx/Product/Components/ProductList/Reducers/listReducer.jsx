define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        pagination: {},
        productSearchActive: false,
        fetchingUpdatedStockLevelsForSkus: {}
    };
    
    var listReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST": function(state) {
            $('#products-loading-message').show();
            return state;
        },
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            let {pagination, productSearchActive} = action.payload;
            let newState = Object.assign({}, state, {
                pagination,
                productSearchActive
            });
            $('#products-loading-message').hide();
            return newState;
        },
        "FETCHING_STOCK_LEVELS_FOR_SKUS_UPDATE": function(state, action) {
            let {fetchingStockLevelsForSkus} = action.payload;
            let newState = Object.assign({}, state, {
                fetchingStockLevelsForSkus
            });
            return newState;
        }
    });
    
    return listReducer
});