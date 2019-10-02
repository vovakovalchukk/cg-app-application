import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    emailAccounts: {}
};

export default reducerCreator(initialState, {
    "CHANGE_EMAIL_ADDRESS": (state, action) => {
        let accountsForType = state[action.payload.type].slice();

        accountsForType[action.payload.index] = Object.assign({}, accountsForType[action.payload.index], {
            newAddress: action.payload.newAddress
        });

        return Object.assign({}, state, {
            [action.payload.type]: accountsForType
        });
    },
    "ADD_NEW_EMAIL_ACCOUNT": (state, action) => {
        let accountsForType = state[action.payload.type].slice();

        accountsForType.push(action.payload.account);

        return Object.assign({}, state, {
            [action.payload.type]: accountsForType
        });
    },
    "ACCOUNT_DELETED_SUCCESSFULLY": (state, action) => {
        let accountsForType = state[action.payload.type].slice();

        accountsForType.splice(action.payload.index, 1);

        return Object.assign({}, state, {
            [action.payload.type]: accountsForType
        });
    },
    "ACCOUNT_DELETE_FAILED": (state, action) => {
        // No-op, the delete failed so nothing to do in the UI
        return state;
    },
    "ACCOUNT_SAVED_SUCCESSFULLY": (state, action) => {
        let accountsForType = state[action.payload.type].slice(),
            account = Object.assign({}, action.payload.account, {
                address: action.payload.account.newAddress
            });

        accountsForType[action.payload.index] = Object.assign({}, accountsForType[action.payload.index], account);

        return Object.assign({}, state, {
            [action.payload.type]: accountsForType
        });
    },
    "ACCOUNT_SAVE_FAILED": (state, action) => {
        let accountsForType = state[action.payload.type].slice();

        accountsForType[action.payload.index] = Object.assign({}, accountsForType[action.payload.index], {
            newAddress: action.payload.account.address
        });

        return Object.assign({}, state, {
            [action.payload.type]: accountsForType
        });
    },
    "ACCOUNT_VERIFICATION_UPDATE": (state, action) => {
        let accountsForType = state[action.payload.type].slice();

        accountsForType[action.payload.index] = Object.assign({}, accountsForType[action.payload.index], {
            verificationStatus: action.payload.verificationStatus
        });

        return Object.assign({}, state, {
            [action.payload.type]: accountsForType
        });
    }
});
