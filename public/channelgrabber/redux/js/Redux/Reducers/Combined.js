define(['redux', 'Redux/Reducers/Todo'], function (Redux, TodoReducer) {
    var combined = Redux.combineReducers({
        todo: TodoReducer
    });
    return combined;
});
