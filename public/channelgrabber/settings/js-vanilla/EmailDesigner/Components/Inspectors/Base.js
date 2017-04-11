define(['react'], function (React) {
    "use strict";

    var BaseComponent = React.createClass({
        displayName: "BaseComponent",

        render: function () {
            return React.createElement(
                "div",
                { id: "heading-inspector", className: "inspector-module" },
                React.createElement(
                    "div",
                    { className: "inspector-holder" },
                    React.createElement(
                        "span",
                        { className: "heading-medium" },
                        this.props.heading
                    ),
                    this.props.children
                )
            );
        }
    });

    return BaseComponent;
});
