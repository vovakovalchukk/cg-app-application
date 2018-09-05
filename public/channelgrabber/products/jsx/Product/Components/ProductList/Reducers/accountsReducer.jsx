define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    let initialState = {
        features: {},
        accounts: {},
        'getAccounts': state => state.accounts.accounts,
        'getFeatures': state => state.accounts.features
    };
    
    let accountsReducer = reducerCreator(initialState, {
        "ACCOUNT_FEATURES_STORE": function(state, action) {
            let newState = Object.assign({}, state, {
                features: action.payload.features
            });
            return newState;
        },
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state,action){
            let newState = Object.assign({}, state, {
                accounts: action.payload.accounts
            });
            return newState;
        }
    });
    
    return accountsReducer;
});