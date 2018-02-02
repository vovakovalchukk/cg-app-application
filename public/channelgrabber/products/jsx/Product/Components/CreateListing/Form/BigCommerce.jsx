define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Common/Components/ImagePicker',
    'Product/Components/CreateListing/Form/BigCommerce/CategorySelect'
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    ImagePicker,
    CategorySelect
) {
    "use strict";

    var NO_SETTINGS = 'NO_SETTINGS';

    var BigCommerceComponent = React.createClass({
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
                        rootCategories: response.category
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
            };
            return tooltips[inputFieldName];
        },
        render: function() {
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
                    refreshCategories={this.refreshCategories}
                    refreshCategoriesDisabled={this.state.refreshCategoriesDisabled}
                    title={this.getTooltipText('category')}
                />
            </div>;
        }
    });

    return BigCommerceComponent;
});