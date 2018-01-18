define([
    'react',
    'Common/Components/Popup',
    'Product/Components/CreateListing/AccountPicker'
], function(
    React,
    Popup,
    AccountPicker
) {
    "use strict";

    var CreateListingPopupComponent = React.createClass({
        getDefaultProps: function() {
            return {
                product: null,
                accounts: {}
            }
        },
        render: function()
        {
            if (!this.props.product) {
                return null;
            }
            return (
                <Popup
                    initiallyActive={!!this.props.product}
                    className="editor-popup"
                    onYesButtonPressed={() => {}} /* TODO repeal and replace these 2 lines, too many ES6 */
                    onNoButtonPressed={() => {}}
                    headerText={"Create New Listing"}
                    yesButtonText="Save"
                    noButtonText="Cancel"
                >
                    <h1>
                        Channel Grabber needs additional information to complete this listing. Please check below and
                        complete all the fields necessary.
                    </h1>
                    <AccountPicker
                        accounts={this.props.accounts}
                        accountsProductIsListedOn={Object.keys(this.props.product.listingsPerAccount)}
                    />
                </Popup>
            );
        }
    });

    return CreateListingPopupComponent;
});