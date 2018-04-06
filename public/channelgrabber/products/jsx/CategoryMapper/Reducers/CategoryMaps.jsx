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
                accountId = action.payload.accountId;

            for (var mapId in newState) {
                newState[mapId] = Object.assign({}, state[mapId], {
                    selectedCategories: []
                });
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
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            var categoryMaps = action.payload.categoryMaps,
                newCategoryMaps = {},
                newCategoryMap,
                newState;

            for (var mapId in categoryMaps) {
                var categoryMap = categoryMaps[mapId],
                    accountCategories = categoryMap.accountCategories,
                    mapName = categoryMap.name,
                    categoriesForAccount,
                    accountId,
                    index,
                    j,
                    k,
                    categories,
                    selectedCategories = {},
                    selectedCategoriesForAccount,
                    category;

                for (index = 0; index < accountCategories.length; index++) {
                    categoriesForAccount = accountCategories[index].categories[0];
                    accountId = accountCategories[index].accountId;

                    selectedCategoriesForAccount = [];

                    for (j = 0; j < categoriesForAccount.length; j++) {
                        categories = categoriesForAccount[j];
                        for (k = 0; k < categories.length; k ++) {
                            category = categories[k];
                            if (category.selected === true) {
                                selectedCategoriesForAccount.push(category.value);
                            }
                        }
                    }

                    selectedCategories[accountId] = selectedCategoriesForAccount;
                }

                newCategoryMap = {
                    name: mapName,
                    selectedCategories: selectedCategories
                };

                newCategoryMaps[mapId] = newCategoryMap;
            }

            newState = Object.assign({}, state, newCategoryMaps);

            return newState;
        }
    });
});
