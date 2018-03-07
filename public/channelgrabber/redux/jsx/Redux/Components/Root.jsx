define([
    'react',
    'Redux/Containers/AddTodo',
    'Redux/Containers/TodoList',
    'Redux/Components/FilterLinks'
], function(
    React,
    AddTodoContainer,
    TodoListContainer,
    FilterLinksComponent,
) {
    "use strict";

    var RootComponent = React.createClass({
        render: function()
        {
            return (
                <div style={{width: "500px"}}>
                    <AddTodoContainer />
                    <TodoListContainer />
                    <FilterLinksComponent />
                </div>
            );
        }
    });

    return RootComponent;
});