import {combineReducers} from 'redux';
import Accounts from './Accounts';
import InitialAccounts from "./InitialAccounts";

let CombinedReducer = combineReducers({
    accounts: Accounts,
    initialAccounts: InitialAccounts
});

export default CombinedReducer;
