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
        },
        hideAddNewCategoryMapComponent: function() {
            return {
                type: "HIDE_NEW_CATEGORY_MAP",
                payload: {}
            };
        },
        categoryMapSelected: function(categoryIds) {
            return {
                type: "CATEGORY_MAP_SELECTED",
                payload: {
                    categoryIds: categoryIds
                }
            };
        },
        categoryMapSelectedByName: function(categoryName) {
            return {
                type: "CATEGORY_MAP_SELECTED_BY_NAME",
                payload: {
                    name: categoryName
                }
            };
        },
    };
});
