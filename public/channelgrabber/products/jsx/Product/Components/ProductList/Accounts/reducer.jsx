define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        features: {},
        accounts: {},
    };
    
    var accountsReducer = reducerCreator(initialState, {
        "ACCOUNT_FEATURES_STORE": function(state, action) {
            let newState = Object.assign({}, state, {
                features: action.payload.features
            });
            return newState;
        },
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state,action){
            console.log('in PRODUCTS_GET_REQUEST_SUCCESS accountReducer action : ' , action);
            let newState = Object.assign({}, state, {
                accounts: action.payload.accounts
            });
            console.log('newState now : ' , newState);
            return newState;
        }
    });
    
    console.log('accountsReducer: ', accountsReducer);
    
    
    return accountsReducer;
});