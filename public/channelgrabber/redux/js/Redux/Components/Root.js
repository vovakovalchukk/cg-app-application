define(['react', 'Redux/Containers/AddTodo'], function (React, AddTodo) {
    "use strict";

    var RootComponent = React.createClass({
        displayName: 'RootComponent',

        getDefaultProps: function () {
            return {};
        },
        getInitialState: function () {
            return {};
        },
        render: function () {
            return React.createElement(
                'div',
                null,
                React.createElement(AddTodo, null)
            );
        }
    });

    return RootComponent;
});
