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
                class: "refresh-icon"
            }
        },
        onClick: function (e) {
            if (this.props.disabled || !this.props.onClick) {
                return;
            }
            this.props.onClick(e);
        },
        getClassName() {
            return "fa fa-2x fa-repeat icon-create-listing" + (this.props.disabled ? ' inactive' : '');
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
