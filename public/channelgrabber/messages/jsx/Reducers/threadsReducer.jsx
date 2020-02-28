import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
    viewing: '',
    loaded: false,
};

const threadsReducer = reducerCreator(initialState, {
    'THREADS_FETCH_START': (state) => {
        let threads = {...state};

        threads.loaded = false;

        return {...state, ...threads};
    },
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

        threads.loaded = true;

        let newState = {...state, ...threads};

        return newState ;
    },
    'THREAD_ORDER_COUNT_FETCH_SUCCESS': (state, action) => {
        let threads = {...state};

        let thread = threads.byId[threads.viewing];

        thread.ordersCount = action.payload;

        let newState = {...state, ...threads};

        return newState ;
    },
    'SAVE_STATUS_SUCCESS': (state, action) => {
        let threads = {...state};

        let thread = action.payload.thread;

        thread.messages = thread.messages.map(message => {
            return message.id;
        })

        threads.byId[thread.id] = thread;

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

        let thread = action.payload.thread;

        thread.messages = thread.messages.map(message => {
            return message.id;
        })

        threads.byId[thread.id] = thread;

        return {...state, ...threads};
    },
});

export default threadsReducer;
