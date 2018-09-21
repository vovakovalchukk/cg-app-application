import Redux from 'redux';
import ReduxForm from 'redux-form';
import InitialValuesReducer from './InitialValues';
import SubmissionStatusesReducer from './SubmissionStatusesReducer';
import AccountsReducer from './AccountSpecificData';
    

    var CombinedReducer = Redux.combineReducers({
        accountsData: AccountsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer
    });

    export default CombinedReducer;

