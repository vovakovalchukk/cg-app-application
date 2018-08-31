define([
    'redux',
    'redux-form',
    './InitialValues',
    './SubmissionStatusesReducer',
    './AccountSpecificData',
    './CategoryTemplates',
    './ProductSearch',
], function(
    Redux,
    ReduxForm,
    InitialValuesReducer,
    SubmissionStatusesReducer,
    AccountsReducer,
    CategoryTemplatesReducer,
    ProductSearchReducer
) {
    "use strict";

    return Redux.combineReducers({
        accountsData: AccountsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer,
        categoryTemplates: CategoryTemplatesReducer,
        productSearch: ProductSearchReducer
    });
});
