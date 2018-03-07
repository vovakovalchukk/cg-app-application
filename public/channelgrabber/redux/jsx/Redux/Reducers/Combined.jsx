define([
    'redux',
    'Redux/Reducers/Todo',
    'Redux/Reducers/FilterLink'
], function(
    Redux,
    TodoReducer,
    FilterLinkReducer
) {
    var combined = Redux.combineReducers({
        todos: TodoReducer,
        visibilityFilter: FilterLinkReducer
    });
    return combined;
});