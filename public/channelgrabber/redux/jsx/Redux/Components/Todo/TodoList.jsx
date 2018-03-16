define([
    'react',
    'Redux/Components/Todo/Todo'
], function(
    React,
    Todo
) {
    "use strict";

    var TodoListComponent = React.createClass({
        getDefaultProps: function() {
            return {
                todos: [],
                onTodoClick: null
            };
        },
        onTodoClick: function(id) {
            if (!this.props.onTodoClick) {
                return;
            }
            this.props.onTodoClick(id);
        },
        render: function()
        {
            return (
                <ul>
                    {this.props.todos.map(function(todo) {
                        return (
                            <Todo
                                id={todo.id}
                                text={todo.text}
                                completed={todo.completed}
                                onClick={this.onTodoClick.bind(this, todo.id)}
                            />
                        );
                    }.bind(this))}
                </ul>
            );
        }
    });

    return TodoListComponent;
});