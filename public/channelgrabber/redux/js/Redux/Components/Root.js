define(['react'], function (React) {
    "use strict";

    var RootComponent = React.createClass({
        displayName: "RootComponent",

        getDefaultProps: function () {
            return {};
        },
        getInitialState: function () {
            return {};
        },
        render: function () {
            return React.createElement(
                "div",
                null,
                "Hello World!"
            );
        }
    });

    return RootComponent;
});
