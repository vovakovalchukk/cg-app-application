import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    emailAccounts: {}
};

export default reducerCreator(initialState, {
    "CHANGE_EMAIL_ADDRESS": (state, action) => {
        return Object.assign({}, state, {
            [action.payload.id]: Object.assign({}, state[action.payload.id], {
                newAddress: action.payload.newAddress
            })
        });
    },
});
