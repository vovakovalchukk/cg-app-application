define([
    'react',
    'Common/Components/Popup',
    'Product/Components/CreateListing/AccountPicker',
    'Product/Components/CreateListing/Form/Ebay'
], function(
    React,
    Popup,
    AccountPicker,
    EbayForm
) {
    "use strict";

    var channelToFormMap = {
        'ebay': EbayForm
    };

    var CreateListingPopupComponent = React.createClass({
        getDefaultProps: function() {
            return {
                product: null,
                accounts: {}
            }
        },
        getInitialState: function() {
            return {
                accountSelected: null
            }
        },
        renderCreateListingForm: function() {
            if (!this.state.accountSelected) {
                return;
            }

            var FormComponent = channelToFormMap[this.state.accountSelected.channel];
            return <FormComponent/>
        },
        onAccountSelected: function(selectValue) {
            this.setState({
                accountSelected: this.props.accounts[selectValue.value]
            })
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
                    <div>
                        <div>Select an account to list to:</div>
                        <div>
                            <AccountPicker
                                accounts={this.props.accounts}
                                accountsProductIsListedOn={Object.keys(this.props.product.listingsPerAccount)}
                                onAccountSelected={this.onAccountSelected.bind(this)}
                            />
                        </div>
                        {this.renderCreateListingForm()}
                    </div>
                </Popup>
            );
        }
    });

    return CreateListingPopupComponent;
});