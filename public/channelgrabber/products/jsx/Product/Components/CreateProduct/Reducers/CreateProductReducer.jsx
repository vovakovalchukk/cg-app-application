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
            //     accountId = action.payload.accountId,
            //     categoryId = action.payload.categoryId,
            //     childCategories = action.payload.categories,
            //     selectedCategories = action.payload.selectedCategories;
            return newState;
        },

    });

    return createProductsReducer;
});