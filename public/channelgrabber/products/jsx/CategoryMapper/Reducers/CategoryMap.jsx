define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/CategorySelect'
], function(
    Redux,
    ReduxForm,
    CategorySelect
) {
    var CategoryMapReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        categorySelect: CategorySelect
    });
    return CategoryMapReducer;
});
