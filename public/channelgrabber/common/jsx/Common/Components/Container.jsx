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
                subHeaderText: null
            }
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
        render: function() {
            return <div className={'container-wrapper ' + this.props.className}>
                <div className={'container-content ' + this.props.className}>
                    <div className="container-header">{this.props.headerText}</div>
                    {this.props.subHeaderText ?
                        <div className="container-sub-header">{this.props.subHeaderText}</div>
                        : null
                    }
                    <div className="container-content">{this.props.children}</div>
                    <div className="container-buttons">
                        <div className="button container-btn no" onClick={this.noButtonPressed}>{this.props.noButtonText}</div>
                        <div className="button container-btn yes" onClick={this.yesButtonPressed}>{this.props.yesButtonText}</div>
                    </div>
                </div>
            </div>
        }
    });

    return ContainerComponent;
});