import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
};

const threadsReducer = reducerCreator(initialState, {
    'THREADS_FETCH_SUCCESS': (state, action) => {
        let threads = {...state};
        //this is fine
        // console.log('payload', JSON.stringify(action.payload, null, 1))
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

        // console.log('THREADS_FETCH_SUCCESS', JSON.stringify(threads, null, 1));


        let newState = {...state, ...threads};
        console.log('newstate tfs', newState)
        return newState ;
    }
});

export default threadsReducer;