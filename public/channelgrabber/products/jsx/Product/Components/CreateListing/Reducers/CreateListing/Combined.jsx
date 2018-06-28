define([
    'redux',
    'redux-form',
    './InitialValues',
    './SubmissionStatusesReducer',
    './AccountSpecificData'
], function(
    Redux,
    ReduxForm,
    InitialValuesReducer,
    SubmissionStatusesReducer,
    AccountsReducer
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        accountsData: AccountsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer
    });

    return CombinedReducer;
});
