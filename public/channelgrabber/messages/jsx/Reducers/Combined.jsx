import statusReducer from 'MessageCentre/Reducers/statusReducer';
import messagesReducer from 'MessageCentre/Reducers/messagesReducer';
import threadsReducer from 'MessageCentre/Reducers/threadsReducer';
import columnReducer from 'MessageCentre/Reducers/columnReducer';

import {combineReducers} from 'redux';

const appReducer = combineReducers({
    status: statusReducer,
    messages: messagesReducer,
    threads: threadsReducer,
    column: columnReducer,
});

const combinedReducer = (state, action) => {
    return appReducer(state, action);
};

export default combinedReducer;