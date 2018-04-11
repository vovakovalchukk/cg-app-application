define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {
        searchText: '',
        page: 1
    };

    return reducerCreator(initialState, {
        "SEARCH_CHANGED": function (state, action) {
            return {
                searchText: action.payload.searchText,
                page: state.page
            };
        }
    });
});
