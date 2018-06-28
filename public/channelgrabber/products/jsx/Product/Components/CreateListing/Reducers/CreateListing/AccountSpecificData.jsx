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
                        isFetching: true,
                        returnPolicies: []
                    }
                })
            });
        },
        "ACCOUNT_POLICIES_FETCHED": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, state[action.payload.accountId], {
                    policies: {
                        isFetching: false,
                        returnPolicies: action.payload.returnPolicies
                    }
                })
            });
        },
        "SET_RETURN_POLICIES_FOR_ACCOUNT": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, state[action.payload.accountId], {
                    policies: {
                        isFetching: false,
                        returnPolicies: action.payload.returnPolicies
                    }
                })
            });
        }
    });
});
