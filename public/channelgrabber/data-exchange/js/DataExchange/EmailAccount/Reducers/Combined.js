import { combineReducers } from 'redux';
import { reducer as reduxFormReducer } from 'redux-form';
import EmailAccounts from './EmailAccounts';

let CombinedReducer = combineReducers({
    form: reduxFormReducer,
    emailAccounts: EmailAccounts
});

export default CombinedReducer;
