define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    var initialState = {};

    return reducerCreator(initialState, {
        "ACCOUNT_POLICIES_REFRESHED": function(state, action) {
            let newState = JSON.parse(JSON.stringify(state));
            newState.returnPolicies[action.payload.accountId] = action.payload.returnPolicies;
            return newState;
        }
    });
});
