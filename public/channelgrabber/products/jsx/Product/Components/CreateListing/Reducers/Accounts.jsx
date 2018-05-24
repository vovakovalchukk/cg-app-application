define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_ROOTS_FETCHED": function(state, action) {
            var newState = Object.assign({}, state),
                accountCategories = action.payload.accountCategories;

            accountCategories.forEach(function(categories) {
                if (!(categories.accountId in newState)) {
                    return;
                }
                newState[categories.accountId] = Object.assign({}, newState[categories.accountId]);
                newState[categories.accountId].categories = categories.categories;
            });

            return newState;
        },
        "FETCH_SETTINGS_FOR_ACCOUNT": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, state[action.payload.accountId], {
                    isFetching: true
                })
            });
        },
        "ACCOUNT_SETTINGS_FETCHED": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, state[action.payload.accountId], {
                    isFetching: false
                })
            });
        },
        "NO_ACCOUNT_SETTINGS": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, state[action.payload.accountId], {
                    isFetching: false
                })
            });
        }
    });
});
