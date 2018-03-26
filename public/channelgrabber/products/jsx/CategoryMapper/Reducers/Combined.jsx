define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/CategoryMap'
], function(
    Redux,
    ReduxForm,
    CategoryMapReducer
) {
    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        categoryMap: CategoryMapReducer
    });
    return CombinedReducer;
});
