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

            newState[mapId] = Object.assign({}, state[mapId], {
                selectedCategories: []
            });

            return newState;
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            return state;
        }
    });
});
