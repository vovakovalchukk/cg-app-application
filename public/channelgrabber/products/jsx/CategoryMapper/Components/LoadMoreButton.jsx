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
                disabled: false
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