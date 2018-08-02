define([
    'redux',
    'redux-form'
], function(
    Redux,
    ReduxForm
) {
    "use strict";

    const CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer
    });

    return CombinedReducer;
});
