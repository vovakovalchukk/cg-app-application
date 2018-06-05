define([
    'redux',
    'redux-form',
    './InitialValues',
    './SubmissionStatusesReducer'
], function(
    Redux,
    ReduxForm,
    InitialValuesReducer,
    SubmissionStatusesReducer
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        submissionStatuses: SubmissionStatusesReducer
    });

    return CombinedReducer;
});
