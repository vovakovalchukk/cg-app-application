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
            }
        },
        render: function() {
            return <div className={'container-wrapper ' + this.props.className}>
                <div className={'container-content ' + this.props.className}>
                    <div className="container-header">
                        <div className="container-header-text"> {this.props.headerText}</div>
                        <div className="container-header-back-button">
                            <i className="fa fa-arrow-circle-o-left" onClick={this.props.onNoButtonPressed}/>
                        </div>
                    </div>
                    {this.props.subHeaderText ?
                        <div className="container-sub-header">{this.props.subHeaderText}</div>
                        : null
                    }
                    <div className="container-children">{this.props.children}</div>
                    <div className="container-buttons">
                        <div style={{margin: "0px auto"}}>
                            <div className="button container-btn no"
                                 onClick={this.props.onNoButtonPressed}>{this.props.noButtonText}</div>
                            <div className="button container-btn yes"
                                 onClick={this.props.onYesButtonPressed}>
                                {this.props.yesButtonText}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        }
    });

    return ContainerComponent;
});