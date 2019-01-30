import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    fetchingUpdatedStockLevelsForSkus: {}
};

var listReducer = reducerCreator(initialState, {
    "PRODUCTS_GET_REQUEST_START": function(state) {
        $('#products-loading-message').show();
        return state;
    },
    "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
        $('#products-loading-message').hide();
        return state;
    },
    "STOCK_LEVELS_UPDATE_REQUEST_SUCCESS": function(state, action) {
        let {fetchingStockLevelsForSkus} = action.payload;
        let newState = Object.assign({}, state, {
            fetchingStockLevelsForSkus
        });
        return newState;
    }
});

export default listReducer