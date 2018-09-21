import React from 'react';
    var Pane = React.createClass({
        render: function () {
            return (
                <div className="pane">
                    {this.props.children}
                </div>
            );
        }
    });

    export default Pane;
