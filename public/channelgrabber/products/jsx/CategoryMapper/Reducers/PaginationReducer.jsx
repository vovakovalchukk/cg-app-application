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
        },
        "FETCH_CATEGORY_MAPS": function (state, action) {
            return {
                searchText: action.payload.searchText,
                page: action.payload.shouldReset ? 1 : state.page
            }
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            return {
                searchText: state.searchText,
                page: state.page + 1
            }
        }
    });
});
