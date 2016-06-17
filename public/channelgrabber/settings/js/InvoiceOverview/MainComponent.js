define([
    'react'
], function(
    React
) {
    "use strict";

    var MainComponent = React.createClass({
        displayName: "HelloMessage",

        render: function render() {
            console.log("So Reactive!");
            return React.createElement(
                "div",
                null,
                "Hello ",
                this.props.name
            );
        }
    });

    return MainComponent;
});