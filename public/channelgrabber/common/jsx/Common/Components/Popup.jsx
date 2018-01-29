define([
    'react',
    'Common/Components/ClickOutside'
], function(
    React,
    ClickOutside
) {
    "use strict";

    var PopupComponent = React.createClass({
        getDefaultProps: function () {
            return {
                initiallyActive: false,
                headerText: "",
                noButtonText: "No",
                yesButtonText: "Yes",
                closeOnNo: true,
                closeOnYes: true,
                onClickOutside: null
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

            var innerContent = <div className={"react-popup " + this.props.className }>
                <div className="react-popup-header">{this.props.headerText}</div>
                <div className="react-popup-content">{this.props.children}</div>
                <div className="react-popup-buttons">
                    <div className="button react-popup-btn no" onClick={this.noButtonPressed}>{this.props.noButtonText}</div>
                    <div className="button react-popup-btn yes" onClick={this.yesButtonPressed}>{this.props.yesButtonText}</div>
                </div>
            </div>;

            return <div>
                <div className="react-popup-screen-mask"></div>
                {this.props.onClickOutside ?
                    <ClickOutside onClickOutside={this.props.onClickOutside}>{innerContent}</ClickOutside>
                    : innerContent
                }
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
