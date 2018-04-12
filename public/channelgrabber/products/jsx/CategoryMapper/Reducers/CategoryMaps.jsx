define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_SELECTED": function (state, action) {
            var newState = JSON.parse(JSON.stringify(state)),
                accountId = action.payload.accountId,
                mapId = action.payload.categoryMapIndex,
                categoryLevel = action.payload.categoryLevel,
                categoryId = action.payload.categoryId;

            if (!newState[mapId]) {
                newState[mapId] = {
                    name: '',
                    selectedCategories: {}
                }
            }

            if (!newState[mapId].selectedCategories[accountId]) {
                newState[mapId].selectedCategories[accountId] = [];
            }

            newState[mapId].selectedCategories[accountId][categoryLevel] = categoryId;
            newState[mapId].selectedCategories[accountId].splice(categoryLevel + 1);

            return newState;
        },
        "REFRESH_CATEGORIES": function (state, action) {
            var newState = Object.assign({}, state),
                accountId = action.payload.accountId,
                categoryMap;

            for (var mapId in newState) {
                categoryMap = newState[mapId];
                categoryMap = Object.assign({}, categoryMap);
                categoryMap.selectedCategories = Object.assign({}, categoryMap.selectedCategories);
                categoryMap.selectedCategories[accountId] = [];
                newState[mapId] = categoryMap;
            }

            return newState;
        },
        "REMOVE_ROOT_CATEGORY": function (state, action) {
            var newState = Object.assign({}, state),
                accountId = action.payload.accountId,
                mapId = action.payload.categoryMapIndex;

            newState[mapId] = Object.assign({}, state[mapId]);
            newState[mapId].selectedCategories = Object.assign({}, newState[mapId].selectedCategories);
            newState[mapId].selectedCategories[accountId] = [];

            return newState;
        },
        "FETCH_CATEGORY_MAPS": function (state, action) {
            if (action.payload.shouldReset) {
                return (0 in state) ? {0: state[0]} : {};
            }
            return state;
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            var categoryMaps = action.payload.categoryMaps,
                newCategoryMaps = {},
                newCategoryMap;

            for (var mapId in categoryMaps) {
                var categoryMap = categoryMaps[mapId],
                    accountCategories = categoryMap.accountCategories;

                newCategoryMap = {
                    selectedCategories: {},
                    name: categoryMap.name,
                    etag: categoryMap.etag
                };

                accountCategories.map(function (categoriesForAccount) {
                    var selectedCategories = [];
                    categoriesForAccount.categories.map(function (categoriesByLevel) {
                        selectedCategories = [];
                        categoriesByLevel.map(function (categories, level) {
                            categories.map(function (category) {
                                if (category.selected) {
                                    selectedCategories.push(category.value);
                                }
                            });
                        })
                    });
                    newCategoryMap.selectedCategories[categoriesForAccount.accountId] = selectedCategories;
                });
                newCategoryMaps[mapId] = newCategoryMap;
            }

            return Object.assign({}, state, newCategoryMaps);
        },
        "CATEGORY_MAP_DELETED": function (state, action) {
            var newState = Object.assign({}, state);
            if (action.payload.mapId in newState) {
                delete newState[action.payload.mapId];
            }
            return newState;
        }
    });
});
