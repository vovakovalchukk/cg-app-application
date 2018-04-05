define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_SELECTED": function (state, action) {
            var newState = Object.assign({}, state),
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

            newState[mapId].selectedCategories = Object.assign({}, newState[mapId].selectedCategories);

            if (!newState[mapId].selectedCategories[accountId]) {
                newState[mapId].selectedCategories[accountId] = [];
            }

            newState[mapId].selectedCategories[accountId][categoryLevel] = categoryId;

            return newState;
        },
        "REFRESH_CATEGORIES": function (state, action) {
            var newState = state.slice(0);
            var accountId = action.payload.accountId;

            for (var i = 0; i < newState.length; i++) {
                var newCategoryMap = Object.assign({}, newState[i].categoryMap);
                newCategoryMap[accountId] = Object.assign({}, newCategoryMap[accountId], {
                    categories: [{0: {tile: ''}}],
                    refreshing: true
                });
                newState[i].categoryMap = newCategoryMap;
            }

            return newState;
        },
        "REFRESH_CATEGORIES_FETCHED": function (state, action) {
            var newState = state.slice(0);
            var accountId = action.payload.accountId;

            for (var i = 0; i < newState.length; i++) {
                var newCategoryMap = Object.assign({}, newState[i].categoryMap);
                newCategoryMap[accountId] = Object.assign({}, newCategoryMap[accountId], {
                    categories: [action.payload.categories],
                    refreshing: false
                });
                newState[i].categoryMap = newCategoryMap;
            }

            return newState;
        },
        "REMOVE_ROOT_CATEGORY": function (state, action) {
            var newState = state.slice(0);
            var accountId = action.payload.accountId;
            var categoryMapIndex = action.payload.categoryMapIndex;

            var newCategoryMap = Object.assign({}, newState[categoryMapIndex].categoryMap);

            var newCategoriesArray = newCategoryMap[accountId].categories.slice(0);
            newCategoriesArray.splice(1);

            newCategoryMap[accountId] = Object.assign({}, newCategoryMap[accountId], {
                categories: newCategoriesArray,
                resetSelection: true
            });

            newState[categoryMapIndex].categoryMap = newCategoryMap

            return newState;
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            return state;
        }
    });
});
