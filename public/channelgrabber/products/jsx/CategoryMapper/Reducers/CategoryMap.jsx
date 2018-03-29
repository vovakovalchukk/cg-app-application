define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_SELECTED": function (state, action) {
            var newState = state.slice(0);
            var accountId = action.payload.accountId;
            var categoryMapIndex = action.payload.categoryMapIndex;
            var categoryLevel = action.payload.categoryLevel + 1;

            var newCategoryMap = Object.assign({}, newState[categoryMapIndex].categoryMap);

            newCategoryMap[accountId] = Object.assign({}, newCategoryMap[accountId]);

            var newCategoriesArray = newCategoryMap[accountId].categories.slice(0);
            newCategoriesArray.splice(categoryLevel);

            newCategoryMap[accountId].categories = newCategoriesArray;

            newCategoryMap[accountId].resetSelection = false;

            newState[categoryMapIndex].categoryMap = newCategoryMap;

            return newState;
        },
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = state.slice(0);
            var accountId = action.payload.accountId;
            var categoryMapIndex = action.payload.categoryMapIndex;
            var categoryLevel = action.payload.categoryLevel + 1;

            var newCategoryMap = Object.assign({}, newState[categoryMapIndex].categoryMap);

            newCategoryMap[accountId] = Object.assign({}, newCategoryMap[accountId]);

            var newCategoriesArray = newCategoryMap[accountId].categories.slice(0);
            newCategoriesArray.push(action.payload.categories);

            newCategoryMap[accountId].categories = newCategoriesArray;

            newCategoryMap[accountId].resetSelection = false;

            newState[categoryMapIndex].categoryMap = newCategoryMap;

            return newState;
        },
        "REFRESH_CATEGORIES": function (state, action) {
            var newState = Object.assign({}, state);
            var accountId = action.payload.accountId;
            newState[accountId].categories = [{
                0: {tile: ''}
            }];
            newState[accountId].refreshing = true;
            return newState;
        },
        "REFRESH_CATEGORIES_FETCHED": function (state, action) {
            var newState = Object.assign({}, state);
            var accountId = action.payload.accountId;
            newState[accountId].categories = [action.payload.categories];
            newState[accountId].refreshing = false;
            return newState;
        },
        "REMOVE_ROOT_CATEGORY": function (state, action) {
            var newState = Object.assign({}, state);
            var accountId = action.payload.accountId;
            newState[accountId].categories.splice(1);
            newState[accountId].resetSelection = true;
            return newState;
        }
    });
});
