define([
    'redux',
    'redux-form',
    'Product/Components/CreateListing/Reducers/Accounts',
    'CategoryMapper/Reducers/CategoryMaps',
    'CategoryMapper/Reducers/Categories'
], function(
    Redux,
    ReduxForm,
    AccountsReducer,
    CategoryMapsReducer,
    CategoriesReducer
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        categoryTemplateOptions: function (state) {
            return state ? state : {};
        },
        initialValues: function (state) {
            return state ? state : {};
        },
        form: ReduxForm.reducer,
        accounts: AccountsReducer,
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
    });

    return CombinedReducer;
});
