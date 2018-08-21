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
    
    var listReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            console.log('in listReducer -R PRODUCTS_GET_REQUEST_SUCCESS action: '  , action);
            let {pagination,productSearchActive} = action.payload;
            let newState = Object.assign({}, state, {
                pagination,
                productSearchActive
            });
            return newState;
        },
    });
    
    return listReducer
});