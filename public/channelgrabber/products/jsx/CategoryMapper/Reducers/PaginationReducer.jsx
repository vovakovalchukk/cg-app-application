define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {
        searchText: '',
        page: 1,
        shouldLoadMore: false
    };

    return reducerCreator(initialState, {
        "SEARCH_CHANGED": function (state, action) {
            return {
                searchText: action.payload.searchText,
                page: state.page,
                shouldLoadMore: state.shouldLoadMore
            };
        },
        "FETCH_CATEGORY_MAPS": function (state, action) {
            return {
                searchText: action.payload.searchText,
                page: action.payload.shouldReset ? 1 : state.page,
                shouldLoadMore: false
            }
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            return {
                searchText: state.searchText,
                page: state.page + 1,
                shouldLoadMore: Object.keys(action.payload.categoryMaps).length > 0
            }
        }
    });
});
