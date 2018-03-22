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
                newState[accountId].categories.push(action.payload.categories);
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
                    tile: ''
                }];
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
                newState[accountId].categories = [action.payload.categories]
                break;
            }
            return newState;
        },
    });
});
