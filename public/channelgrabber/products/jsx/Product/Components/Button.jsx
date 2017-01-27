define([
    'React'
], function(
    React
) {
    "use strict";

    var ButtonComponent = React.createClass({
        render: function () {
            return (
                <div className="button" onClick={this.props.onClick}>
                    {this.props.text}
                </div>
            );
        }
    });

    return ButtonComponent;
});