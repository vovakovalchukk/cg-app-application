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
    AccountSpecificData
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer,
        accountSpecificData: AccountSpecificData
    });

    return CombinedReducer;
});
