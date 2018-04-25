define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";
    var initialState = {
        taxRates: {}
    };
    var AccountReducer = reducerCreator(initialState, {
        "INITIAL_ACCOUNT_DATA_LOADED": function(state, action) {
            var newTaxRates = action.payload.taxRates;
            var newStockModeOptions = action.payload.stockModeOptions;
            var newState = Object.assign({
                taxRates: newTaxRates,
                stockModeOptions: newStockModeOptions
            }, {})
            return newState;
        }
    });

    return AccountReducer;
});