import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
};

const messagesReducer = reducerCreator(initialState, {
    'TEMPLATES_SET': (state, action) => {
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
    }
});

export default messagesReducer;