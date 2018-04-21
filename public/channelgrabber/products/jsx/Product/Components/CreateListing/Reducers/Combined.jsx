define([
    'redux',
    'redux-form'
], function(
    Redux,
    ReduxForm
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        accounts: function (state) {
            return state ? state : {};
        },
        categoryTemplateOptions: function (state) {
            return state ? state : {};
        },
        initialValues: function (state) {
            return {};
        }
    });
    return CombinedReducer;
});
