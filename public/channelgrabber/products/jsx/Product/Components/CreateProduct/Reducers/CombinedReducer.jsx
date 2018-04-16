define([
    'redux',
    'redux-form',

], function (
    Redux,
    ReduxForm,
    ) {
        var CombinedReducer = Redux.combineReducers({
            form: ReduxForm.reducer
        });
        return CombinedReducer;
    });