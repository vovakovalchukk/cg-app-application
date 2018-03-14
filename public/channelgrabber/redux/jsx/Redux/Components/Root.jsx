define([
    'react',
    'Redux/Containers/AddTodo',
    'Redux/Containers/TodoList',
    'Redux/Components/FilterLinks',
    'Redux/Containers/ContactForm'
], function(
    React,
    AddTodoContainer,
    TodoListContainer,
    FilterLinksComponent,
    ContactFormContainer
) {
    "use strict";

    var RootComponent = React.createClass({
        contactSubmit: function(values) {
            console.log(values);
        },
        render: function()
        {
            return (
                <div style={{width: "500px"}}>
                    <h1>Todo list</h1>
                    <AddTodoContainer />
                    <TodoListContainer />
                    <FilterLinksComponent />
                    <h1>Contact Form</h1>
                    <ContactFormContainer onSubmit={this.contactSubmit} />
                </div>
            );
        }
    });

    return RootComponent;
});