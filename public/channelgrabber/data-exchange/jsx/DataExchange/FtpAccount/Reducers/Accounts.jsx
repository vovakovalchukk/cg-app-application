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
    }
});
