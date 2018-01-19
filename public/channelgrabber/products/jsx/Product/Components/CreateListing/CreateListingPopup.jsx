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

    var NO_SETTINGS = 'NO_SETTINGS';

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

            if (accountOptions.length == 2) {
                this.onAccountSelected(accountOptions[1]);
            }
        },
        setFormStateListing: function(listingFormState) {
            this.setState(listingFormState);
        },
        componentWillReceiveProps: function(newProps) {
            if (!newProps.product) {
                return;
            }

            this.setState({
                productId: newProps.product.id,
                title: newProps.product.name,
                description: newProps.product.details.description ? newProps.product.details.description : null,
                price: newProps.product.details.price ? newProps.product.details.price : null
            });
        },
        renderCreateListingForm: function() {
            if (!this.state.accountSelected) {
                return;
            }

            if (this.state.error && this.state.error == NO_SETTINGS) {
                return <div>
                    <h2>
                        In order to create listings on this account, please first create the
                        <a href={"/settings/channel/sales/" + this.state.accountId}>default listing settings</a>
                    </h2>
                </div>
            }

            var FormComponent = channelToFormMap[this.state.accountSelected.channel];
            return <FormComponent {...this.state} setFormStateListing={this.setFormStateListing}/>
        },
        onAccountSelected: function(selectValue) {
            var accountId = selectValue.value;
            var account = this.props.accounts[selectValue.value];

            this.setState({
                accountSelected: account,
                accountId: accountId
            });

            if (!accountId) {
                return;
            }

            $.ajax({
                url: '/products/create-listings/ebay/default-settings/' + selectValue.value,
                type: 'GET',
                success: function (response) {
                    if (response.error == NO_SETTINGS) {
                        this.setState({
                            error: NO_SETTINGS
                        })

                        return;
                    }
                    this.setState({
                        listingCurrency: response.listingCurrency,
                        listingDipsatchTime: response.listingDipsatchTime,
                        listingDuration: response.listingDuration,
                        listingLocation: response.listingLocation,
                        listingPaymentMethod: response.listingPaymentMethod,
                        paypalEmail: response.paypalEmail
                    })
                }.bind(this)
            });
        },
        getAccountOptions: function() {
            var options = [{name: null, value: null}];

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
                    initiallyActive={!!this.props.product}
                    className="editor-popup"
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