define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/CategoryMaps',
    'CategoryMapper/Reducers/Categories',
    'CategoryMapper/Reducers/InitialValues'
], function(
    Redux,
    ReduxForm,
    CategoryMapsReducer,
    CategoriesReducer,
    InitialValuesReducer
) {
    var CombinedReducer = Redux.combineReducers({
        accounts: function (state) {
            return state ? state : {};
        },
        categories: CategoriesReducer,
        categoryMaps: CategoryMapsReducer,
        form: ReduxForm.reducer,
        initialValues: InitialValuesReducer
    });
    return CombinedReducer;
});
