define([], function() {
    var ResponseActions = {
        categoryChildrenFetched: function (accountId, categoryId, categoryLevel, data) {
            return {
                type: 'CATEGORY_CHILDREN_FETCHED',
                payload: {
                    accountId: accountId,
                    categories: data.hasOwnProperty('categories') ? data.categories : {},
                    categoryId: categoryId,
                    categoryLevel: categoryLevel
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
        }
    }

    var Api = {
        buildCategoryChildrenUrl: function (accountId, categoryId) {
            return '/settings/category/templates/' + accountId + '/category-children/' + categoryId;
        },
        buildRefreshCategoryUrl: function(accountId) {
            return '/settings/category/templates/' + accountId + '/refresh-categories';
        }
    }

    return {
        categorySelected: function (dispatch, accountId, categoryId, categoryLevel) {
            $.get(
                Api.buildCategoryChildrenUrl(accountId, categoryId),
                function(response) {
                    dispatch(ResponseActions.categoryChildrenFetched(accountId, categoryId, categoryLevel, response));
                }
            );

            return {
                type: 'CATEGORY_SELECTED',
                payload: {
                    accountId: accountId,
                    categoryId: categoryId,
                    categoryLevel: categoryLevel
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
        }
    };
});
