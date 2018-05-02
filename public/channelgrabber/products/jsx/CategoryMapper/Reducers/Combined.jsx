define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/Accounts',
    'CategoryMapper/Reducers/CategoryMaps',
    'CategoryMapper/Reducers/Categories',
    'CategoryMapper/Reducers/InitialValues',
    'CategoryMapper/Reducers/PaginationReducer'
], function(
    Redux,
    ReduxForm,
    AccountsReducer,
    CategoryMapsReducer,
    CategoriesReducer,
    InitialValuesReducer,
    PaginationReducer
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        accounts: AccountsReducer,
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        pagination: PaginationReducer
    });
    return CombinedReducer;
});
