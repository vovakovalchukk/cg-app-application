define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Ebay/CategorySelect',
    'Product/Components/CreateListing/Form/Shared/VariationPicker',
    'Common/Components/ImagePicker'
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    CategorySelect,
    VariationPicker,
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
                variationsDataForProduct: [],
                attributeNameMap: {},
                listingType: null
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

                    if (newSiteId) {
                        this.props.setFormStateListing({
                            price: null,
                            category: null,
                            duration: null,
                            dispatchTimeMax: null,
                            shippingService: null,
                            shippingPrice: null
                        });
                    } else if (response.defaultSiteId) {
                        this.props.setFormStateListing({site: response.defaultSiteId});
                    }
                }
            });
        },
        onSiteChange: function(site) {
            this.props.getSelectCallHandler('site')(site);
            this.fetchAndSetChannelSpecificFieldValues(null, site.value);
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
        onListingTypeSelected: function(listingType) {
            this.props.setFormStateListing({
                listingType: listingType.value
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
        getChannelSpecificVariationFields: function() {
            return {
                ean: {
                    displayName: 'Barcode',
                    getFormComponent: function(value, onChange) {
                        return <Input
                            name="ean"
                            value={value}
                            onChange={onChange}
                        />
                    },
                    getDefaultValueFromVariation: function(variation) {
                        return variation.details.ean;
                    }
                }
            }
        },
        renderVariationPicker: function () {
            if (this.props.variationsDataForProduct.length == 0) {
                return;
            }

            return <VariationPicker
                images={false}
                variationsDataForProduct={this.props.variationsDataForProduct}
                variationFormState={this.props.variations}
                setFormStateListing={this.props.setFormStateListing}
                attributeNames={this.props.product.attributeNames}
                attributeNameMap={this.props.attributeNameMap}
                editableAttributeNames={true}
                channelSpecificFields={this.getChannelSpecificVariationFields()}
                currency={this.state.currency}
                listingType={this.props.listingType}
                fetchVariations={this.props.fetchVariations}
                product={this.props.product}
            />
        },
        renderVariationListingType: function()
        {
            if (this.props.variationsDataForProduct.length == 0) {
                return;
            }
            var multiVariation = (this.props.variations && Object.keys(this.props.variations).length > 1);
            var options = [
                {"value": "variation", "name": "Variation Product", "selected": multiVariation},
                {"value": "single", "name": "Single Product"}
            ];
            return <label>
                <span className={"inputbox-label"}>Listing Type:</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        options={options}
                        autoSelectFirst={false}
                        onOptionChange={this.onListingTypeSelected}
                        disabled={multiVariation}
                        selectedOption={multiVariation ? options[0] : null}
                    />
                </div>
            </label>;
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
                {this.renderVariationPicker()}
                {this.renderVariationListingType()}
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
