define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Common/Components/Button',
    'Common/Components/ImagePicker',
    'Product/Components/CreateListing/Form/Shopify/CategorySelect',
    'Product/Components/CreateListing/Form/Shopify/RefreshIcon',
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    Button,
    ImagePicker,
    CategorySelect,
    RefreshIcon
) {
    "use strict";

    var NO_SETTINGS = 'NO_SETTINGS';

    return React.createClass({
        getDefaultProps: function() {
            return {
                title: null,
                description: null,
                price: null,
                accountId: null,
                brand: null,
                product: null,
                category: []
            }
        },
        getInitialState: function() {
            return {
                error: false,
                settingsFetched: false,
                categories: null,
                refreshCategoriesDisabled: false
            }
        },
        componentDidMount: function() {
            this.fetchAndSetDefaultsForAccount();
            this.fetchAndSetCategories();
        },
        componentWillReceiveProps(newProps) {
            if (this.props.accountId != newProps.accountId) {
                this.fetchAndSetDefaultsForAccount(newProps.accountId);
                this.fetchAndSetCategories(newProps.accountId);
            }
        },
        fetchAndSetDefaultsForAccount(newAccountId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;

            $.ajax({
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
        fetchAndSetCategories(newAccountId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;

            $.get('/products/create-listings/' + accountId + '/channel-specific-field-values', function(data) {
                this.setState({categories: data.categories});
            }.bind(this));
        },
        refreshCategories() {
            this.setState({refreshCategoriesDisabled: true});
            $.get('/products/create-listings/' + this.props.accountId + '/refresh-categories', function(data) {
                this.setState({
                    categories: data.categories,
                    refreshCategoriesDisabled: false
                });
            }.bind(this));
        },
        onInputChange: function(event) {
            var newStateObject = {};
            newStateObject[event.target.name] = event.target.value;
            this.props.setFormStateListing(newStateObject);
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
                        In order to create listings on this account, please first create the
                        <a href={"/settings/channel/sales/" + this.props.accountId}>default listing settings</a>
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
                    <span className={"inputbox-label"}>Price</span>
                    <div className={"order-inputbox-holder"}>
                        <CurrencyInput value={this.props.price} onChange={this.onInputChange} currency={this.props.listingCurrency} />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Brand</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name="brand"
                            value={this.props.brand}
                            onChange={this.onInputChange}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Category</span>
                    <div className={"order-inputbox-holder"}>
                        <CategorySelect
                            accountId={this.props.accountId}
                            categories={this.state.categories}
                        />
                    </div>
                    <RefreshIcon
                        onClick={this.refreshCategories}
                        disabled={this.state.refreshCategoriesDisabled}
                    />
                </label>
                <label>
                    <span className={"inputbox-label"}>Image</span>
                    {this.renderImagePicker()}
                </label>
            </div>;
        }
    });
});