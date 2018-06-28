define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    var initialState = {};

    return reducerCreator(initialState, {
        "FETCH_ACCOUNT_POLICIES": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, state[action.payload.accountId], {
                    policies: {
                        isFetching: true
                    }
                })
            });
        },
        "ACCOUNT_POLICIES_FETCHED": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, state[action.payload.accountId], {
                    policies: action.payload.policies
                })
            });
        }
    });
});
