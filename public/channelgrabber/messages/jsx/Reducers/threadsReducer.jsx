import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
};

const threadsReducer = reducerCreator(initialState, {
    'THREADS_FETCH_SUCCESS': (state, action) => {
        let threads = {...state};
        action.payload.forEach(thread => {
            thread.messages = thread.messages.map(message => {
                return message.id;
            });
            threads.byId[thread.id] = thread;
        });
        threads.allIds = action.payload.map(thread => {
            return thread.id;
        });
        return {...state, ...threads};
    }
});

export default threadsReducer;