define([
    'redux',
    'redux-form',
    './Products'
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
