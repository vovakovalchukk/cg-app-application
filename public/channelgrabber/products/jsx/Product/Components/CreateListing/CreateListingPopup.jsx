define([
    'react',
    'Common/Components/Container',
    'Common/Components/Popup/Message',
    'Product/Components/CreateListing/Form/Ebay',
    'Product/Components/CreateListing/Form/Shopify',
    'Common/Components/Select',
    'Product/Utils/CreateListingUtils'
], function(
    React,
    Container,
    PopupMessage,
    EbayForm,
    ShopifyForm,
    Select,
    CreateListingUtils
) {
    "use strict";

    var channelToFormMap = {
        'ebay': EbayForm,
        'shopify': ShopifyForm
    };

    return React.createClass({
        getDefaultProps: function() {
            return {
                product: null,
                accounts: {},
                availableChannels: {}
            }
        },
        getInitialState: function() {
            return {
                accountSelected: null,
                productId: null,
                accountId: null,
                title: null,
                description: null,
                price: null,
                errors: [],
                warnings: []
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
                product={this.props.product}
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
                if (CreateListingUtils.productCanListToAccount(account, this.props.availableChannels)) {
                    options.push({name: account.displayName, value: account.id});
                }
            }

            return options;
        },
        submitFormData: function () {
            var formData = this.getFormData();
            $.ajax({
                url: '/products/listing/submit',
                data: formData,
                type: 'POST',
                context: this,
            }).then(function(response) {
                window.scrollTo(0, 0);
                if (response.valid) {
                    this.handleFormSubmitSuccess(response);
                } else {
                    this.handleFormSubmitError(response);
                }
            }, function(response) {
                n.error('There was a problem creating the listing');
            });
        },
        getFormData: function() {
            var formData = {
                accountId: this.state.accountId,
                productId: this.state.productId,
                listing: {}
            };
            formData.listing = this.getListingDataFromState();

            return formData;
        },
        getListingDataFromState: function() {
            var listing = this.cloneState();
            delete listing.accountSelected;
            delete listing.productId;
            delete listing.accountId;
            delete listing.errors;
            delete listing.warnings;
            return listing;
        },
        cloneState: function() {
            return JSON.parse(JSON.stringify(this.state));
        },
        handleFormSubmitSuccess: function(response) {
            n.success('Listing created successfully');
            this.props.onCreateListingClose();
        },
        handleFormSubmitError: function(response) {
            this.setState({
                errors: response.errors,
                warnings: response.warnings
            });
        },
        renderErrorMessage: function() {
            if (this.state.errors.length == 0) {
                return;
            }
            return (
                <PopupMessage
                    initiallyActive={!!this.state.errors.length}
                    headerText="There were errors when trying to create the listing"
                    className="error"
                    onCloseButtonPressed={this.onErrorMessageClosed}
                >
                    <h4>Errors</h4>
                    <ul>
                        {this.state.errors.map(function (error) {
                            return (<li>{error}</li>);
                        })}
                    </ul>
                    <h4>Warnings</h4>
                    <ul>
                        {this.state.warnings.map(function (warning) {
                            return (<li>{warning}</li>);
                        })}
                    </ul>
                    <p>Please address these errors then try again.</p>
                </PopupMessage>
            );
        },
        onErrorMessageClosed: function() {
            this.setState({
                errors: [],
                warnings: []
            });
        },
        render: function() {
            return (
                    <Container
                        initiallyActive={true}
                        className="editor-popup product-create-listing"
                        onYesButtonPressed={this.submitFormData}
                        onNoButtonPressed={this.props.onCreateListingClose}
                        closeOnYes={false}
                        headerText={"Create New Listing"}
                        subHeaderText={"ChannelGrabber needs additional information to complete this listing. Please check below and complete all the fields necessary."}
                        yesButtonText="Create Listing"
                        noButtonText="Cancel"
                    >
                        <form>
                            {this.renderErrorMessage()}
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
                    </Container>
            );
        }
    });
});
