define([
    'react',
    'react-redux',
    'Common/Components/Container',
], function(
    React,
    ReactRedux,
    Container
) {
    "use strict";

    var AccountSelectionPopup = React.createClass({
        render: function() {
            console.log(this.props);
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

    var mapStateToProps = function (state, ownProps) {
        return {
            acccounts: state.accounts,
            channelBadges: state.channelBadges
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {}
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(AccountSelectionPopup);
});
