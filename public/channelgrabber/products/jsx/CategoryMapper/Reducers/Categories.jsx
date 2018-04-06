define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state),
                accountId = action.payload.accountId,
                categoryId = action.payload.categoryId,
                childCategories = action.payload.categories,
                selectedCategories = action.payload.selectedCategories;

            newState[accountId] = Object.assign({}, newState[accountId]);

            var accountCategories = JSON.parse(JSON.stringify(newState[accountId])),
                categories = accountCategories;

            for (var i = 0; i < selectedCategories.length; i++) {
                categories = categories[selectedCategories[i]].categoryChildren;
            }

            if (categoryId in categories && 'categoryChildren' in categories[categoryId] && Object.keys(categories[categoryId].categoryChildren).length > 0) {
                return state;
            }

            categories[categoryId] = Object.assign({}, categories[categoryId], {
                categoryChildren: childCategories
            });

            newState[accountId] = accountCategories;

            return newState;
        },
        "REFRESH_CATEGORIES_FETCHED": function (state, action) {
            var newState = Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, action.payload.categories)
            });

            return newState;
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            var categoryMaps = action.payload.categoryMaps,
                newState = JSON.parse(JSON.stringify(state));

            console.log(state);

            for (var mapId in categoryMaps) {
                var categoryMap = categoryMaps[mapId],
                    accountCategories = categoryMap.accountCategories,
                    mapName = categoryMap.name,
                    categoriesForAccount,
                    accountId,
                    index,
                    categoryLevel,
                    k,
                    categories,
                    category,
                    currentCategories;

                for (index = 0; index < accountCategories.length; index++) {
                    categoriesForAccount = accountCategories[index].categories[0];
                    accountId = accountCategories[index].accountId;

                    currentCategories = newState[accountId];
                    var categoryId = null;

                    categoryLevelLoop:
                        for (categoryLevel = 0; categoryLevel < categoriesForAccount.length; categoryLevel++) {
                            categories = categoriesForAccount[categoryLevel];

                            console.log({
                                cats: categories,
                                current: currentCategories,
                                id: categoryId
                            });

                            if (categoryId) {
                                var map = {};
                                currentCategories[categoryId].categoryChildren = categories.reduce(function (map, category) {
                                    map[category.value] = {
                                        title: category.name
                                    };
                                    return map;
                                });
                                currentCategories = currentCategories[categoryId].categoryChildren;
                            }

                            categoriesLoop:
                                for (k = 0; k < categories.length; k ++) {
                                    category = categories[k];
                                    if (category.selected) {
                                        categoryId = category.value;
                                        continue categoryLevelLoop;
                                    }
                                }
                        }
                }
            }

            console.log(newState);

            return state;
        }
    });
});
