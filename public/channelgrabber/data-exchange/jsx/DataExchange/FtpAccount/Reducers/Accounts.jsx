import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    accounts: []
};

export default reducerCreator(initialState, {
    "UPDATE_INPUT_VALUE": (state, action) => {
        let newState = state.slice();
        newState[action.payload.index] = Object.assign({}, newState[action.payload.index], {
           [action.payload.property]: action.payload.newValue
        });
        return newState;
    },
    "ADD_NEW_ACCOUNT": (state, action) => {
        let newState = state.slice();
        newState.push(action.payload.account);
        return newState;
    },
    "ACCOUNT_DELETED_SUCCESSFULLY": (state, action) => {
        let newState = state.slice();
        newState.splice(action.payload.index, 1);
        return newState;
    },
    "ACCOUNT_DELETE_FAILED": (state, action) => {
        // No-op, the delete failed so nothing to do in the UI
        return state;
    },
    "ACCOUNT_UPDATED_SUCCESSFULLY": (state, action) => {
        let newState = state.slice();
        newState[action.payload.index] = Object.assign({}, newState[action.payload.index], {
            etag: action.payload.response.etag,
            id: action.payload.response.id
        });
        return newState;
    },
});
