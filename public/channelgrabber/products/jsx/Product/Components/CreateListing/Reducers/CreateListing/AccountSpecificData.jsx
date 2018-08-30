define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    let initialState = {};

    let setPoliciesOnStateForAccount = function(state, accountId, policies) {
        return Object.assign({}, state, {
            [accountId]: Object.assign({}, state[accountId], {
                policies: {
                    isFetching: policies.isFetching,
                    returnPolicies: policies.returnPolicies,
                    paymentPolicies: policies.paymentPolicies,
                    shippingPolicies: policies.shippingPolicies
                }
            })
        });
    };

    return reducerCreator(initialState, {
        "FETCH_ACCOUNT_POLICIES": function(state, action) {
            return setPoliciesOnStateForAccount(state, action.payload.accountId, {
                isFetching: true,
                returnPolicies: [],
                paymentPolicies: [],
                shippingPolicies: []
            });
        },
        "ACCOUNT_POLICIES_FETCHED": function(state, action) {
            return setPoliciesOnStateForAccount(state, action.payload.accountId, Object.assign(action.payload.policies, {
                isFetching: false
            }));
        },
        "SET_POLICIES_FOR_ACCOUNT": function(state, action) {
            return setPoliciesOnStateForAccount(state, action.payload.accountId, Object.assign(action.payload.policies, {
                isFetching: false
            }));
        },
        "ASSIGN_SEARCH_PRODUCT_TO_CG_PRODUCT": function(state, action) {
            console.log('TEST', state, action);
            return {};
        }
    });
});
