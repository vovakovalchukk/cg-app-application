define([
    'react'
], function(
    React
) {
    "use strict";

    var MessageComponent = React.createClass({
        getDefaultProps: function () {
            return {
                initiallyActive: false,
                headerText: "",
                className: "",
                closeButtonText: "Close"
            };
        },
        getInitialState: function () {
            return {
                active: this.props.initiallyActive
            };
        },
        componentWillReceiveProps: function (nextProps) {
            this.setState({
                active: nextProps.initiallyActive
            });
        },
        closeButtonPressed: function()
        {
            this.setState({
                active: false
            });
        },
        render: function () {
            if (!this.state.active) {
                return (null);
            }
            return (
                <div className={"react-popup-message " + this.props.className }>
                    <div className="react-popup-message-header">{this.props.headerText}</div>
                    <div className="react-popup-message-content">{this.props.children}</div>
                    <div className="react-popup-message-buttons">
                        <div className="button react-popup-btn close" onClick={this.closeButtonPressed}>{this.props.closeButtonText}</div>
                    </div>
                </div>
            );
        }
    });

    return MessageComponent;
});