define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";
    
    var initialState = {
        columnSettings: []
    };
    
    var CreateListingReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            console.log('in PRODUCTS_GET_REQUEST_SUCCESS action: ' , action);
            
            
            let newState = Object.assign({}, state, {
                columnSettings: action.payload.columnSettings
            });
            return newState;
        }
    });
    
    return CreateListingReducer;
});