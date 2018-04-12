define([], function() {
    "use strict";

    return {
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
            delete data.bodyTag;
            return {
                type: 'CATEGORY_MAPS_FETCHED',
                payload: {
                    categoryMaps: data
                }
            }
        },
        categoryMapDeleted: function(mapId) {
            return {
                type: 'CATEGORY_MAP_DELETED',
                payload: {
                    mapId
                }
            }
        },
    }
});
