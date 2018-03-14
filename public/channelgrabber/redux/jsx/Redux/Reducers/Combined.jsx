define([
    'redux',
    'redux-form',
    'Redux/Reducers/Todo',
    'Redux/Reducers/FilterLink'
], function(
    Redux,
    ReduxForm,
    TodoReducer,
    FilterLinkReducer
) {
    var combined = Redux.combineReducers({
        todos: TodoReducer,
        visibilityFilter: FilterLinkReducer,
        form: ReduxForm.reducer
    });
    return combined;
});