define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/CategoryMaps',
    'CategoryMapper/Reducers/Categories'
], function(
    Redux,
    ReduxForm,
    CategoryMapsReducer,
    CategoriesReducer
) {
    var CombinedReducer = Redux.combineReducers({
        accounts: function (state) {
            if (!state) {
                return {};
            }
            return state;
        },
        categories: CategoriesReducer,
        form: ReduxForm.reducer,
        categoryMaps: CategoryMapsReducer
    });
    return CombinedReducer;
});
