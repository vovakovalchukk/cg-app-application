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
        }
    });
});
