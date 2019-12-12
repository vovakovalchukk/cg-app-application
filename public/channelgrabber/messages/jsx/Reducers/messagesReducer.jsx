import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
};

const messagesReducer = reducerCreator(initialState, {
    'THREADS_FETCH_SUCCESS': (state, action) => {
        let messages = {...state};
        action.payload.forEach(thread => {
            thread.messages.forEach(message => {
                messages.byId[message.id] = message;
            });
        });
        messages.allIds = action.payload.map(message => {
            return message.id;
        });
        return {...state, ...messages};
    }
});

export default messagesReducer;