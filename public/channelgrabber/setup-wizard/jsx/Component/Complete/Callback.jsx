define(['react', 'AjaxRequester'], function(React, ajaxRequester) {
    "use strict";

    var CallbackComponent = React.createClass({
        getDefaultProps: function() {
            return {
                callNow: false,
                callLater: null,
                thanks: null,
                ajax: null
            };
        },
        handleCallNow: function() {
            this.sendAjaxNotification(true, this.props.thanks);
        },
        handleCallLater: function() {
            this.sendAjaxNotification(false, this.props.callLater);
        },
        sendAjaxNotification: function(callNow, redirect) {
            if (this.props.ajax) {
                ajaxRequester.sendRequest(this.props.ajax, {callNow: callNow ? 1 : 0}, this.redirect.bind(this, redirect));
            } else {
                this.redirect(redirect);
            }
        },
        redirect: function(redirect) {
            if (redirect) {
                window.location = redirect;
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