define(['react'], function (React) {
    "use strict";

    var FilterLinkComponent = React.createClass({
        displayName: "FilterLinkComponent",

        getDefaultProps: function () {
            return {
                filter: "",
                active: false,
                onClick: null
            };
        },
        onClick: function (e) {
            e.preventDefault();
            if (!this.props.onClick) {
                return;
            }
            this.props.onClick(this.props.filter);
        },
        render: function () {
            if (this.props.active) {
                return React.createElement(
                    "span",
                    null,
                    this.props.children
                );
            }
            return React.createElement(
                "a",
                { href: "#",
                    onClick: this.onClick.bind(this)
                },
                this.props.children
            );
        }
    });

    return FilterLinkComponent;
});
