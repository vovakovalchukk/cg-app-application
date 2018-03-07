define(['react', 'Redux/Containers/AddTodo', 'Redux/Containers/TodoList', 'Redux/Components/FilterLinks'], function (React, AddTodoContainer, TodoListContainer, FilterLinksComponent) {
    "use strict";

    var RootComponent = React.createClass({
        displayName: 'RootComponent',

        render: function () {
            return React.createElement(
                'div',
                { style: { width: "500px" } },
                React.createElement(AddTodoContainer, null),
                React.createElement(TodoListContainer, null),
                React.createElement(FilterLinksComponent, null)
            );
        }
    });

    return RootComponent;
});
