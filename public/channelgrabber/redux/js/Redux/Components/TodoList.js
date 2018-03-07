define(['react', 'Redux/Components/Todo'], function (React, Todo) {
    "use strict";

    var TodoListComponent = React.createClass({
        displayName: 'TodoListComponent',

        getDefaultProps: function () {
            return {
                todos: [],
                onTodoClick: null
            };
        },
        onTodoClick: function (id) {
            if (!this.props.onTodoClick) {
                return;
            }
            this.props.onTodoClick(id);
        },
        render: function () {
            return React.createElement(
                'ul',
                null,
                this.props.todos.map(function (todo) {
                    return React.createElement(Todo, {
                        id: todo.id,
                        text: todo.text,
                        completed: todo.completed,
                        onClick: this.onTodoClick.bind(this, todo.id)
                    });
                }.bind(this))
            );
        }
    });

    return TodoListComponent;
});
