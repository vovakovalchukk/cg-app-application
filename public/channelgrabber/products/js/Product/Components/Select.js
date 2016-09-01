define(['react'], function (React) {
    "use strict";

    var SelectComponent = React.createClass({
        displayName: "SelectComponent",

        getDefaultProps: function () {
            return {
                options: []
            };
        },
        getInitialState: function () {
            return {
                currentSelection: {},
                active: false
            };
        },
        onClick: function (e) {
            this.setState({
                active: !this.state.active
            });
        },
        onOptionSelected: function (e) {
            var selectedOption = this.props.options.find(function (option) {
                if (option.value === e.target.value) {
                    return option;
                }
            });
            this.setState({
                currentSelection: selectedOption
            });
            this.props.onNewOption(selectedOption);
        },
        render: function () {
            var options = this.props.options.map(function (opt) {
                return React.createElement(
                    "li",
                    { className: "custom-select-item " + (opt.selected ? "active" : ""), value: opt.value, key: opt.value, onClick: this.onOptionSelected },
                    React.createElement(
                        "a",
                        { value: opt.value },
                        opt.name
                    )
                );
            }.bind(this));
            return React.createElement(
                "div",
                { className: "custom-select " + (this.state.active ? 'active' : ''), onClick: this.onClick },
                React.createElement(
                    "div",
                    { className: "selected" },
                    React.createElement(
                        "span",
                        { className: "selected-content" },
                        React.createElement(
                            "b",
                            null,
                            this.props.prefix ? this.props.prefix + ": " : ""
                        ),
                        this.state.currentSelection.name ? this.state.currentSelection.name : this.props.defaultValue
                    ),
                    React.createElement(
                        "span",
                        { className: "sprite-arrow-down-10-black" },
                        " "
                    )
                ),
                React.createElement(
                    "div",
                    { className: "animated fadeInDown open-content" },
                    React.createElement(
                        "ul",
                        null,
                        options
                    )
                )
            );
        }
    });

    return SelectComponent;
});
