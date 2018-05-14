define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    var initialState = {};

    return reducerCreator(initialState, {
        "REFRESH_CATEGORIES": function(state, action) {
            var newState = Object.assign({}, state),
                account = newState[action.payload.accountId];

            account = Object.assign({}, account, {
                refreshing: true
            });
            newState[action.payload.accountId] = account;

            return newState;
        },
        "REFRESH_CATEGORIES_FETCHED": function (state, action) {
            var newState = Object.assign({}, state),
                account = newState[action.payload.accountId];

            account = Object.assign({}, account, {
                refreshing: false
            });
            newState[action.payload.accountId] = account;

            return newState;
        },
    });
});
