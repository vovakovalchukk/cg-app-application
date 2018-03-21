define(['react', 'AjaxRequester'], function(React, ajaxRequester) {
    "use strict";

    var CallbackComponent = React.createClass({
        getDefaultProps: function() {
            return {
                callNow: false,
                callLater: null,
                ajax: null
            };
        },
        getInitialState: function() {
            return {
                loading: false
            };
        },
        handleCallLater: function() {
            this.sendAjaxNotification(false, this.props.callLater);
        },
        sendAjaxNotification: function(callNow, redirect) {
            this.setState({loading: true});
            if (this.props.ajax) {
                ajaxRequester.sendRequest(
                    this.props.ajax,
                    {callNow: callNow ? 1 : 0},
                    this.redirect.bind(this, redirect),
                    this.setState.bind(this, {loading: false})
                );
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
                        One of our product specialists will contact you to activate your trial shortly.
                    </div>

                    <div className="callback-phone-number">
                        Activate your 14 day free trial now by calling us on 0161 711 0248
                    </div>
                </div>
            );
        },
        renderCallLater: function() {
            var buttons = [];
            if (!this.state.loading) {
                buttons.push(this.renderButton('Book your activation call now', this.handleCallLater));
            } else {
                buttons.push(this.renderLoader())
            }
            return(
                <div>
                    <div className="callback-message">
                        Click below to book a call with one of our product specialists to activate your trial.
                    </div>
                    <div className="callback-buttons">
                        {buttons}
                    </div>
                </div>
            );
        },
        renderButton: function(message, callback) {
            return(
                <div className="callback-button" onClick={callback}>{message}</div>
            );
        },
        renderLoader: function() {
            return(
                <div><img src="/cg-built/zf2-v4-ui/img/loading.gif"/></div>
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