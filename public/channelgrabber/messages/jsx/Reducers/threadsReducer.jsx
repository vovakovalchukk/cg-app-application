import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
    searchBy: '',
    secondaryReplyActionsVisible: false,
    replyText: '',
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
    'REPLY_ACTIONS_TOGGLE_VISIBILITY': (state, action) => {
        let threads = {...state};

        threads.secondaryReplyActionsVisible = action.payload;

        return {...state, ...threads};
    },
    'REPLY_INPUT_CHANGED': (state, action) => {
        let threads = {...state};

        threads.replyText = action.payload;

        return {...state, ...threads};
    },
});

export default threadsReducer;