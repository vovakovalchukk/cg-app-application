define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    var initialState = {};

    return reducerCreator(initialState, {
        "LISTING_FORM_SUBMITTED_SUCCESSFUL": function(state, action) {
            return Object.assign({}, state, {
                guid: action.payload.guid
            });
        },
        "LISTING_FORM_SUBMITTED_ERROR": function(state, action) {
            n.error(action.payload.error);
            return state;
        },
        "LISTING_FORM_SUBMITTED_NOT_ALLOWED": function (state, action) {
            n.error("You do not have permission to do this.");
            return state;
        },
        "LISTING_PROGRESS_FETCHED": function(state, action) {
            var accounts = action.payload.accounts;
            var newState = Object.assign({}, state, {
                accounts: Object.assign({}, state.accounts)
            });
            var newAccountsState = newState.accounts;
            for (var accountId in accounts) {
                if (!newAccountsState[accountId]) {
                    newAccountsState[accountId] = {};
                }
                for (var categoryId in accounts[accountId].categories) {
                    newAccountsState[accountId][categoryId] = accounts[accountId].categories[categoryId];
                }
            }
            return newState;
        }
    });
});
