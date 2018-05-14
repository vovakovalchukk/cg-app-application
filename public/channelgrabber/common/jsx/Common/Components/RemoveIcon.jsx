define([
    'react'
], function(
    React
) {
    "use strict";

    return React.createClass({
        getDefaultProps: function() {
            return {
                onClick: null,
                disabled: false,
                className: "remove-icon"
            }
        },
        onClick: function (e) {
            if (this.props.disabled || !this.props.onClick) {
                return;
            }
            this.props.onClick(e);
        },
        getClassName() {
            return "fa fa-2x fa-minus-square" + (this.props.disabled ? ' inactive' : '');
        },
        render: function() {
            return (
                <span className={this.props.className}>
                    <i
                        className={this.getClassName()}
                        aria-hidden="true"
                        onClick={this.onClick}
                    />
                </span>
            );
        }
    });
});
