define(['react'], function (React) {
    var Pane = React.createClass({
        displayName: "Pane",

        render: function () {
            return React.createElement(
                "div",
                { className: "pane" },
                this.props.children
            );
        }
    });

    return Pane;
});
