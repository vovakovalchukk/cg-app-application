import filtersReducer from 'MessageCentre/Reducers/filtersReducer';
import messagesReducer from 'MessageCentre/Reducers/messagesReducer';
import threadsReducer from 'MessageCentre/Reducers/threadsReducer';
import columnReducer from 'MessageCentre/Reducers/columnReducer';
import templatesReducer from 'MessageCentre/Reducers/templatesReducer';
import replyReducer from 'MessageCentre/Reducers/replyReducer';
import searchReducer from 'MessageCentre/Reducers/searchReducer';

import {combineReducers} from 'redux';

const appReducer = combineReducers({
    filters: filtersReducer,
    messages: messagesReducer,
    threads: threadsReducer,
    column: columnReducer,
    templates: templatesReducer,
    reply: replyReducer,
    search: searchReducer,
});

const combinedReducer = (state, action) => {
    return appReducer(state, action);
};

export default combinedReducer;