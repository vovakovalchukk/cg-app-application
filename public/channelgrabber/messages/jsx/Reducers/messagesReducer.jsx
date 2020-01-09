import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
};

const messagesReducer = reducerCreator(initialState, {
    'THREADS_FETCH_SUCCESS': (state, action) => {
        let messages = {...state};

        messages.byId = {};

        messages.allIds = [];

        action.payload.forEach(thread => {
            thread.messages.forEach(message => {
                messages.byId[message.id] = message;
                messages.allIds.push(message.id);
            });
        });
        
        return {...state, ...messages};
    },
    'ADD_MESSAGE_SUCCESS': (state, action) => {
        console.log('ADD_MESSAGE_SUCCESS state', state);
        console.log('ADD_MESSAGE_SUCCESS action', action);
        return null;
    }
});

export default messagesReducer;