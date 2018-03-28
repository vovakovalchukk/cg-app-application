define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_SELECTED": function (state, action) {
            var newState = Object.assign({}, state);
            var accountId = action.payload.accountId;
            newState[accountId].categories.splice(action.payload.categoryLevel + 1);
            newState[accountId].resetSelection = false;
            return newState;
        },
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state);
            var accountId = action.payload.accountId;
            var categories = state[accountId].categories.slice();
            newState[accountId] = Object.assign({}, newState[accountId]);
            categories.push(action.payload.categories);
            newState[accountId].categories = categories;
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
