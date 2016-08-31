define(['react'], function (React) {
    "use strict";

    var ButtonComponent = React.createClass({
        displayName: "ButtonComponent",

        render: function () {
            return React.createElement(
                "div",
                { className: "button", onClick: this.props.onClick },
                this.props.text
            );
        }
    });

    return ButtonComponent;
});
