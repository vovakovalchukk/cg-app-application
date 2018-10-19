import Redux from 'redux';
import ReduxForm from 'redux-form';
import InitialValuesReducer from './InitialValues';
import SubmissionStatusesReducer from './SubmissionStatusesReducer';
import AccountsReducer from './AccountSpecificData';
import CategoryTemplatesReducer from './CategoryTemplates';
import ProductSearchReducer from './ProductSearch';
    

    export default Redux.combineReducers({
        accountsData: AccountsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer,
        categoryTemplates: CategoryTemplatesReducer,
        productSearch: ProductSearchReducer
    });

