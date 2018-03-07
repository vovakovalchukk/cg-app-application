define(['Redux/Reducers/creator'], function (reducerCreator) {
    var initialState = [];
    var Todo = reducerCreator(initialState, {
        "ADD": function (state, action) {
            var newState = state.slice(0);
            newState.push({
                id: state.length + 1,
                text: action.payload.text,
                completed: false
            });
            return newState;
        },
        "TOGGLE": function (state, action) {
            return state.map(function (todo) {
                if (todo.id != action.payload.id) {
                    return todo;
                }
                var newTodo = JSON.parse(JSON.stringify(todo));
                newTodo.completed = !todo.completed;
                return newTodo;
            });
        }
    });

    return Todo;
});
