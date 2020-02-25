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
                if (thread.channel.toLowerCase() === 'amazon') {
                    message.body = message.body.nl2br();
                }
                messages.byId[message.id] = message;
                messages.allIds.push(message.id);
            });
        });

        return {...state, ...messages};
    },
    'ADD_MESSAGE_SUCCESS': (state, action) => {
        let messages = {...state};

        const newMessage = action.payload.messageEntity;

        messages.byId[newMessage.id] = newMessage;

        messages.allIds.push(newMessage.id);

        return {...state, ...messages};
    }
});

export default messagesReducer;
