define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
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
        }
    });
});
