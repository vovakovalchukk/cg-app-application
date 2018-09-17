define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    let initialState = {
        stockModeOptions: []
    };
    
    let stockModeReducer = reducerCreator(initialState, {
        "STOCK_MODE_OPTIONS_STORE": function(state, action) {
            let newState = Object.assign({}, state, {
                stockModeOptions: action.payload.stockModeOptions
            });
            return newState;
        }
    });
    
    return stockModeReducer;
});