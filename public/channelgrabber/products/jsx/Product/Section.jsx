define([
    'react',
], function(
    React
) {
    const ContainerSection = React.createClass({
        getDefaultProps: function () {
            return {
                headerText: null,
                showYesButton: false,
                showNoButton: false,
                showBackButton: false,
                className: ''
            }
        },
        render: function() {
            return <div className={'container-wrapper ' + this.props.className}>
                <div className={'container-content ' + this.props.className}>
                    {this.renderHeader()}
                    {this.renderContent()}
                    {this.renderButtons()}
                </div>
            </div>;
        },
        renderHeader: function() {
            return <div className="container-header">
                <div className="container-header-text"> {this.props.headerText}</div>
                {this.renderBackButton()}
            </div>;
        },
        renderBackButton: function() {
            return this.props.showBackButton && <div className="container-header-back-button">
                <i className="fa fa-arrow-circle-o-left" onClick={this.onBackButtonPressed} />
            </div>;
        },
        renderContent: function() {
            return <div className="container-children">{this.props.children}</div>;
        },
        renderButtons: function() {
            if (!this.props.showYesButton && !this.props.showNoButton) {
                return null;
            }

            return <div className="container-buttons">
                <div style={{margin: "0px auto"}}>
                    {this.renderNoButton()}
                    {this.renderYesButton()}
                </div>
            </div>;
        },
        renderYesButton: function() {
            return this.props.showYesButton && <div
                className={"button container-btn yes"}
                onClick={this.onYesButtonPressed}
            >
                {this.props.yesButtonText}
            </div>;
        },
        onYesButtonPressed: function() {
            if (this.props.yesButtonDisabled) {
                return;
            }
            this.props.onYesButtonPressed();
        },
        renderNoButton: function() {
            return this.props.showNoButton && <div
                className="button container-btn no"
                onClick={this.props.onNoButtonPressed}
            >
                {this.props.noButtonText}
            </div>;
        }
    });

    return ContainerSection;
});
