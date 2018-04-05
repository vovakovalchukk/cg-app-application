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
                categoryId = action.payload.categoryId;

            newState[accountId] = Object.assign({}, newState[accountId]);
            newState[accountId][categoryId] = Object.assign({}, newState[accountId][categoryId], {
                categoryChildren: {}
            });

            return newState;
        },
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state),
                accountId = action.payload.accountId,
                categoryId = action.payload.categoryId,
                childCategories = action.payload.categories;

            newState[accountId] = Object.assign({}, newState[accountId]);
            newState[accountId][categoryId] = Object.assign({}, newState[accountId][categoryId], {
                categoryChildren: childCategories
            });

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
            // console.log(state, action);
            return state;
        }
    });
});
