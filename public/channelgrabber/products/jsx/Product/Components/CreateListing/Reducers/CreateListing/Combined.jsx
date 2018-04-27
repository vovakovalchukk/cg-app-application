define([
    'redux',
    'redux-form',
    './InitialValues'
], function(
    Redux,
    ReduxForm,
    InitialValuesReducer
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer
    });

    return CombinedReducer;
});
