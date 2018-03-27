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
                class: "remove-icon"
            }
        },
        onClick: function (e) {
            if (this.props.disabled || !this.props.onClick) {
                return;
            }
            this.props.onClick(e);
        },
        getClassName() {
            return "fa fa-2x fa-times fa-trash-o" + (this.props.disabled ? ' inactive' : '');
        },
        render: function() {
            return (
                <span className={this.props.class}>
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
