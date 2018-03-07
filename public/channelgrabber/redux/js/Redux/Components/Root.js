define(['react', 'Redux/Containers/AddTodo', 'Redux/Containers/TodoList'], function (React, AddTodoContainer, TodoListContainer) {
    "use strict";

    var RootComponent = React.createClass({
        displayName: 'RootComponent',

        render: function () {
            return React.createElement(
                'div',
                { style: { width: "500px" } },
                React.createElement(AddTodoContainer, null),
                React.createElement(TodoListContainer, null)
            );
        }
    });

    return RootComponent;
});
