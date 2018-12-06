import reducerCreator from 'Common/Reducers/creator';
"use strict";

let initialState = {
    features: {},
    accounts: {},
    'getAccounts': state => state.accounts.accounts,
    'getFeatures': state => state.accounts.features
};

let accountsReducer = reducerCreator(initialState, {
    "ACCOUNT_FEATURES_STORE": function(state, action) {
        console.log('in ACCOUNT_FEATURES_sTORE -R with action :  ' , action);
        return Object.assign({}, state, {
            features: action.payload.features
        });
    },
    "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
        let accounts = {};
        Object.keys(action.payload.accounts).forEach(accountId => {
            let account = action.payload.accounts[accountId];
            if (!account.active || account.deleted) {
                return;
            }
            accounts[account.id] = account;
        });
        return Object.assign({}, state, {
            accounts: accounts
        });
    }
});

export default accountsReducer;