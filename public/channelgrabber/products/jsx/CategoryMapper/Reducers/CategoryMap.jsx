define([
    'redux',
    'redux-form'
], function(
    Redux,
    ReduxForm
) {
    var CategoryMapReducer = Redux.combineReducers({
        form: ReduxForm.reducer
    });
    return CategoryMapReducer;
});