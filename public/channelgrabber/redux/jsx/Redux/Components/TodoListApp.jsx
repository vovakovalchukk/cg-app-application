define([
    'react',
    'Redux/Containers/Todo/AddTodo',
    'Redux/Containers/Todo/TodoList',
    'Redux/Components/Todo/FilterLinks'
], function(
    React,
    AddTodoContainer,
    TodoListContainer,
    FilterLinksComponent
) {
    "use strict";

    var TodoListApp = React.createClass({
        render: function()
        {
            return (
                <div>
                    <h1>Todo list</h1>
                    <AddTodoContainer />
                    <TodoListContainer />
                    <FilterLinksComponent />
                </div>
            );
        }
    });

    return TodoListApp;
});