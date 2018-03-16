define([
    'react',
    'react-redux',
    'Redux/Actions/Todo',
    'Redux/Components/Todo/TodoList'
], function(
    React,
    ReactRedux,
    Actions,
    TodoListComponent
) {
    var getVisibleTodos = function(todos, filter) {
        switch (filter) {
            case 'SHOW_ALL':
                return todos;
            case 'SHOW_COMPLETED':
                return todos.filter(function(todo) {
                    return todo.completed;
                });
            case 'SHOW_ACTIVE':
                return todos.filter(function(todo) {
                    return !todo.completed;
                });
            default:
                throw new Error('Unknown filter: ' + filter);
        }
    };

    var mapStateToProps = function(state) {
        return {
            todos: getVisibleTodos(state.todos, state.visibilityFilter)
        };
    };

    var mapDispatchToProps = {
        onTodoClick: Actions.toggle
    };

    var TodoListConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return TodoListConnector(TodoListComponent);
});