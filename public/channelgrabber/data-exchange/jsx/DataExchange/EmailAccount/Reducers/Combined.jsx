import {combineReducers} from 'redux';
import EmailAccounts from './EmailAccounts';

let CombinedReducer = combineReducers({
    emailAccounts: EmailAccounts
});

export default CombinedReducer;
