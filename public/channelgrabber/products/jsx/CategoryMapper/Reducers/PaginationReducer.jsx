define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {
        searchText: '',
        page: 1,
        shouldLoadMore: false,
        isFetching: false,
        loadMoreActive: false
    };

    return reducerCreator(initialState, {
        "SEARCH_CHANGED": function (state, action) {
            return Object.assign({}, state, {
                searchText: action.payload.searchText
            });
        },
        "FETCH_CATEGORY_MAPS": function (state, action) {
            var page = action.payload.shouldReset ? 1 : state.page;
            return {
                searchText: action.payload.searchText,
                page: page,
                shouldLoadMore: false,
                loadMoreActive: page !== 1,
                isFetching: true
            }
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            return {
                searchText: state.searchText,
                page: state.page + 1,
                shouldLoadMore: Object.keys(action.payload.categoryMaps).length > 0,
                loadMoreActive: Object.keys(action.payload.categoryMaps).length > 0,
                isFetching: false
            }
        }
    });
});
