define([
    'redux',
    'redux-form',
    'Product/Components/CreateListing/Reducers/Accounts',
    'Product/Components/CreateListing/Reducers/CategoryTemplateOptions',
    'CategoryMapper/Reducers/CategoryMaps',
    'CategoryMapper/Reducers/Categories'
], function(
    Redux,
    ReduxForm,
    AccountsReducer,
    CategoryTemplateOptionsReducer,
    CategoryMapsReducer,
    CategoriesReducer
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        initialValues: function (state) {
            return state ? state : {};
        },
        categoryTemplateOptions: CategoryTemplateOptionsReducer,
        form: ReduxForm.reducer,
        accounts: AccountsReducer,
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
    });

    return CombinedReducer;
});
