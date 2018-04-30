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
                newState[mapId] = Helper.invalidateSelectedCategoriesForAccount(newState[mapId], accountId);
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
                newCategoryMaps = {};

            for (var mapId in categoryMaps) {
                newCategoryMaps[mapId] = Helper.extractSelectedCategoryDataFromCategoryMap(categoryMaps[mapId]);
            }

            return Object.assign({}, state, newCategoryMaps);
        },
        "CATEGORY_MAP_DELETED": function (state, action) {
            var newState = Object.assign({}, state);
            if (action.payload.mapId in newState) {
                delete newState[action.payload.mapId];
            }
            return newState;
        },
        "ADD_NEW_CATEGORY_MAP": function (state, action) {
            var newState = Object.assign({}, state);
            newState[action.payload.mapId] = Object.assign({}, newState[0], {
                etag: action.payload.etag
            });
            newState[0] = {
                name: '',
                selectedCategories: {}
            }
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
