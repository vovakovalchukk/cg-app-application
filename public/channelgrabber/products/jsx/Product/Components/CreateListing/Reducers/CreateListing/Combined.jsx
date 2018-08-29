define([
    'redux',
    'redux-form',
    './InitialValues',
    './SubmissionStatusesReducer',
    './AccountSpecificData',
    './CategoryTemplates'
], function(
    Redux,
    ReduxForm,
    InitialValuesReducer,
    SubmissionStatusesReducer,
    AccountsReducer,
    CategoryTemplatesReducer
) {
    "use strict";

    return Redux.combineReducers({
        accountsData: AccountsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer,
        categoryTemplates: CategoryTemplatesReducer
    });
});
