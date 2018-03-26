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
                newState[accountId].resetSelection = false;
                break;
            }
            return newState;
        },
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state);
            for (var accountId in newState) {
                if (action.payload.accountId != accountId) {
                    continue;
                }
                newState[accountId] = Object.assign({}, newState[accountId]);
                var categories = state[accountId].categories.slice();
                categories.push(action.payload.categories);
                newState[accountId].categories = categories;
                break;
            }
            return newState;
        },
        "REFRESH_CATEGORIES": function (state, action) {
            var newState = Object.assign({}, state);
            for (var accountId in newState) {
                if (action.payload.accountId != accountId) {
                    continue;
                }
                newState[accountId].categories = [{
                    0: {tile: ''}
                }];
                newState[accountId].refreshing = true;
                break;
            }
            return newState;
        },
        "REFRESH_CATEGORIES_FETCHED": function (state, action) {
            var newState = Object.assign({}, state);
            for (var accountId in newState) {
                if (action.payload.accountId != accountId) {
                    continue;
                }
                newState[accountId].categories = [action.payload.categories];
                newState[accountId].refreshing = false;
                break;
            }
            return newState;
        },
        "REMOVE_ROOT_CATEGORY": function (state, action) {
            var newState = Object.assign({}, state);
            for (var accountId in newState) {
                if (action.payload.accountId != accountId) {
                    continue;
                }
                newState[accountId].categories.splice(1);
                newState[accountId].resetSelection = true;
            }
            return newState;
        }
    });
});
