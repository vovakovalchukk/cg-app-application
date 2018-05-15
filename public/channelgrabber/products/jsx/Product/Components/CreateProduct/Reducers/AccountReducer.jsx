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
            return {
                taxRates: action.payload.taxRates
            }
        }
    });

    return AccountReducer;
});