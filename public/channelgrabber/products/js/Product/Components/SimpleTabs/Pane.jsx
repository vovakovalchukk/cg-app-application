define([
    'react'
], function(
    React
) {
    var Pane = React.createClass({
        render: function() {
            return (
                <div className="pane">
                    {this.props.children}
                </div>
            );
        }
    });

    return Pane;
});