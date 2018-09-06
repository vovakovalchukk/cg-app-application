define([
    'react',
    './Section'
], function(
    React,
    Section
) {
    const SectionedContainer = React.createClass({
        getDefaultProps: function() {
            return {
                headerTexts: [],
                children: [],
                sectionClassName: '',
                yesButtonText: 'Yes',
                noButtonText: "No",
                onYesButtonPressed: () => {},
                onNoButtonPressed: () => {},
                onBackButtonPressed: () => {},
                yesButtonDisabled: false
            }
        },
        render: function() {
            if (this.props.headerTexts.length !== this.props.children.length) {
                console.error('The SectionedContainer must have an equal number of header texts and sections to render');
            }

            return <span>{this.renderSections()}</span>;
        },
        renderSections: function() {
            return this.props.children.map(function(child, index) {
                return this.renderSection(child, index);
            }, this);
        },
        isLastSection: function(index) {
            return index === this.props.children.length - 1;
        },
        renderLastSection: function(child, index) {
            return <Section
                headerText={this.props.headerTexts[index]}
                showYesButton={true}
                showNoButton={true}
            >
                {child}
            </Section>;
        },
        renderSection: function(child, index) {
            const isLastSection = this.isLastSection(index);
            return <Section
                headerText={this.props.headerTexts[index]}
                className={this.props.sectionClassName}
                showBackButton={index === 0}
                onBackButtonPressed={this.props.onBackButtonPressed}
                showNoButton={isLastSection}
                onNoButtonPressed={this.props.onNoButtonPressed}
                noButtonText={this.props.noButtonText}
                showYesButton={true}
                yesButtonText={isLastSection ? this.props.yesButtonText : 'Next'}
                onYesButtonPressed={this.getYesButtonAction()}
            >
                {child}
            </Section>;
        },
        getYesButtonAction: function() {
            return this.props.onYesButtonPressed;
        }
    });

    return SectionedContainer;
});
