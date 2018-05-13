define([
    'react'
], function(
    React
) {
    "use strict";

    var ContainerComponent = React.createClass({
        getDefaultProps: function() {
            return {
                className: null,
                headerText: null,
                subHeaderText: null,
                yesButtonDisabled: false,
                onNoButtonPressed: function () {},
                onBackButtonPressed: null
            }
        },
        getYesButtonClassName: function() {
            return "button container-btn yes" + (this.props.yesButtonDisabled ? " disabled" : "");
        },
        onYesButtonPressed: function() {
            if (this.props.yesButtonDisabled) {
                return;
            }
            this.props.onYesButtonPressed(...arguments);
        },
        onBackButtonPressed: function() {
            if (this.props.onBackButtonPressed) {
                this.props.onBackButtonPressed();
                return;
            }
            this.props.onNoButtonPressed();
        },
        onNoButtonPressed: function() {
            this.props.onNoButtonPressed();
        },
        render: function() {
            return <div className={'container-wrapper ' + this.props.className}>
                <div className={'container-content ' + this.props.className}>
                    <div className="container-header">
                        <div className="container-header-text"> {this.props.headerText}</div>
                        <div className="container-header-back-button">
                            <i className="fa fa-arrow-circle-o-left" onClick={this.onBackButtonPressed} />
                        </div>
                    </div>
                    {this.props.subHeaderText ?
                        <div className="container-sub-header">{this.props.subHeaderText}</div>
                        : null
                    }
                    <div className="container-children">{this.props.children}</div>
                    <div className="container-buttons">
                        <div style={{margin: "0px auto"}}>
                            <div className="button container-btn no" onClick={this.onNoButtonPressed}>{this.props.noButtonText}</div>
                            <div className={this.getYesButtonClassName()} onClick={this.onYesButtonPressed}>{this.props.yesButtonText}</div>
                        </div>
                    </div>
                </div>
            </div>
        }
    });

    return ContainerComponent;
});