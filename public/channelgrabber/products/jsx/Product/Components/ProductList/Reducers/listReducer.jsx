import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    fetchingUpdatedStockLevelsForSkus: {},
    currentRowScrollIndex: null
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
    },
    "VERTICAL_SCROLLBAR_SET_TO_0": function(state) {
        let newState = Object.assign({}, state, {
            currentRowScrollIndex: 0
        });
        return newState;
    },
    "HORIZONTAL_SCROLLBAR_INDEX_RESET": function(state) {
        let newState = Object.assign({}, state, {
            currentRowScrollIndex: null
        });
        return newState;
    }
});

export default listReducer