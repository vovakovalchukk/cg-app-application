define([], function() {

    var Api = {
        buildCategoryChildrenUrl: function (accountId, categoryId) {
            return '/settings/category/templates/' + accountId + '/category-children/' + categoryId;
        },
        buildRefreshCategoryUrl: function(accountId) {
            return '/settings/category/templates/' + accountId + '/refresh-categories';
        },
        buildFetchCategoryMapsUrl: function () {
            return '/settings/category/templates/fetch'
        },
    }

    var ResponseActions = {
        categoryChildrenFetched: function (categoryMapIndex, accountId, categoryId, categoryLevel, selectedCategories, data) {
            return {
                type: 'CATEGORY_CHILDREN_FETCHED',
                payload: {
                    categoryMapIndex: categoryMapIndex,
                    accountId: accountId,
                    categories: data.hasOwnProperty('categories') ? data.categories : {},
                    categoryId: categoryId,
                    categoryLevel: categoryLevel,
                    selectedCategories: selectedCategories
                }
            }
        },
        categoryRefreshed: function(accountId, data) {
            return {
                type: 'REFRESH_CATEGORIES_FETCHED',
                payload: {
                    accountId: accountId,
                    categories: data.hasOwnProperty('categories') ? data.categories : {}
                }
            }
        },
        categoryMapsFetched: function(data) {
            return {
                type: 'CATEGORY_MAPS_FETCHED',
                payload: {
                    categoryMaps: data
                }
            }
        }
    }

    return {
        categorySelected: function (dispatch, categoryMapIndex, accountId, categoryId, categoryLevel, selectedCategories) {

            console.log(selectedCategories);

            selectedCategoriesForChildren = selectedCategories.slice(0);
            selectedCategoriesForChildren.splice(categoryLevel);
            console.log(selectedCategories, selectedCategoriesForChildren)

            $.get(
                Api.buildCategoryChildrenUrl(accountId, categoryId),
                function(response) {
                    dispatch(ResponseActions.categoryChildrenFetched(categoryMapIndex, accountId, categoryId, categoryLevel, selectedCategoriesForChildren, response));
                }
            );

            return {
                type: 'CATEGORY_SELECTED',
                payload: {
                    categoryMapIndex: categoryMapIndex,
                    accountId: accountId,
                    categoryId: categoryId,
                    categoryLevel: categoryLevel,
                    selectedCategories: selectedCategories
                }
            };
        },
        refreshButtonClicked: function (dispatch, accountId) {
            $.get(
                Api.buildRefreshCategoryUrl(accountId),
                function (response) {
                    dispatch(ResponseActions.categoryRefreshed(accountId, response));
                }
            )

            return {
                type: 'REFRESH_CATEGORIES',
                payload: {
                    accountId: accountId
                }
            }
        },
        removeButtonClicked: function (categoryMapIndex, accountId) {
            return {
                type: 'REMOVE_ROOT_CATEGORY',
                payload: {
                    categoryMapIndex: categoryMapIndex,
                    accountId: accountId
                }
            }
        },
        fetchCategoryMaps: function (dispatch) {
            $.get(
                Api.buildFetchCategoryMapsUrl(),
                function (response) {
                    dispatch(ResponseActions.categoryMapsFetched(response));
                }
            )

            return {
                type: 'FETCH_CATEGORY_MAPS',
                payload: {}
            }
        }
    };
});
