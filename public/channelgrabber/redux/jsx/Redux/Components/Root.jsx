define([
    'react',
    'Redux/Components/TodoListApp',
    'Redux/Components/ContactFormApp'
], function(
    React,
    TodoListApp,
    ContactFormApp
) {
    "use strict";

    var RootComponent = React.createClass({
        render: function()
        {
            return (
                <div style={{width: "500px"}}>
                    <TodoListApp />
                    <ContactFormApp />
                </div>
            );
        }
    });

    return RootComponent;
});