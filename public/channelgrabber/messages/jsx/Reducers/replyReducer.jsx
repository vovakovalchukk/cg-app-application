import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    text: '',
}

const threadsReducer = reducerCreator(initialState, {
    'REPLY_INPUT_CHANGED': (state, action) => {
        let reply = {...state};

        reply.text = action.payload;

        return {...state, ...reply};
    },
});

export default threadsReducer;