import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    initialAccounts: []
};

export default reducerCreator(initialState, {
    "ACCOUNT_UPDATED_SUCCESSFULLY": (state, action) => {
        let newState = state.slice();
        newState[action.payload.index] = Object.assign({}, action.payload.account, {
            etag: action.payload.response.etag,
            id: action.payload.response.id
        });
        return newState;
    },
});
