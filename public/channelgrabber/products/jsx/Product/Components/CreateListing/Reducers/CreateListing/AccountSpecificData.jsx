import reducerCreator from 'Common/Reducers/creator';
    

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

    export default reducerCreator(initialState, {
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
        }
    });

