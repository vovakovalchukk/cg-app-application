define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Ebay/CategorySelect',
    'Common/Components/ImagePicker',
    'Product/Components/Tooltip'
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    CategorySelect,
    ImagePicker,
    Tooltip
) {
    "use strict";

    var NO_SETTINGS = 'NO_SETTINGS';

    var EbayComponent = React.createClass({
        getDefaultProps: function() {
            return {
                title: null,
                description: null,
                price: null,
                accountId: null,
                product: null
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
                rootCategories: null,
                listingDurationFieldValues: null
            }
        },
        componentDidMount: function() {
            this.fetchAndSetDefaultsForAccount();
            this.fetchAndSetChannelSpecificFieldValues();
        },
        componentWillReceiveProps(newProps) {
            if (this.props.accountId != newProps.accountId) {
                this.fetchAndSetDefaultsForAccount(newProps.accountId);
                this.fetchAndSetChannelSpecificFieldValues(newProps.accountId);
            }
        },
        fetchAndSetDefaultsForAccount(newAccountId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;
            $.ajax({
                context: this,
                url: '/products/create-listings/ebay/default-settings/' + accountId,
                type: 'GET',
                success: function (response) {
                    if (response.error == NO_SETTINGS) {
                        this.setState({
                            error: NO_SETTINGS
                        });

                        return;
                    }
                    this.setState({
                        settingsFetched: true,
                        error: false
                    });
                    this.props.setFormStateListing({
                        dispatchTimeMax: response.listingDispatchTime,
                        duration: response.listingDuration
                    })
                }
            });
        },
        fetchAndSetChannelSpecificFieldValues: function(newAccountId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;
            this.setState({
                listingDurationFieldValues: null
            });
            $.ajax({
                context: this,
                url: '/products/create-listings/ebay/channel-specific-field-values/' + accountId,
                type: 'GET',
                success: function (response) {
                    this.setState({
                        currency: response.currency,
                        rootCategories: response.category,
                        shippingServiceFieldValues: response.shippingService,
                    });
                }
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
        getListingDurationOptions: function() {
            var listingDurationOptions = [];
            for (var listingDurationOption in this.state.listingDurationFieldValues) {
                listingDurationOptions.push({name: this.state.listingDurationFieldValues[listingDurationOption], value: listingDurationOption});
            }
            return listingDurationOptions;
        },
        onLeafCategorySelected(categoryId) {
            this.props.setFormStateListing({category: categoryId});
            this.fetchAndSetListingDurationOptions(categoryId);
        },
        fetchAndSetListingDurationOptions(categoryId) {
            if (!categoryId) {
                this.setState({
                    listingDurationFieldValues: null,
                    duration: null
                });
                return;
            }
            $.ajax({
                url: '/products/create-listings/ebay/category-dependent-field-values/' + this.props.accountId + '/' + categoryId,
                type: 'GET',
                success: function (response) {
                    this.setState({
                        listingDurationFieldValues: response.listingDuration
                    });
                }.bind(this)
            });
        },
        onImageSelected: function(image, selectedImageIds) {
            this.props.setFormStateListing({
                imageId: image.id
            });
        },
        renderImagePicker: function() {
            if (this.props.product.images.length == 0) {
                return (
                    <p>No images available</p>
                );
            }
            return (
                <ImagePicker
                    name="image"
                    multiSelect={false}
                    images={this.props.product.images}
                    onImageSelected={this.onImageSelected}
                />
            );
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
                        <Tooltip hoverContent="hover hover hover">
                            <Input
                                name='title'
                                value={this.props.title}
                                onChange={this.onInputChange}
                            />
                        </Tooltip>
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Price</span>
                    <div className={"order-inputbox-holder"}>
                        <CurrencyInput
                            value={this.props.price}
                            onChange={this.onInputChange}
                            currency={this.state.currency}
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
                <label>
                    <span className={"inputbox-label"}>Image</span>
                    {this.renderImagePicker()}
                </label>
                <CategorySelect
                    accountId={this.props.accountId}
                    rootCategories={this.state.rootCategories}
                    onLeafCategorySelected={this.onLeafCategorySelected}
                />
                {(this.state.listingDurationFieldValues)?
                    <label>
                        <span className={"inputbox-label"}>Listing Duration</span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                name="duration"
                                options={this.getListingDurationOptions()}
                                selectedOption={{name: this.props.duration}}
                                autoSelectFirst={false}
                                onOptionChange={this.props.getSelectCallHandler('duration')}
                            />
                        </div>
                    </label>
                : null}
                <label>
                    <span className={"inputbox-label"}>Dispatch Time Max</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name="dispatchTimeMax"
                            type="number"
                            value={this.props.dispatchTimeMax}
                            onChange={this.onInputChange}
                        />
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
                            name="shippingPrice"
                            value={this.props.shippingPrice}
                            onChange={this.onInputChange}
                            currency={this.state.currency}
                        />
                    </div>
                </label>
            </div>;
        }
    });

    return EbayComponent;
});