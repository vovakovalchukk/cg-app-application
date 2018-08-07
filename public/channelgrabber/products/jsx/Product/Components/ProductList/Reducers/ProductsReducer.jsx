define([
    'Common/Reducers/creator',
], function(
    reducerCreator,
) {
    "use strict";
    var initialState = {
    };
    
    var ProductsReducer = reducerCreator(initialState, {
        // "FORM_SUBMIT_REQUEST": function(state, action) {
        //     var newState = Object.assign({}, state, {
        //         isSubmitting: true
        //     });
        //     return newState;
        // }
    });
    
    return ProductsReducer;
});