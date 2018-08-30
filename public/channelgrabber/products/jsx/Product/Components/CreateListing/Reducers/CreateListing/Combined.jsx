define([
    'redux',
    'redux-form',
    './InitialValues',
    './SubmissionStatusesReducer',
    './AccountSpecificData',
    './CategoryTemplates',
    './SelectedProducts',
], function(
    Redux,
    ReduxForm,
    InitialValuesReducer,
    SubmissionStatusesReducer,
    AccountsReducer,
    CategoryTemplatesReducer,
    SelectedProductsReducer
) {
    "use strict";

    return Redux.combineReducers({
        accountsData: AccountsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer,
        categoryTemplates: CategoryTemplatesReducer,
        selectedProducts: SelectedProductsReducer
    });
});
