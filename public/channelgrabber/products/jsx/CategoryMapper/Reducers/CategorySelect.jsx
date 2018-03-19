define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};
    return reducerCreator(initialState, {
        "CATEGORY_SELECTED": function (state, action) {
            var newState = JSON.parse(JSON.stringify(state));
            return newState;
        },
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = JSON.parse(JSON.stringify(state));
            newState.categories = action.payload.categories;
            return newState;
        }
    });
});