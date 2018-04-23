define([
    'CategoryMapper/Actions/ApiHelper',
    'Product/Components/CreateListing/Actions/ResponseActions',
], function(
    ApiHelper,
    ResponseActions
) {
    "use strict";

    return {
        fetchCategoryRoots: function(dispatch) {
            $.get(
                ApiHelper.buildFetchCategoryRootsUrl(),
                function(response) {
                    dispatch(ResponseActions.categoryRootsFetched(response));
                }
            );

            return {
                type: "FETCH_CATEGORY_ROOTS",
                payload: {}
            };
        },
        showAddNewCategoryMapComponent: function() {
            return {
                type: "SHOW_ADD_NEW_CATEGORY_MAP",
                payload: {}
            };
        }
    };
});
