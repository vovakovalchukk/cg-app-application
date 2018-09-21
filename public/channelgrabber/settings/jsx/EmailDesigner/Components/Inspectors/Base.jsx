import React from 'react';
    

    var BaseComponent = React.createClass({
        render: function() {
            return (
                <div id="heading-inspector" className="inspector-module">
                    <div className="inspector-holder">
                        <span className="heading-medium">{this.props.heading}</span>
                        {this.props.children}
                    </div>
                </div>
            );
        }
    });

    export default BaseComponent;
