define([
    'react-with-addons',
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
    ImagePicker
) {
    "use strict";

    var NO_SETTINGS = 'NO_SETTINGS';

    var CUSTOM_ITEM_SPECIFIC = 'addCustomItemSpecific';

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
                listingDurationFieldValues: null,
                itemSpecifics: {},
                optionalItemSpecifics: [],
                optionalItemSpecificsSelectOptions: [],
                selectedItemSpecifics: {},
                customItemSpecificCount: 0
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
        fetchAndSetChannelSpecificFieldValues: function(newAccountId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;
            this.setState({
                listingDurationFieldValues: null
            });
            $.ajax({
                context: this,
                url: '/products/create-listings/' + accountId + '/channel-specific-field-values',
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
            this.fetchAndSetCategoryDependentFieldValues(categoryId);
        },
        fetchAndSetCategoryDependentFieldValues(categoryId) {
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
                        listingDurationFieldValues: response.listingDuration,
                        itemSpecifics: response.itemSpecifics
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
        getTooltipText(inputFieldName) {
            var tooltips = {
                title: 'An effective title should include brand name and item specifics. Reiterate what your item actually is to make it easy to find',
                price: 'How much do you want to sell your item for?',
                description: 'Describe your item in detail. Be sure to include all item specifics like size shape and colour. Clearly state the item\'s condition such as new or used',
                image: 'Pick an image to use on this listing',
                category: 'Select a category to list your product to',
                duration: 'ChannelGrabber recommends using GTC as this will allow us to automatically activate listings when you add new stock',
                dispatchTimeMax: 'What is the longest amount of time it may take you to dispatch an item?',
                shippingService: 'This must match your shipping services on eBay',
                shippingPrice: 'How much you want to charge for shipping'
            };
            return tooltips[inputFieldName];
        },
        buildItemSpecificsInputs: function() {
            var itemSpecifics = [];
            if (this.state.itemSpecifics.required) {
                var required = [];
                var hasPlusButton;
                $.each(this.state.itemSpecifics.required, function (name, properties) {
                    hasPlusButton = (properties.maxValues && properties.maxValues > 1 && properties.type == 'text');
                    required.push(this.buildItemSpecificsInputByType(name, properties, hasPlusButton));
                }.bind(this));
                itemSpecifics.push(<span>Item Specifics (Required){required}</span>);
            }
            if (this.state.itemSpecifics.optional) {
                itemSpecifics.push(
                    <label>
                        <span className={"inputbox-label"}><b>Item Specifics (Optional)</b></span>
                        <div className={"order-inputbox-holder"}>
                            <Select
                                name="item-specifics-optional"
                                options={this.buildOptionalItemSpecificsSelectOptions(this.state.itemSpecifics.optional)}
                                autoSelectFirst={false}
                                title="Item Specifics (Optional)"
                                onOptionChange={this.onOptionalItemSpecificSelect}
                            />
                        </div>
                    </label>
                );
            }
            return <span>{itemSpecifics}</span>;
        },
        buildItemSpecificsInputByType: function(name, properties) {
            if (properties.type == 'text') {
                return this.buildTextItemSpecific(name, properties);
            }
            if (properties.type == 'select') {
                return this.buildSelectItemSpecific(name, properties);
            }
            if (properties.type == 'textselect') {
                return this.buildTextSelectItemSpecific(name, properties);
            }
            if (properties.type == 'extraCustom') {
                return this.buildExtraItemSpecific(properties);
            }
        },
        buildTextItemSpecific: function(name, options, hasPlusButton = false) {
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={name}
                        value={this.getItemSpecificTextInputValue(name)}
                        onChange={this.onItemSpecificInputChange}
                    />
                </div>
                {this.renderPlusButton(name, hasPlusButton)}
            </label>
        },
        renderPlusButton: function (name, shouldRender = false) {
            if (!shouldRender) {
                return null;
            }
            return <span className="refresh-icon">
                <i
                    className='fa fa-2x fa-plus-square icon-create-listing'
                    aria-hidden='true'
                    onClick={this.onPlusButtonClick}
                />
            </span>;
        },
        onPlusButtonClick: function (item) {
            console.log(item);
        },
        buildExtraItemSpecific: function () {
            var customInputName = 'CustomInputName' + this.state.customItemSpecificCount;
            var customInputValueName = 'CustomInputValueName' + this.state.customItemSpecificCount;
            var itemSpecific = <label>
                <span className={"inputbox-label"}>
                    <Input
                        name={customInputName}
                    />
                </span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={customInputValueName}
                    />
                </div>
            </label>;
            this.setState({customItemSpecificCount: this.state.customItemSpecificCount + 1});
            return itemSpecific;
        },
        getItemSpecificTextInputValue: function(name) {
            if (this.props.itemSpecifics && this.props.itemSpecifics[name]) {
                return this.props.itemSpecifics[name];
            }
            return null;
        },
        buildOptionalItemSpecificsSelectOptions: function(itemSpecifics) {
            var options = [];
            $.each(itemSpecifics, function (name, value) {
                options.push({
                    "name": name,
                    "value": value
                })
            });
            options.push({
                "name": "Add Custom Item Specific",
                "value": {type: 'extraCustom'}
            })
            return options;
        },
        buildSelectItemSpecific: function(name, options) {
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="duration"
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        onOptionChange={this.onItemSpecificSelected}
                    />
                </div>
            </label>
        },
        getSelectOptionsForItemSpecific(selectName, options) {
            var selectOptions = [];
            $.each(options, function(optionValue, optionName) {
                selectOptions.push({
                    "name": optionName,
                    "value": {
                        "value": optionValue,
                        "selectName": selectName
                    }
                });
            });
            return selectOptions;
        },
        buildTextSelectItemSpecific: function(name, options) {
            return <label>
                <span className={"inputbox-label"}>{name}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="duration"
                        options={this.getSelectOptionsForItemSpecific(name, options.options)}
                        autoSelectFirst={false}
                        title={name}
                        onOptionChange={this.onItemSpecificSelected}
                    />
                </div>
            </label>
        },
        onOptionalItemSpecificSelect: function (field) {
            // Do no render the same field twice
            for (var i = 0; i < this.state.optionalItemSpecifics.length; i++) {
                if (this.state.optionalItemSpecifics[i].name == field.name) {
                    return;
                }
            }

            var optionalItemSpecifics = JSON.parse(JSON.stringify(this.state.optionalItemSpecifics));
            optionalItemSpecifics.push(field);
            this.setState({
                optionalItemSpecifics: optionalItemSpecifics
            });
        },
        buildOptionalItemSpecificsInputs: function() {
            var itemSpecifics = [];
            var field;
            var optionalItemSpecificsLenght = this.state.optionalItemSpecifics.length;
            for (var key = 0; key < optionalItemSpecificsLenght; key++) {
                field = this.state.optionalItemSpecifics[key];
                itemSpecifics.push(this.buildItemSpecificsInputByType(
                    field.name,
                    field.value
                ));
            }
            return <span>{itemSpecifics}</span>;
        },
        onItemSpecificSelected: function(field) {
            var selectedItemSpecifics = this.state.selectedItemSpecifics;
            this.state.selectedItemSpecifics[field.value.selectName] = field.value.value;
            this.setState({
                selectedItemSpecifics: this.state.selectedItemSpecifics
            });
            this.props.setFormStateListing({"itemSpecifics": this.state.selectedItemSpecifics});
        },
        onItemSpecificInputChange: function(event) {
            var selectedItemSpecifics = this.state.selectedItemSpecifics;
            selectedItemSpecifics[event.target.name] = event.target.value;
            this.setState({
                selectedItemSpecifics: this.state.selectedItemSpecifics
            });
            this.props.setFormStateListing({"itemSpecifics": this.state.selectedItemSpecifics});
        },
        shouldComponentUpdate: function(nextProps, nextState) {
            var currentCustomItemSpecificCount = this.state.customItemSpecificCount;
            var nextCustomItemSpecificCount = nextState.customItemSpecificCount;
            nextState.customItemSpecificCount = currentCustomItemSpecificCount;

            if (React.addons.shallowCompare(this, nextProps, nextState) === false) {
                return false;
            }

            nextState.customItemSpecificCount = nextCustomItemSpecificCount;
            return true;
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
                                options={this.getListingDurationOptions()}
                                selectedOption={{name: this.props.duration}}
                                autoSelectFirst={false}
                                onOptionChange={this.props.getSelectCallHandler('duration')}
                                title={this.getTooltipText('duration')}
                            />
                        </div>
                    </label>
                : null}
                {(this.state.itemSpecifics) ? this.buildItemSpecificsInputs() : null}
                {(this.state.optionalItemSpecifics) ? this.buildOptionalItemSpecificsInputs() : null}
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