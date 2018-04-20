define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/CategoryMaps',
    'CategoryMapper/Reducers/Categories',
    'CategoryMapper/Reducers/InitialValues',
    'CategoryMapper/Reducers/PaginationReducer'
], function(
    Redux,
    ReduxForm,
    CategoryMapsReducer,
    CategoriesReducer,
    InitialValuesReducer,
    PaginationReducer
) {
    var CombinedReducer = Redux.combineReducers({
        accounts: function (state) {
            return state ? state : {};
        },
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer,
        pagination: PaginationReducer
    });
    return CombinedReducer;
});
