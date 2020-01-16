import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    text: '',
    buttonSelectTitle: 'Send and Resolve',
}

const threadsReducer = reducerCreator(initialState, {
    'REPLY_INPUT_CHANGE': (state, action) => {
        let reply = {...state};

        reply.text = action.payload;

        return {...state, ...reply};
    },
    'REPLY_OPTION_SELECT': (state, action) => {
        let reply = {...state};

        reply.buttonSelectTitle = action.payload;

        return {...state, ...reply};
    },
    'ADD_MESSAGE_SUCCESS': (state, action) => {
        let reply = {...state};

        reply.text = '';

        return {...state, ...reply};
    },
});

export default threadsReducer;