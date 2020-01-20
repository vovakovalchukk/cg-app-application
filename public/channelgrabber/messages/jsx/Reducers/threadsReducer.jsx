import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
    searchBy: '',
    viewing: '',
};

const threadsReducer = reducerCreator(initialState, {
    'THREADS_FETCH_SUCCESS': (state, action) => {
        let threads = {...state};

        threads.byId = {};

        action.payload.forEach((thread) => {
            let newThread = {...thread};
            newThread.messages = newThread.messages.map(message => {
                return message.id;
            });
            threads.byId[newThread.id] = newThread;
        });

        threads.allIds = action.payload.map(thread => {
            return thread.id;
        });

        let newState = {...state, ...threads};

        return newState ;
    },
    'SEARCH_INPUT_CHANGED': (state, action) => {
        let threads = {...state};

        threads.searchBy = action.payload;

        return {...state, ...threads};
    },
    'SAVE_STATUS_SUCCESS': (state, action) => {
        let threads = {...state};

        threads.byId[action.payload.id] = action.payload;

        return {...state, ...threads};
    },
    'ADD_MESSAGE_SUCCESS': (state, action) => {
        let threads = {...state};

        const newMessage = action.payload.messageEntity;

        const thread = threads.byId[newMessage.threadId];

        thread.messages.push(newMessage.id);

        return {...state, ...threads};
    },
    'ASSIGN_USER_SUCCESS': (state, action) => {
        let threads = {...state};

        threads.byId[action.payload.id] = action.payload;

        return {...state, ...threads};
    },
});

export default threadsReducer;
