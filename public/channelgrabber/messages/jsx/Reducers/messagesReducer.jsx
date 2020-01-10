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

        // Why is state just returning threads?

        // The new message is contained inside "action.payload.messageEntity"

        // TODO:
        // 1. The new message id will need to be added to the messages.allIds array in state
        // 2. The new message will need to be added to the messages.byId object

        return null;
    }
});

export default messagesReducer;