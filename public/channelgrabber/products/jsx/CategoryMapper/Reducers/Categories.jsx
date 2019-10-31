import reducerCreator from 'Common/Reducers/creator';
    var initialState = {};

    var categoryHasChildren = function(category) {
        return 'categoryChildren' in category && Object.keys(category.categoryChildren).length > 0;
    };

    var formatNewCategoriesArray = function(categories) {
        var newCategories = {};
        categories.map(function (category) {
            newCategories[category.value] = {
                title: category.name,
                listable: category.listable
            };
        });
        return newCategories;
    };

    var getSelectedCategoryId = function(categories) {
        var selectedCategoryId = null;
        categories.map(function (category) {
            if (category.selected) {
                selectedCategoryId = category.value;
            }
        });
        return selectedCategoryId;
    };

    var extractCategoryDataFromCategoryMap = function(newState, accountCategories) {
        accountCategories.map(function (categoryMap) {
            var currentCategories = newState[categoryMap.accountId];
            if (!currentCategories || Object.keys(currentCategories).length === 0) {
                return;
            }
            categoryMap.categories.map(function (categoriesByLevel) {
                var selectedCategoryId;
                categoriesByLevel.map(function (categories, level) {
                    if (level == 0) {
                        selectedCategoryId = getSelectedCategoryId(categories);
                        return;
                    }

                    var currentSelectedCategory = currentCategories[selectedCategoryId];
                    if (currentSelectedCategory === undefined) {
                        return;
                    }

                    if (!categoryHasChildren(currentSelectedCategory)) {
                        currentSelectedCategory.categoryChildren = formatNewCategoriesArray(categories);
                    }

                    currentCategories = currentCategories[selectedCategoryId].categoryChildren;
                    selectedCategoryId = getSelectedCategoryId(categories);
                });
            });
        });
    }

    export default reducerCreator(initialState, {
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

            if (categoryHasChildren(categories[parentCategoryId])) {
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
                newState = JSON.parse(JSON.stringify(state));

            for (var mapId in categoryMaps) {
                extractCategoryDataFromCategoryMap(newState, categoryMaps[mapId].accountCategories);
            }

            return newState;
        },
        "CATEGORY_ROOTS_FETCHED": function (state, action) {
            var categories = {};
            action.payload.accountCategories.forEach(function (value, index) {
                categories[value.accountId] = Object.assign({}, value.categories);
            });
            return categories;
        }
    });

