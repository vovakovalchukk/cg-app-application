define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Ebay/CategorySelect',
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    CategorySelect
) {
    "use strict";

    var NO_SETTINGS = 'NO_SETTINGS';

    var EbayComponent = React.createClass({
        getDefaultProps: function() {
            return {
                title: null,
                description: null,
                price: null,
                accountId: null
            }
        },
        getInitialState: function() {
            return {
                error: false,
                settingsFetched: false,
                categoryFieldValues: {},
                shippingServiceFieldValues: {},
                currencyFieldValues: {},
                shippingService: null,
                rootCategories: null
            }
        },
        componentDidMount: function() {
            this.fetchAndSetDefaultsForAccount();
            this.fetchAndSetChannelSpecificFieldValues();
        },
        fetchAndSetDefaultsForAccount: function() {
            $.ajax({
                url: '/products/create-listings/ebay/default-settings/' + this.props.accountId,
                type: 'GET',
                success: function (response) {
                    if (response.error == NO_SETTINGS) {
                        this.setState({
                            error: NO_SETTINGS
                        });

                        return;
                    }
                    this.setState({
                        settingsFetched: true
                    });
                    this.props.setFormStateListing({
                        listingCurrency: response.listingCurrency,
                        listingDispatchTime: response.listingDispatchTime,
                        listingDuration: response.listingDuration,
                        listingLocation: response.listingLocation,
                        listingPaymentMethod: response.listingPaymentMethod,
                        paypalEmail: response.paypalEmail
                    })
                }.bind(this)
            });
        },
        fetchAndSetChannelSpecificFieldValues: function() {
            $.ajax({
                url: '/products/create-listings/ebay/channel-specific-field-values/' + this.props.accountId,
                type: 'GET',
                success: function (response) {
                    this.setState({
                        rootCategories: response.category,
                        shippingServiceFieldValues: response.shippingService,
                        currency: response.currency
                    });
                }.bind(this)
            });
        },
        onInputChange: function(event) {
            var newStateObject = {};
            newStateObject[event.target.name] = event.target.value;
            this.props.setFormStateListing(newStateObject);
        },
        getShippingServiceOptions: function() {
            var shippingServiceOptions = [];
            for (var shippingService in this.state.shippingServiceFieldValues) {
                shippingServiceOptions.push({name: shippingService, value: this.state.shippingServiceFieldValues[shippingService]});
            }
            return shippingServiceOptions;
        },
        render: function() {

            if (this.state.error && this.state.error == NO_SETTINGS) {
                return <div>
                    <h2>
                        In order to create listings on this account, please first create the <a
                        href={"/settings/channel/sales/" + this.props.accountId}>default listing settings</a>
                    </h2>
                </div>;
            }

            if (!this.state.settingsFetched) {
                return <div>Loading...</div>;
            }

            return <div>
                <label>
                    <span className={"inputbox-label"}>Listing Title:</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name='title'
                            value={this.props.title}
                            onChange={this.onInputChange}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Price</span>
                    <div className={"order-inputbox-holder"}>
                        <CurrencyInput
                            value={this.props.price}
                            onChange={this.onInputChange}
                            currency={this.props.currency}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Description</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name="description"
                            value={this.props.description}
                            onChange={this.onInputChange}
                        />
                    </div>
                </label>
                <CategorySelect
                    accountId={this.props.accountId}
                    rootCategories={this.state.rootCategories}
                    setFormStateListing={this.props.setFormStateListing}
                />
                <label>
                    <span className={"inputbox-label"}>Listing Duration</span>
                    <div className={"order-inputbox-holder"}>
                        only show if we're on a leaf category
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Dispatch Time Max</span>
                    <div className={"order-inputbox-holder"}>

                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Shipping Service</span>
                    <div className={"order-inputbox-holder"}>
                        <Select
                            name="shippingService"
                            options={this.getShippingServiceOptions()}
                            selectedOption={{name: this.props.shippingService}}
                            autoSelectFirst={false}
                            onOptionChange={this.props.getSelectCallHandler('shippingService')}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Shipping Price</span>
                    <div className={"order-inputbox-holder"}>
                        <CurrencyInput
                            value={this.props.shippingPrice}
                            onChange={this.onInputChange}
                            currency={this.props.currency}
                        />
                    </div>
                </label>
            </div>;
        }
    });

    return EbayComponent;
});