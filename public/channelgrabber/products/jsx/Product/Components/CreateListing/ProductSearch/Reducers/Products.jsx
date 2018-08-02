define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    return reducerCreator({}, {
        "FETCH_SEARCH_RESULTS": function() {
            return {};
        },
        "SEARCH_RESULTS_FETCHED": function(state, action) {
            return action.payload.products;
        }
    });
});
