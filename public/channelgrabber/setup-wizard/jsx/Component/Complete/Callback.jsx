define(['react', 'react-dom'], function(React, ReactDOM) {
    "use strict";

    var CallbackComponent = React.createClass({
        getDefaultProps: function() {
            return {
                callNow: false,
                callLater: null,
                thanks: null
            };
        },
        handleCallNow: function() {
            if (this.props.thanks) {
                window.location = this.props.thanks;
            }
        },
        handleCallLater: function() {
            if (this.props.callLater) {
                window.location = this.props.callLater;
            }
        },
        renderCallNow: function() {
            return(
                <div>
                    <div className="callback-message">
                        Click below to let us know when you are free for one of our product specialists to contact you to activate your trial.
                    </div>
                    <div className="callback-buttons">
                        {this.renderButton('Call Now', this.handleCallNow)}
                        {this.renderButton('Call Later', this.handleCallLater)}
                    </div>
                </div>
            );
        },
        renderCallLater: function() {
            return(
                <div>
                    <div className="callback-message">
                        Click below to book a call with one of our product specialists to activate your trial.
                    </div>
                    <div className="callback-buttons">
                        {this.renderButton('Book your activation call now', this.handleCallLater)}
                    </div>
                </div>
            );
        },
        renderButton: function(message, callback) {
            return(
                <div className="callback-button" onClick={callback}>{message}</div>
            );
        },
        render: function() {
            if (this.props.callNow) {
                return this.renderCallNow();
            }
            return this.renderCallLater()
        }
    });

    return CallbackComponent;
});