define([
    'react'
], function(
    React
) {
    "use strict";

    var PopupComponent = React.createClass({
        getDefaultProps: function () {
            return {
                initiallyActive: false,
                headerText: "",
                subHeaderText: null,
                noButtonText: "No",
                yesButtonText: "Yes",
                closeOnNo: true,
                closeOnYes: true
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
        componentDidMount: function () {
            window.addEventListener('triggerPopup', this.triggerPopup);
        },
        componentWillUnmount: function () {
            window.addEventListener('triggerPopup', this.triggerPopup);
        },
        triggerPopup: function () {
            this.setState({
                active: !this.state.active
            });
        },
        noButtonPressed: function () {
            if (this.props.onNoButtonPressed !== undefined) {
                this.props.onNoButtonPressed();
            }
            if (this.props.closeOnNo) {
                this.setState({active: false});
            }
        },
        yesButtonPressed: function () {
            if (this.props.onYesButtonPressed !== undefined) {
                this.props.onYesButtonPressed();
            }
            if (this.props.closeOnYes) {
                this.setState({active: false});
            }
        },
        getPopupMarkup: function () {
            if (! this.state.active) {
                return;
            }

            return <div>
                <div className="react-popup-screen-mask"></div>
                <div className={"react-popup " + this.props.className }>
                    <div className="react-popup-header">{this.props.headerText}</div>
                    {this.props.subHeaderText ?
                        <div className="react-popup-subheader">{this.props.subHeaderText}</div>
                        : null
                    }
                    <div className="react-popup-content">{this.props.children}</div>
                    <div className="react-popup-buttons">
                        <div className="button react-popup-btn no" onClick={this.noButtonPressed}>{this.props.noButtonText}</div>
                        <div className="button react-popup-btn yes" onClick={this.yesButtonPressed}>{this.props.yesButtonText}</div>
                    </div>
                </div>
            </div>;
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
