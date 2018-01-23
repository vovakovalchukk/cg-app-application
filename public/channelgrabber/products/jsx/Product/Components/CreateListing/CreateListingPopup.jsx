define([
    'react',
    'Common/Components/Popup',
    'Product/Components/CreateListing/Form/Ebay',
    'Common/Components/Select',
    'Product/Utils/CreateListingUtils'
], function(
    React,
    Popup,
    EbayForm,
    Select,
    CreateListingUtils
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
                accountSelected: null,
                productId: null,
                accountId: null,
                title: null,
                description: null,
                price: null
            }
        },
        componentDidMount: function() {
            var accountOptions = this.getAccountOptions();

            if (accountOptions.length == 1) {
                this.onAccountSelected(accountOptions[0]);
            }

            if (!this.props.product) {
                return;
            }

            this.setState({
                productId: this.props.product.id,
                title: this.props.product.name,
                description: this.props.product.details.description ? this.props.product.details.description : null,
                price: this.props.product.details.price ? this.props.product.details.price : null
            });
        },
        setFormStateListing: function(listingFormState) {
            this.setState(listingFormState);
        },
        getSelectCallHandler: function(fieldName) {
            return function(selectValue) {
                var newState = {};
                newState[fieldName] = selectValue.value;
                this.setFormStateListing(newState);
            }.bind(this);
        },
        renderCreateListingForm: function() {
            if (!this.state.accountSelected) {
                return;
            }

            var FormComponent = channelToFormMap[this.state.accountSelected.channel];
            return <FormComponent
                {...this.state}
                setFormStateListing={this.setFormStateListing}
                getSelectCallHandler={this.getSelectCallHandler}
            />
        },
        onAccountSelected: function(selectValue) {
            var accountId = selectValue.value;
            var account = this.props.accounts[selectValue.value];

            this.setState({
                accountSelected: account,
                accountId: accountId
            });
        },
        getAccountOptions: function() {
            var options = [];

            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                if (CreateListingUtils.productCanListToAccount(account, Object.keys(this.props.product.listingsPerAccount))) {
                    options.push({name: account.displayName, value: account.id});
                }
            }

            return options;
        },
        submitFormData: function () {
            var formData = {
                accountId: this.state.accountId,
                productId: this.state.productId,
                listing: {
                    title: this.state.title,
                    price: this.state.price,
                    description: this.state.description
                }
            };
            console.log(formData);
        },
        render: function()
        {
            return (
                <Popup
                    initiallyActive={true}
                    className="editor-popup create-listing"
                    onYesButtonPressed={this.submitFormData}
                    onNoButtonPressed={this.props.onCreateListingClose}
                    headerText={"Create New Listing"}
                    yesButtonText="Save"
                    noButtonText="Cancel"
                >
                    <h1>
                        Channel Grabber needs additional information to complete this listing. Please check below and
                        complete all the fields necessary.
                    </h1>
                    <form>
                        <div className={"order-form half"}>
                            <label>
                                <span className={"inputbox-label"}>Select an account to list to:</span>
                                <div className={"order-inputbox-holder"}>
                                    <Select
                                        options={this.getAccountOptions()}
                                        selectedOption={
                                            this.state.accountSelected
                                            && this.state.accountSelected.displayName
                                                ? {name: this.state.accountSelected.displayName}
                                                : null
                                        }
                                        onOptionChange={this.onAccountSelected.bind(this)}
                                        autoSelectFirst={false}
                                    />
                                </div>
                            </label>
                            {this.renderCreateListingForm()}
                        </div>
                    </form>
                </Popup>
            );
        }
    });

    return CreateListingPopupComponent;
});