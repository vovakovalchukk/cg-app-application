define([
    'react',
    'Redux/Containers/AddTodo',
    'Redux/Containers/TodoList'
], function(
    React,
    AddTodoContainer,
    TodoListContainer
) {
    "use strict";

    var RootComponent = React.createClass({
        render: function()
        {
            return (
                <div style={{width: "500px"}}>
                    <AddTodoContainer />
                    <TodoListContainer />
                </div>
            );
        }
    });

    return RootComponent;
});