import ApiHelper from 'CategoryMapper/Actions/ApiHelper';
import ResponseActions from 'CategoryMapper/Actions/ResponseActions';
    export default {
        categorySelected: function (dispatch, categoryMapIndex, accountId, categoryId, categoryLevel, selectedCategories) {

            $.get(
                ApiHelper.buildCategoryChildrenUrl(accountId, categoryId),
                function(response) {
                    var selectedCategoriesForChildren = selectedCategories.slice(0);
                    selectedCategoriesForChildren.splice(categoryLevel);
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
                ApiHelper.buildRefreshCategoryUrl(accountId),
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
        fetchCategoryMaps: function (dispatch, searchText, page) {
            $.post(
                ApiHelper.buildFetchCategoryMapsUrl(),
                {
                    search: searchText,
                    page: page
                },
                function (response) {
                    dispatch(ResponseActions.categoryMapsFetched(response));
                }
            )

            return {
                type: 'FETCH_CATEGORY_MAPS',
                payload: {
                    shouldReset: searchText.length > 0 && page == 1,
                    searchText: searchText
                }
            }
        },
        updateSearch: function (searchText) {
            return {
                type: 'SEARCH_CHANGED',
                payload: {
                    searchText: searchText
                }
            }
        },
        deleteCategoryMap: function (dispatch, mapId) {
            $.get(
                ApiHelper.buildDeleteCategoryMapUrl(mapId)
            ).success(function (response) {
                if (response.valid) {
                    n.success('The category map was deleted successfully.');
                    dispatch(ResponseActions.categoryMapDeleted(mapId));
                    return;
                }
                n.error('There was an error while deleting the category map. Please try again or contact support if the problem persists.');
            }).error(function () {
                n.error('There was an error while deleting the category map. Please try again or contact support if the problem persists.');
            });

            return {
                type: 'DELETE_CATEGORY_MAP',
                payload: {
                    mapId: mapId
                }
            };
        }
    };

