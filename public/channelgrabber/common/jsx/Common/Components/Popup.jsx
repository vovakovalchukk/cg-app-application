define([
    'React'
], function(
    React
) {
    "use strict";

    var PopupComponent = React.createClass({
        getDefaultProps: function () {
            return {
                initiallyActive: false,
                headerText: "",
                noButtonText: "No",
                yesButtonText: "Yes"
            };
        },
        getInitialState: function () {
            return {
                active: this.props.initiallyActive
            };
        },
        getPopupMarkup: function () {
            if (! this.state.active) {
                return;
            }
            return (
                <div>
                    <div className="react-popup-screen-mask"></div>
                        <div className="react-popup">
                            <div className="react-popup-header">{this.props.headerText}</div>
                        <div className="react-popup-content">{this.props.children}</div>
                        <div className="react-popup-buttons">
                            <div className="button react-popup-btn no" onClick={this.props.onNoButtonPressed}>{this.props.noButtonText}</div>
                            <div className="button react-popup-btn yes" onClick={this.props.onYesButtonPressed}>{this.props.yesButtonText}</div>
                        </div>
                    </div>
                </div>
            );
        },
        render: function () {
            return (
                <div>
                    {this.getPopupMarkup()}
                </div>
            );
        }
    });

    return PopupComponent;
});
