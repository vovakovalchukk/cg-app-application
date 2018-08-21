define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        pagination:{},
        productSearchActive: false
    };
    
    var accountReducer = reducerCreator(initialState, {
        "ACCOUNT_FEATURES_STORE": function(state, action) {
            let newState = Object.assign({}, state, {
                features: action.payload.features
            });
            return newState;
        },
    });
    
    return accountReducer
});