import filtersReducer from 'MessageCentre/Reducers/filtersReducer';
import messagesReducer from 'MessageCentre/Reducers/messagesReducer';
import threadsReducer from 'MessageCentre/Reducers/threadsReducer';
import columnReducer from 'MessageCentre/Reducers/columnReducer';
import replyReducer from 'MessageCentre/Reducers/replyReducer';

import {combineReducers} from 'redux';

const appReducer = combineReducers({
    filters: filtersReducer,
    messages: messagesReducer,
    threads: threadsReducer,
    column: columnReducer,
    reply: replyReducer,
});

const combinedReducer = (state, action) => {
    return appReducer(state, action);
};

export default combinedReducer;