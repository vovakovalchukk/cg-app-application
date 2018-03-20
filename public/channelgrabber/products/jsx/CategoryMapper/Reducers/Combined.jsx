define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/AccountCategorySelect',
    'CategoryMapper/Reducers/CategoryMap'
], function(
    Redux,
    ReduxForm,
    AccountCategoryReducer,
    CategoryMapReducer
) {
    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        categoryMap: CategoryMapReducer
    });
    return CombinedReducer;
});
