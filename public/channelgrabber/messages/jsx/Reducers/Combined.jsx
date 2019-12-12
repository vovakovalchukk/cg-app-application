import filtersReducer from 'MessageCentre/Reducers/filtersReducer';
import {combineReducers} from 'redux';

const appReducer = combineReducers({
    filters: filtersReducer,
});

const combinedReducer = (state, action) => {
    return appReducer(state, action);
};

export default combinedReducer;