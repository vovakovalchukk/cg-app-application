define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/CategoryMaps',
    'CategoryMapper/Reducers/Categories',
    'Product/Components/CreateListing/Reducers/Accounts',
    'Product/Components/CreateListing/Reducers/AddNewCategoryVisible',
    'Product/Components/CreateListing/Reducers/CategoryTemplateOptions',
], function(
    Redux,
    ReduxForm,
    CategoryMapsReducer,
    CategoriesReducer,
    AccountsReducer,
    AddNewCategoryVisibleReducer,
    CategoryTemplateOptionsReducer
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
        addNewCategoryVisible: AddNewCategoryVisibleReducer
    });

    return CombinedReducer;
});
