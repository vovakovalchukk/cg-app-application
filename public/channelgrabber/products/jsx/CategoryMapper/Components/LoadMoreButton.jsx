define([
    'react'
], function(
    React
) {
    "use strict";

    var LoadMoreButton = React.createClass({
        getDefaultProps: function() {
            return {
                onClick: function () {},
                disabled: false,
                active: false
            }
        },
        onClick: function() {
            if (this.props.disabled) {
                return;
            }
            this.props.onClick();
        },
        getClassName: function() {
            return "button container-btn yes" + (this.props.disabled ? " disabled" : "");
        },
        render: function() {
            if (!this.props.active) {
                return null;
            }
            return (
                <span className="button-container">
                    <div className="load-more-button">
                        <div className={this.getClassName()} onClick={this.onClick}>
                            <span>Load more</span>
                        </div>
                    </div>
                </span>
            );
        }
    });

    return LoadMoreButton;
});