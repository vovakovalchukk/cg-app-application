define([
    'react'
], function(
    React
) {
    "use strict";

    var ButtonComponent = React.createClass({
        onClick: function (e) {
            if (this.props.disabled) {
                return;
            }
            this.props.onClick(e);
        },
        render: function () {
            return (
                <div className={"button" + (this.props.disabled ? " disabled" : "")} onClick={this.onClick}>
                    {this.props.sprite ? <span className={"sprite-sprite "+this.props.sprite}>&nbsp;</span> : ''}
                    <span className="button-text">{this.props.text}</span>
                </div>
            );
        }
    });

    return ButtonComponent;
});