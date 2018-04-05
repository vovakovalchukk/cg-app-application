define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state),
                accountId = action.payload.accountId,
                categoryId = action.payload.categoryId,
                childCategories = action.payload.categories,
                selectedCategories = action.payload.selectedCategories;

            newState[accountId] = Object.assign({}, newState[accountId]);
            if (categoryId in newState[accountId]) {
                newState[accountId][categoryId] = Object.assign({}, newState[accountId][categoryId], {
                    categoryChildren: childCategories
                });
            }

            var accountCategories = JSON.parse(JSON.stringify(newState[accountId])),
                categories = accountCategories;
            for (var i = 0; i < selectedCategories.length; i++) {
                if (categoryId == selectedCategories[i]) {
                    categories[categoryId] = Object.assign({}, categories[categoryId], {
                        categoryChildren: childCategories
                    });
                    break;
                }

                categories = categories[selectedCategories[i]].categoryChildren;
            }
            newState[accountId] = accountCategories;

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
