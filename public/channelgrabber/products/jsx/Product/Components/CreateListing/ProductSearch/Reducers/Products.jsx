define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    let initialState = {};

    return reducerCreator(initialState, {
        "SEARCH_RESULTS_FETCHED": function(state, action) {
            return action.payload.products;
        }
    });
});
