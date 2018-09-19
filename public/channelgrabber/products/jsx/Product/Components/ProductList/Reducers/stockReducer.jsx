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
            console.log('-R STOCK_MODE_OPTIONS_STORE action.payload: ' , action.payload);
            
            
            let newState = Object.assign({}, state, {
                stockModeOptions: action.payload.stockModeOptions
            });
            return newState;
        }
    });
    
    return stockModeReducer;
});