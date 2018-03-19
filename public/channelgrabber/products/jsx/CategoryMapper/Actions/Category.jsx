define([], function() {
    var categoryChildrenFetched = function (accountId, categoryId, categoryLevel, data) {
        return {
            type: 'CATEGORY_CHILDREN_FETCHED',
            payload: {
                accountId: accountId,
                categories: data.hasOwnProperty('categories') ? data.categories : {},
                categoryId: categoryId,
                categoryLevel: categoryLevel
            }
        }
    };

    var buildCategoryChildrenUrl = function (accountId, categoryId) {
        return '/settings/category/templates/' + accountId + '/category-children/' + categoryId;
    }

    return {
        categorySelected: function (dispatch, accountId, categoryId, categoryLevel) {
            $.get(
                buildCategoryChildrenUrl(accountId, categoryId),
                function(response) {
                    dispatch(categoryChildrenFetched(accountId, categoryId, categoryLevel, response));
                }
            );

            return {
                type: 'CATEGORY_SELECTED',
                payload: {
                    categoryId: categoryId
                }
            };
        }
    };
});
