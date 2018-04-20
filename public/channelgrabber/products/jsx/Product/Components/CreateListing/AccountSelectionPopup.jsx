define([
    'react',
    'Common/Components/Container',
], function(
    React,
    Container
) {
    "use strict";

    var AccountSelectionPopup = React.createClass({
        render: function() {
            console.log(this.props.accounts, this.props.channelBadges);
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup product-create-listing"
                    closeOnYes={false}
                    headerText={"Selects accounts to list to"}
                    onNoButtonPressed={this.props.onCreateListingClose}
                    yesButtonText="Next"
                    noButtonText="Cancel"
                >
                    <span>Test</span>
                </Container>
            );
        }
    });

    return AccountSelectionPopup;
});
