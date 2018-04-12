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
            return "load-more-button" + (this.props.disabled ? " disabled" : "");
        },
        render: function() {
            return (
                <span className="button-container">
                    <div className={this.getClassName()}>
                        <div className={"button container-btn yes"} onClick={this.onClick}>
                            <span>Load more</span>
                        </div>
                    </div>
                </span>
            );
        }
    });

    return LoadMoreButton;
});