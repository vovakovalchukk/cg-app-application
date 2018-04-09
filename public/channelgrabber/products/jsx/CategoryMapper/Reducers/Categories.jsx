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
                newState = JSON.parse(JSON.stringify(state)),
                newCategories = {};

            for (var mapId in categoryMaps) {
                var categoryMap = categoryMaps[mapId],
                    accountCategories = categoryMap.accountCategories;

                accountCategories.map(function (categoryMap) {
                    var currentCategories = newState[categoryMap.accountId];
                    categoryMap.categories.map(function (categoriesByLevel) {
                        var selectedCategoryId;
                        categoriesByLevel.map(function (categories, level) {

                            if (level == 0) {
                                categories.map(function (category) {
                                    if (category.selected) {
                                        selectedCategoryId = category.value;
                                    }
                                });
                                return;
                            }

                            newCategories = {};
                            categories.map(function (category) {
                                newCategories[category.value] = {
                                    title: category.name
                                }
                            });

                            currentCategories[selectedCategoryId].categoryChildren = newCategories;
                            currentCategories = currentCategories[selectedCategoryId].categoryChildren;

                            categories.map(function (category) {
                                if (category.selected) {
                                    selectedCategoryId = category.value;
                                }
                            });
                        })
                    });
                });
            }

            return newState;
        }
    });
});
