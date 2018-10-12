import React from 'react';


class BaseComponent extends React.Component {
    render() {
        return (
            <div id="heading-inspector" className="inspector-module">
                <div className="inspector-holder">
                    <span className="heading-medium">{this.props.heading}</span>
                    {this.props.children}
                </div>
            </div>
        );
    }
}

export default BaseComponent;
