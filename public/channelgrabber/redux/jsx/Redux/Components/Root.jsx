define([
    'react',
    'Redux/Containers/AddTodo'
], function(
    React,
    AddTodo
) {
    "use strict";

    var RootComponent = React.createClass({
        getDefaultProps: function() {
            return {
            };
        },
        getInitialState: function()
        {
            return {
            };
        },
        render: function()
        {
            return (
                <div>
                    <AddTodo />
                </div>
            );
        }
    });

    return RootComponent;
});