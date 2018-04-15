define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    var categoryHasChildren = function(categories, parentCategoryId) {
        return (parentCategoryId in categories && 'categoryChildren' in categories[parentCategoryId] && Object.keys(categories[parentCategoryId].categoryChildren).length > 0);
    }

    var extractCategoryDataFromCategoryMap = function(newState, accountCategories) {
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

                    var newCategories = {};
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

    return reducerCreator(initialState, {
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state),
                accountId = action.payload.accountId,
                parentCategoryId = action.payload.categoryId,
                childCategories = action.payload.categories,
                selectedCategories = action.payload.selectedCategories;

            newState[accountId] = Object.assign({}, newState[accountId]);

            var accountCategories = JSON.parse(JSON.stringify(newState[accountId])),
                categories = accountCategories;

            for (var i = 0; i < selectedCategories.length; i++) {
                categories = categories[selectedCategories[i]].categoryChildren;
            }

            if (categoryHasChildren(categories, parentCategoryId)) {
                return state;
            }

            categories[parentCategoryId] = Object.assign({}, categories[parentCategoryId], {
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
                extractCategoryDataFromCategoryMap(newState, categoryMaps[mapId].accountCategories);
            }

            return newState;
        }
    });
});
