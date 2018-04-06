define([
    'Common/Reducers/creator'
], function (
    reducerCreator
) {
    var initialState = {};

    var createProductsReducer = reducerCreator(initialState, {

        "TEST_REDUCE": function(state,action){
            var newState = Object.assign({}, state);
            return newState;
        },

        "SAVE_PRODUCT_REQUEST": function (state, action) {
            var newState = Object.assign({}, state);
            return newState;
        }

    });

    return createProductsReducer;
});