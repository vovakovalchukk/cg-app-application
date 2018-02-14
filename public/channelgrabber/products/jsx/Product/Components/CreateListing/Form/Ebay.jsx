define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Ebay/CategorySelect',
    'Common/Components/ImagePicker'
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    CategorySelect,
    ImagePicker,
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
                product: null,
                ean: null
            }
        },
        getInitialState: function() {
            return {
                error: false,
                settingsFetched: false,
                shippingServiceFieldValues: {},
                currencyFieldValues: {},
                shippingService: null,
                rootCategories: null,
                listingDurationFieldValues: null,
                availableSites: {}
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
                url: '/products/create-listings/' + accountId + '/default-settings',
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
        fetchAndSetChannelSpecificFieldValues: function(newAccountId, newSiteId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;
            var siteId = newSiteId ? newSiteId : this.props.site;
            this.setState({
                listingDurationFieldValues: null
            });
            $.ajax({
                context: this,
                url: '/products/create-listings/' + accountId + '/channel-specific-field-values',
                type: 'POST',
                data: {
                    siteId: siteId
                },
                success: function (response) {
                    this.setState({
                        currency: response.currency,
                        rootCategories: response.category,
                        shippingServiceFieldValues: response.shippingService,
                        availableSites: response.sites
                    });

                    if (!newSiteId && response.defaultSiteId) {
                        this.props.setFormStateListing({site: response.defaultSiteId});
                    }
                }
            });
        },
        onSiteChange: function(site) {
            this.props.getSelectCallHandler('site')(site);
            this.fetchAndSetChannelSpecificFieldValues(undefined, site.value);
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
        getSelectOptions: function(selectFieldValues) {
            var selectOptions = [];
            for (var selectOption in selectFieldValues) {
                selectOptions.push({name: selectFieldValues[selectOption], value: selectOption});
            }
            return selectOptions;
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
                url: '/products/create-listings/' + this.props.accountId + '/category-dependent-field-values/' + categoryId,
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
                    title={this.getTooltipText('image')}
                />
            );
        },
        getTooltipText: function(inputFieldName) {
            var tooltips = {
                title: 'An effective title should include brand name and item specifics. Reiterate what your item actually is to make it easy to find',
                price: 'How much do you want to sell your item for?',
                description: 'Describe your item in detail. Be sure to include all item specifics like size shape and colour. Clearly state the item\'s condition such as new or used',
                image: 'Pick an image to use on this listing',
                category: 'Select a category to list your product to',
                duration: 'ChannelGrabber recommends using GTC as this will allow us to automatically activate listings when you add new stock',
                dispatchTimeMax: 'What is the longest amount of time it may take you to dispatch an item?',
                shippingService: 'This must match your shipping services on eBay',
                shippingPrice: 'How much you want to charge for shipping',
                site: null,
                ean: null
            };
            return tooltips[inputFieldName];
        },
        getBarcodeErrors: function() {
            var errors = [];
            var EAN_LENGTH = 13;
            if (!this.props.ean || this.props.ean.length == 0) {
                return errors;
            }

            if (this.props.ean.length != EAN_LENGTH) {
                errors.push('Barcode must be 13 characters long');
            }

            if (!this.props.ean.match(new RegExp('^[0-9]+$'))) {
                errors.push('Barcode must be numbers only');
            }
            return errors;
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
                    <span className={"inputbox-label"}>Site:</span>
                    <div className={"order-inputbox-holder"}>
                        <Select
                            name="site"
                            options={this.getSelectOptions(this.state.availableSites)}
                            selectedOption={{name: this.state.availableSites[this.props.site]}}
                            autoSelectFirst={false}
                            onOptionChange={this.onSiteChange}
                            title={this.getTooltipText('site')}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Listing Title:</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name='title'
                            value={this.props.title}
                            onChange={this.onInputChange}
                            title={this.getTooltipText('title')}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Price</span>
                    <div className={"order-inputbox-holder"}>
                        <CurrencyInput
                            value={this.props.price}
                            onChange={this.onInputChange}
                            currency={this.state.currency}
                            title={this.getTooltipText('price')}
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
                            title={this.getTooltipText('description')}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Image</span>
                    {this.renderImagePicker()}
                </label>
                <label>
                    <span className={"inputbox-label"}>Barcode</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name="ean"
                            value={this.props.ean}
                            onChange={this.onInputChange}
                            title={this.getTooltipText('ean')}
                            errors={this.getBarcodeErrors()}
                        />
                    </div>
                </label>
                <CategorySelect
                    accountId={this.props.accountId}
                    rootCategories={this.state.rootCategories}
                    onLeafCategorySelected={this.onLeafCategorySelected}
                    title={this.getTooltipText('category')}
                />
                {(this.state.listingDurationFieldValues)?
                    <label>
                        <span className={"inputbox-label"}>Listing Duration</span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                name="duration"
                                options={this.getSelectOptions(this.state.listingDurationFieldValues)}
                                selectedOption={{name: this.props.duration}}
                                autoSelectFirst={false}
                                onOptionChange={this.props.getSelectCallHandler('duration')}
                                title={this.getTooltipText('duration')}
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
                            title={this.getTooltipText('dispatchTimeMax')}
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
                            title={this.getTooltipText('shippingService')}
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
                            title={this.getTooltipText('shippingPrice')}
                        />
                    </div>
                </label>
            </div>;
        }
    });

    return EbayComponent;
});