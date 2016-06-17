define([
    'React'
], function(
    React
) {
    "use strict";

    var TemplateComponent = React.createClass({
        displayName: "HelloMessage",

        render: function render() {
            return React.createElement(
                "div",
                null,
                "Hello ",
                this.props.name
            );
        }
    });

    return TemplateComponent;
});