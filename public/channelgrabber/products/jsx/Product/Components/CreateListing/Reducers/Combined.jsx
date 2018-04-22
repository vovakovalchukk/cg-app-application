define([
    'redux',
    'redux-form',
    'Product/Components/CreateListing/Reducers/Accounts'
], function(
    Redux,
    ReduxForm,
    AccountsReducer
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        accounts: AccountsReducer,
        categoryTemplateOptions: function (state) {
            return state ? state : {};
        },
        initialValues: function (state) {
            return state ? state : {};
        }
    });

    return CombinedReducer;
});
