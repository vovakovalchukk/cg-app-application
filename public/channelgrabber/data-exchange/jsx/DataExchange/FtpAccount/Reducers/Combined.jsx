import {combineReducers} from 'redux';
import FtpAccounts from './FtpAccounts';

let CombinedReducer = combineReducers({
    ftpAccounts: FtpAccounts
});

export default CombinedReducer;
