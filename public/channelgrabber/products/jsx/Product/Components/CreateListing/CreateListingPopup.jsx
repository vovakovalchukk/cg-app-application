define([
    'react',
    'Common/Components/Popup',
    'Common/Components/Popup/Message',
    'Product/Components/CreateListing/AccountPicker',
    'Product/Components/CreateListing/Form/Ebay'
], function(
    React,
    Popup,
    PopupMessage,
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

            var FormComponent = channelToFormMap[this.state.accountSelected.channel];
            return <FormComponent {...this.state} setFormStateListing={this.setFormStateListing}/>
        },
        onAccountSelected: function(selectValue) {
            this.setState({
                accountSelected: this.props.accounts[selectValue.value],
                accountId: selectValue.value
            })
        },
        submitFormData: function () {
            var formData = this.gatherFormData();
// TEST
formData.errors = true;
            $.ajax({
                url: '/products/listing/submit',
                data: formData,
                type: 'POST',
                context: this,
            }).then(function(response)
            {
                console.log(response);
                if (response.valid) {
                    this.handleFormSubmitSuccess(response);
                } else {
                    this.handleFormSubmitError(response);
                }
            }, function(response)
            {
                console.log(response);
                n.error('There was a problem creating the listing');
            });
        },
        gatherFormData: function() {
            var formData = {
                accountId: this.state.accountId,
                productId: this.state.productId,
                listing: {
                    title: this.state.title,
                    price: this.state.price,
                    description: this.state.description
                }
            };
            // TODO: get the channel-specific fields
            console.log(formData);
            return formData;
        },
        handleFormSubmitSuccess: function(response) {
            n.success('Listing created successfully');
            this.setState({
                active: false
            });
        },
        handleFormSubmitError: function(response) {
            this.setState({
                errors: response.errors,
                warnings: response.warnings
            });
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
                    onYesButtonPressed={this.submitFormData}
                    onNoButtonPressed={function() {}}
                    closeOnYes={false}
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
                                    <AccountPicker
                                        product={this.props.product}
                                        accounts={this.props.accounts}
                                        accountsProductIsListedOn={Object.keys(this.props.product.listingsPerAccount)}
                                        onAccountSelected={this.onAccountSelected.bind(this)}
                                    />
                                </div>
                            </label>
                            {this.renderCreateListingForm()}
                        </div>
                    </form>
                    <PopupMessage
                        initiallyActive={!!this.state.errors}
                        headerText="There were errors when trying to create the listing"
                    >
                        <h4>Errors</h4>
                        <ul>
                            {this.state.errors.map(function (error) {
                                <li>{error}</li>
                            })}
                        </ul>
                        <h4>Warnings</h4>
                        <ul>
                            {this.state.warnings.map(function (warning) {
                                <li>{warning}</li>
                            })}
                        </ul>
                    </PopupMessage>
                </Popup>
            );
        }
    });

    return CreateListingPopupComponent;
});