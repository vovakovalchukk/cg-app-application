import {combineReducers} from 'redux';
import {reducer as reduxFormReducer} from 'redux-form';
import InitialValuesReducer from './InitialValues';
import SubmissionStatusesReducer from './SubmissionStatusesReducer';
import AccountsReducer from './AccountSpecificData';
    

    var CombinedReducer = combineReducers({
        accountsData: AccountsReducer,
        form: reduxFormReducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer
    });

    export default CombinedReducer;

