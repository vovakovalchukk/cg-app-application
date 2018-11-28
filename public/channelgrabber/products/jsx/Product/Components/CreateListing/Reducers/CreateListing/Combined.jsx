import {combineReducers} from 'redux';
import {reducer as reduxFormReducer} from 'redux-form';
import InitialValuesReducer from './InitialValues';
import SubmissionStatusesReducer from './SubmissionStatusesReducer';
import AccountsReducer from './AccountSpecificData';
import CategoryTemplatesReducer from './CategoryTemplates';
import ProductSearchReducer from './ProductSearch';
    
    var CombinedReducer = combineReducers({
        accountsData: AccountsReducer,
        form: reduxFormReducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer,
        categoryTemplates: CategoryTemplatesReducer,
        productSearch: ProductSearchReducer
    });

    export default CombinedReducer;
