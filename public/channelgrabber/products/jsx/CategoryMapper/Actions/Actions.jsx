import ApiHelper from 'CategoryMapper/Actions/ApiHelper';
import ResponseActions from 'CategoryMapper/Actions/ResponseActions';
    export default {
        categorySelected: function (dispatch, categoryMapIndex, accountId, categoryId, categoryLevel, selectedCategories) {

            let response = {"categories":{"630540":{"title":"Mens","listable":true},"630547":{"title":"Women","listable":true}},"bodyTag":[]};
//            if (categoryId = 630540) {
//                response = {"categories":{"630541":{"title":"Accessories","listable":true},"630542":{"title":"Apparel","listable":true},"630543":{"title":"shoes","listable":true},"630546":{"title":"Socks","listable":true}},"bodyTag":[]};
//            }

            $.get(
                ApiHelper.buildCategoryChildrenUrl(accountId, categoryId),
                function() {
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
            dispatch(
                ResponseActions.categoryRefreshed(
                    accountId,
                    {"categories":{"630536":{"title":"Clothes","listable":true},"630538":{"title":"Hats","listable":true},"630539":{"title":"More Stuff","listable":true}},"bodyTag":[]}
                )
            );
//            $.get(
//                ApiHelper.buildRefreshCategoryUrl(accountId),
//                function (response) {
//                    dispatch(ResponseActions.categoryRefreshed(accountId, response));
//                }
//            )

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

