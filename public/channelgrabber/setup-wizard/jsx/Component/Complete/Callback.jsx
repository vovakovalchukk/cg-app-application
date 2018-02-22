define(['react', 'react-dom'], function(React, ReactDOM) {
    "use strict";

    var CallbackComponent = React.createClass({
        getDefaultProps: function() {
            return {
                callNow: false
            };
        },
        renderCallNow: function() {
            return(
                <div>
                    <div className="callback-message">
                        Click below to let us know when you are free for one of our product specialists to contact you to activate your trial.
                    </div>
                    <div className="callback-buttons">
                        {this.renderButton('Call Now')}
                        {this.renderButton('Call Later')}
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
                        {this.renderButton('Book your activation call now')}
                    </div>
                </div>
            );
        },
        renderButton: function(message, callback) {
            return(
                <div className="callback-button">{message}</div>
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