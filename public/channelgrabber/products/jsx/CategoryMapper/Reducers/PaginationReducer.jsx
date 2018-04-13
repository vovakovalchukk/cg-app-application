define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {
        searchText: '',
        page: 1,
        loadMoreEnabled: false,
        isFetching: false,
        loadMoreVisible: false
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
                loadMoreEnabled: false,
                loadMoreVisible: page !== 1,
                isFetching: true
            }
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            return {
                searchText: state.searchText,
                page: state.page + 1,
                loadMoreEnabled: Object.keys(action.payload.categoryMaps).length > 0,
                loadMoreVisible: Object.keys(action.payload.categoryMaps).length > 0,
                isFetching: false
            }
        }
    });
});
