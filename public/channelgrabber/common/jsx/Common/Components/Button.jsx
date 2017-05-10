define([
    'react'
], function(
    React
) {
    "use strict";

    var ButtonComponent = React.createClass({
        render: function () {
            return (
                <div className="button" onClick={this.props.onClick}>
                    {this.props.sprite ? <span className={"sprite-sprite "+this.props.sprite}>&nbsp;</span> : ''}
                    <span className="button-text">{this.props.text}</span>
                </div>
            );
        }
    });

    return ButtonComponent;
});