define(['redux', 'Redux/Reducers/Todo'], function (Redux, TodoReducer) {
    var combined = Redux.combineReducers({
        todos: TodoReducer
    });
    return combined;
});
