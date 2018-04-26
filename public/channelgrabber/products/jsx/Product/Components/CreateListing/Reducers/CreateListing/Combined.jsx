define([
    'redux',
    'redux-form'
], function(
    Redux,
    ReduxForm
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer
    });

    return CombinedReducer;
});
