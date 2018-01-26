define([
    'react'
], function(
    React
) {
    "use strict";

    return React.createClass({
        getDefaultProps: function() {
            return {
                onClick: function() {return false},
                disabled: false,
                class: "refresh-icon"
            }
        },
        onClick: function (e) {
            if (this.props.disabled) {
                return;
            }
            this.props.onClick(e);
        },
        getClassName() {
            return "fa fa-2x fa-refresh icon-create-listing" + (this.props.disabled ? ' inactive' : '');
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
