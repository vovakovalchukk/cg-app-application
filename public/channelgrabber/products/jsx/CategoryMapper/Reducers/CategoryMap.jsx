define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_SELECTED": function (state, action) {
            var newState = Object.assign({}, state);
            for (var accountId in newState) {
                if (action.payload.accountId != accountId) {
                    continue;
                }
                newState[accountId].categories.splice(action.payload.categoryLevel + 1);
            }
            return newState;
        },
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state);
            for (var accountId in newState) {
                if (action.payload.accountId != accountId) {
                    continue;
                }
                newState[accountId].categories.push(action.payload.categories);
            }
            return newState;
        }
    });
});
