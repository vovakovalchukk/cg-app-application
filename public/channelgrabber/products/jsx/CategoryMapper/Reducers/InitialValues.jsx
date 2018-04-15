define([
    'Common/Reducers/creator',
    'CategoryMapper/Reducers/Helper'
], function(
    reducerCreator,
    Helper
) {
    "use strict";

    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            var categoryMaps = action.payload.categoryMaps,
                newCategoryMaps = {},
                newCategoryMap;

            for (var mapId in categoryMaps) {
                newCategoryMaps[mapId] = Helper.extractSelectedCategoryDataFromCategoryMap(categoryMaps[mapId]);
            }

            return Object.assign({}, state, newCategoryMaps);
        },
        "ADD_NEW_CATEGORY_MAP": function (state, action) {
            var newState = Object.assign({}, state),
                selectedCategories = {};

            action.payload.categories.forEach(function(categoryId, accountId) {
                categoryId ? selectedCategories[accountId] = [categoryId] : null;
            });

            var newMap = {
                name: action.payload.name,
                etag: action.payload.etag,
                selectedCategories: selectedCategories
            };

            console.log(state, action, newMap);

            newState[action.payload.mapId] = newMap;

            return newState;
        }
    });
});
