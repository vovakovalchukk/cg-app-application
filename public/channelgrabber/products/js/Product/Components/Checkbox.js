define(['react'], function (React) {
    "use strict";

    var CheckboxComponent = React.createClass({
        displayName: "CheckboxComponent",

        render: function () {
            return React.createElement(
                "div",
                { className: "checkbox-container" },
                React.createElement(
                    "div",
                    { className: "checkbox-holder bulk-action-checkbox" },
                    React.createElement(
                        "a",
                        { className: "std-checkbox" },
                        React.createElement("input", { type: "checkbox", id: "product-checkbox-input-" + this.props.id, name: "" }),
                        React.createElement(
                            "label",
                            { htmlFor: "product-checkbox-input-" + this.props.id },
                            React.createElement("span", { className: "checkbox_label" })
                        )
                    )
                )
            );
        }
    });

    return CheckboxComponent;
});
