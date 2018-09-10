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
                className: '',
                sectionName: '',
                nextSectionName: null
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
            return <a name={this.props.sectionName}>
                <div className="container-header">
                    <div className="container-header-text"> {this.props.headerText}</div>
                    {this.renderBackButton()}
                </div>
            </a>;
        },
        renderBackButton: function() {
            return this.props.showBackButton && <div className="container-header-back-button">
                <i className="fa fa-arrow-circle-o-left" onClick={this.props.onBackButtonPressed} />
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
                {this.renderNoButton()}
                {this.renderYesButton()}
            </div>;
        },
        renderYesButton: function() {
            if (!this.props.showYesButton) {
                return null;
            }

            const yesButton = (
                <div
                    className={"button container-btn yes"}
                    onClick={this.onYesButtonPressed}
                >
                    {this.props.yesButtonText}
                </div>
            );

            if (this.props.nextSectionName) {
                return <a href={"#" + this.props.nextSectionName}>{yesButton}</a>;
            }

            return yesButton;
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
