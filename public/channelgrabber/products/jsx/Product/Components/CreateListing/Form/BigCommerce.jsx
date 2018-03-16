define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/BigCommerce/CategorySelect',
    'Product/Components/CreateListing/Form/Shared/VariationPicker',
    'Product/Components/CreateListing/Form/Shared/SimpleProduct',
    'Common/Components/ImagePicker',
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    CategorySelect,
    VariationPicker,
    SimpleProduct,
    ImagePicker
) {
    "use strict";

    var BigCommerceComponent = React.createClass({
        getDefaultProps: function() {
            return {
                title: null,
                description: null,
                price: null,
                accountId: null,
                product: null,
                weight: null,
                variationsDataForProduct: []
            }
        },
        getInitialState: function() {
            return {
                error: false,
                shippingService: null,
                rootCategories: null,
            }
        },
        componentDidMount: function() {
            this.fetchAndSetChannelSpecificFieldValues();
        },
        componentWillReceiveProps(newProps) {
            if (this.props.accountId != newProps.accountId) {
                this.fetchAndSetChannelSpecificFieldValues(newProps.accountId);
            }
        },
        fetchAndSetChannelSpecificFieldValues: function(newAccountId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;

            $.ajax({
                context: this,
                url: '/products/create-listings/' + accountId + '/channel-specific-field-values',
                type: 'GET',
                success: function (response) {
                    this.setState({
                        rootCategories: response.categories
                    });
                }
            });
        },
        refreshCategories() {
            this.setState({refreshCategoriesDisabled: true});
            $.get('/products/create-listings/' + this.props.accountId + '/refresh-categories', function(data) {
                if (data.error) {
                    n.error(data.error);
                }
                this.setState({
                    rootCategories: data.categories || [],
                    refreshCategoriesDisabled: false
                });
            }.bind(this));
        },
        onInputChange: function(event) {
            var newStateObject = {};
            newStateObject[event.target.name] = event.target.value;
            this.props.setFormStateListing(newStateObject);
        },
        onLeafCategorySelected(categoryId) {
            this.props.setFormStateListing({category: categoryId});
        },
        onImageSelected: function(image) {
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
                weight: 'BigCommerce requires a weight used for shipping purposes. Enter the weight of the product by using the weight measurement set in you BigCommerce account settings'
            };
            return tooltips[inputFieldName];
        },
        getCustomFields: function() {
            return {
                weight: {
                    displayName: 'Weight',
                    getFormComponent: function(value, onChange) {
                        return <Input
                            name="weight"
                            value={value}
                            onChange={onChange}
                            title={this.getTooltipText('weight')}
                        />
                    }.bind(this),
                    getDefaultValueFromVariation: function(variation) {
                        return variation.details.weight ? variation.details.weight : null;
                    }
                }
            }
        },
        renderVariationSpecificFields: function () {
            var variationsDataForProduct = this.props.variationsDataForProduct;
            var attributeNames = this.props.product.attributeNames;
            if (this.props.variationsDataForProduct.length == 0) {
                return <SimpleProduct
                    setFormStateListing={this.props.setFormStateListing}
                    customFields={this.getCustomFields()}
                    currency={this.state.currency}
                    product={this.props.product}
                    price={this.props.price}
                    weight={this.props.weight}
                />;
            }

            return <VariationPicker
                variationsDataForProduct={variationsDataForProduct}
                variationFormState={this.props.variations}
                setFormStateListing={this.props.setFormStateListing}
                attributeNames={attributeNames}
                customFields={this.getCustomFields()}
                currency={this.state.currency}
                fetchVariations={this.props.fetchVariations}
                product={this.props.product}
            />
        },
        render: function() {
            return <div>
                {this.renderVariationSpecificFields()}
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
                    <span className={"inputbox-label"}>Primary image</span>
                    {this.renderImagePicker()}
                </label>
                <CategorySelect
                    accountId={this.props.accountId}
                    rootCategories={this.state.rootCategories}
                    onLeafCategorySelected={this.onLeafCategorySelected}
                    refreshCategories={this.refreshCategories}
                    refreshCategoriesDisabled={this.state.refreshCategoriesDisabled}
                    title={this.getTooltipText('category')}
                />
            </div>;
        }
    });

    return BigCommerceComponent;
});