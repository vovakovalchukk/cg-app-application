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

            newState[action.payload.mapId] = {
                name: action.payload.name,
                etag: action.payload.etag,
                selectedCategories: selectedCategories
            };

            return newState;
        },
        "UPDATE_CATEGORY_MAP": function (state, action) {
            var newState = Object.assign({}, state);
            newState[action.payload.mapId] = Object.assign({}, newState[action.payload.mapId], {
                etag: action.payload.etag
            });
            return newState;
        }
    });
});
